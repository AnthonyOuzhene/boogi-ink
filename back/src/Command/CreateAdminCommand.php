<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Usage : ddev console app:create-admin admin@exemple.fr "Prénom Nom"
 *         ddev console app:create-admin dev@exemple.fr "Prénom Nom" --super   (super admin : le développeur)
 * Mot de passe en saisie masquée, pour qu'il n'apparaisse pas dans l'historique du terminal.
 * Super admin : en plus, l'accroche de l'accueil et les comptes super admin (role_hierarchy dans security.yaml).
 */
#[AsCommand(
    name: 'app:create-admin',
    description: 'Crée un compte administrateur pour accéder au backoffice',
)]
class CreateAdminCommand
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly ValidatorInterface $validator,
    ) {
    }

    public function __invoke(
        SymfonyStyle $io,
        #[Argument('Email de connexion')] string $email,
        #[Argument('Nom affiché')] string $name,
        #[Option('Crée un super admin (le développeur) au lieu d\'un admin')] bool $super = false,
    ): int {
        $password = $io->askHidden('Mot de passe (saisie masquée)');
        if (null === $password || mb_strlen($password) < 8) {
            $io->error('Le mot de passe doit contenir au moins 8 caractères.');

            return Command::FAILURE;
        }

        $user = (new User())
            ->setEmail($email)
            ->setName($name)
            ->setRoles([$super ? 'ROLE_SUPER_ADMIN' : 'ROLE_ADMIN']);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));

        // Mêmes règles que dans le backoffice : email valide, unique, nom non vide
        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $io->error($error->getPropertyPath().' : '.$error->getMessage());
            }

            return Command::FAILURE;
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success(sprintf('%s %s créé. Connexion : https://boogi-ink.ddev.site/boogi-0806/login', $super ? 'Super admin' : 'Administrateur', $email));

        return Command::SUCCESS;
    }
}

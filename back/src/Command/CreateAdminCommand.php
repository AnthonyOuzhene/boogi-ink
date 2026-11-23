<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Usage : ddev console app:create-admin admin@exemple.fr "Prénom Nom"
 * Le mot de passe est demandé de façon masquée (il n'apparaît pas dans l'historique du terminal).
 */
#[AsCommand(
    name: 'app:create-admin',
    description: 'Crée un compte administrateur pour accéder au backoffice',
)]
class CreateAdminCommand
{
    // Les services sont injectés par le constructeur (autowiring)
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
    ): int {
        $password = $io->askHidden('Mot de passe (saisie masquée)');
        if (null === $password || mb_strlen($password) < 8) {
            $io->error('Le mot de passe doit contenir au moins 8 caractères.');

            return Command::FAILURE;
        }

        $user = (new User())
            ->setEmail($email)
            ->setName($name)
            ->setRoles(['ROLE_ADMIN']);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));

        // Mêmes règles que dans le backoffice : email valide, unique, nom non vide
        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $io->error($error->getPropertyPath().' : '.$error->getMessage());
            }

            return Command::FAILURE;
        }

        $this->entityManager->persist($user); // "à enregistrer"
        $this->entityManager->flush();        // exécute réellement l'INSERT

        $io->success(sprintf('Administrateur %s créé. Connexion : https://boogi-ink.ddev.site/boogi-0806/login', $email));

        return Command::SUCCESS;
    }
}

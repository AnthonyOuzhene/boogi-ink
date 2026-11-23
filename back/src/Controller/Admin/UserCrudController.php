<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @extends AbstractCrudController<User>
 */
class UserCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Administrateur')
            ->setEntityLabelInPlural('Administrateurs')
            ->setSearchFields(['name', 'email']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name', 'Nom');
        yield EmailField::new('email', 'Email');
        // Pas de choix de rôle : sans espace client, un compte ne sert qu'à se connecter
        // au backoffice, il est donc toujours administrateur (voir persistEntity())
        // Champ "virtuel" : jamais affiché, jamais stocké en clair
        yield TextField::new('plainPassword', 'Mot de passe')
            ->setFormType(PasswordType::class)
            ->onlyOnForms()
            ->setRequired(Crud::PAGE_NEW === $pageName)
            ->setHelp(Crud::PAGE_EDIT === $pageName ? 'Laisser vide pour conserver le mot de passe actuel.' : '');
    }

    // Appelé par EasyAdmin juste avant l'INSERT
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $entityInstance->setRoles(['ROLE_ADMIN']); // tout compte créé ici accède au backoffice
        $this->hashPassword($entityInstance);
        parent::persistEntity($entityManager, $entityInstance);
    }

    // Appelé par EasyAdmin juste avant l'UPDATE
    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->hashPassword($entityInstance);
        parent::updateEntity($entityManager, $entityInstance);
    }

    private function hashPassword(User $user): void
    {
        $plainPassword = $user->getPlainPassword();
        if (null === $plainPassword || '' === $plainPassword) {
            return;
        }

        $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));
        $user->setPlainPassword(null);
    }
}

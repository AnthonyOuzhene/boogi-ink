<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Security\Voter\AdminUserVoter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
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
            ->setSearchFields(['name', 'email'])
            // Fiche, modification, suppression : un admin ne peut pas toucher au compte du super admin
            ->setEntityPermission(AdminUserVoter::MANAGE);
    }

    // Liste : un admin ne voit pas le compte du super admin (filtré dès la requête SQL)
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            // Les rôles sont stockés en JSON : on exclut les comptes qui contiennent ROLE_SUPER_ADMIN
            $queryBuilder
                ->andWhere('entity.roles NOT LIKE :superAdmin')
                ->setParameter('superAdmin', '%ROLE_SUPER_ADMIN%');
        }

        return $queryBuilder;
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
        // Tout compte créé ici est admin (jamais super admin : celui-ci se crée en ligne de commande,
        // app:create-admin --super). La modification d'un compte ne touche pas à ses rôles.
        $entityInstance->setRoles(['ROLE_ADMIN']);
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

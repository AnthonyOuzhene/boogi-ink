<?php

namespace App\Controller\Admin;

use App\Entity\Comment;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends AbstractCrudController<Comment>
 */
class CommentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Comment::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Avis')
            ->setEntityLabelInPlural("Livre d'or")
            ->setDefaultSort(['createdAt' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield DateTimeField::new('createdAt', 'Déposé le')->hideOnForm();
        // Nom tapé dans le formulaire public du livre d'or (pas de compte client)
        yield TextField::new('authorName', 'Auteur');
        yield TextField::new('title', 'Titre');
        yield TextareaField::new('message', 'Message')->hideOnIndex();
        yield TextField::new('projectName', 'Projet réalisé')->hideOnIndex();
        yield DateField::new('realisedAt', 'Date du tatouage')->hideOnIndex();
        // Interrupteur cliquable directement depuis la liste
        yield BooleanField::new('isPublished', 'Publié')->renderAsSwitch();
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters->add('isPublished');
    }
}

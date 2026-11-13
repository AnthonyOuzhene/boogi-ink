<?php

namespace App\Controller\Admin;

use App\Entity\Page;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends AbstractCrudController<Page>
 */
class PageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Page::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Page')
            ->setEntityLabelInPlural('Pages')
            ->setPageTitle(Crud::PAGE_INDEX, 'Pages (mentions légales, confidentialité…)')
            ->setDefaultSort(['title' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('title', 'Titre');
        // SlugField : se remplit automatiquement à partir du titre pendant la saisie
        yield SlugField::new('slug', 'Adresse')
            ->setTargetFieldName('title')
            ->setHelp("Adresse de la page sur le site : /pages/<adresse>. Attention : la modifier casse les anciens liens.");
        yield TextareaField::new('content', 'Contenu')
            ->setHelp('Une ligne vide entre deux paragraphes. Une ligne commençant par "## " devient un intertitre.')
            ->setNumOfRows(20)
            ->hideOnIndex();
        yield DateTimeField::new('updatedAt', 'Mise à jour')->hideOnForm();
    }
}

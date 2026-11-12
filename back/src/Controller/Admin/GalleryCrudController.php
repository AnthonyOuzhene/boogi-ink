<?php

namespace App\Controller\Admin;

use App\Entity\Gallery;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

/**
 * @extends AbstractCrudController<Gallery>
 */
class GalleryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Gallery::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Réalisation')
            ->setEntityLabelInPlural('Réalisations')
            ->setDefaultSort(['realisedAt' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield ImageField::new('mainPicture', 'Photo principale')
            ->setBasePath(Gallery::UPLOAD_PATH)
            ->setUploadDir('public/'.Gallery::UPLOAD_PATH)
            ->setUploadedFileNamePattern('[randomhash].[extension]')
            // obligatoire à la création, facultatif en modification (on garde l'ancienne)
            ->setRequired(Crud::PAGE_NEW === $pageName);
        yield TextField::new('name', 'Titre');
        yield AssociationField::new('category', 'Catégorie');
        yield DateField::new('realisedAt', 'Date de réalisation');
        yield UrlField::new('video', 'Lien vidéo')
            ->setHelp('Lien Instagram, YouTube… (facultatif)')
            ->hideOnIndex();
        // Chaque photo supplémentaire est éditée avec le formulaire de PictureCrudController.
        // by_reference=false : Symfony appelle addPicture()/removePicture(),
        // qui renseignent picture.gallery (sans ça, la clé étrangère resterait vide).
        yield CollectionField::new('pictures', 'Photos supplémentaires')
            ->useEntryCrudForm(PictureCrudController::class)
            ->setFormTypeOption('by_reference', false);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters->add('category');
    }
}

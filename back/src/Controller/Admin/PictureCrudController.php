<?php

namespace App\Controller\Admin;

use App\Entity\Gallery;
use App\Entity\Picture;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;

/**
 * Pas dans le menu : ce CRUD sert de "sous-formulaire" pour chaque photo
 * dans le formulaire d'une réalisation (voir GalleryCrudController).
 *
 * @extends AbstractCrudController<Picture>
 */
class PictureCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Picture::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield ImageField::new('filename', 'Photo')
            ->setBasePath(Gallery::UPLOAD_PATH)
            ->setUploadDir('public/'.Gallery::UPLOAD_PATH)
            ->setUploadedFileNamePattern('[randomhash].[extension]');
        yield IntegerField::new('position', 'Ordre')
            ->setHelp('Les photos sont affichées de la plus petite à la plus grande valeur.');
    }
}

<?php

namespace App\Controller\Admin;

use App\Entity\StudioPhoto;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Validator\Constraints\Image;

/**
 * @extends AbstractCrudController<StudioPhoto>
 */
class StudioPhotoCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return StudioPhoto::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Photo du salon')
            ->setEntityLabelInPlural('Photos du salon')
            ->setDefaultSort(['position' => 'ASC', 'id' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield ImageField::new('picture', 'Photo')
            ->setBasePath(StudioPhoto::UPLOAD_PATH)
            ->setUploadDir('public/'.StudioPhoto::UPLOAD_PATH)
            ->setUploadedFileNamePattern('[randomhash].[extension]')
            ->setFileConstraints(new Image(maxSize: '10M'))
            ->setRequired(Crud::PAGE_NEW === $pageName)
            ->setHelp('La première photo (ordre le plus petit) est affichée en grand. Elle est recadrée pour remplir son emplacement.');
        yield TextField::new('caption', 'Légende')
            ->setHelp('Exemple : « Le poste de travail ». Elle sert aussi de description pour les personnes malvoyantes.');
        yield IntegerField::new('position', 'Ordre');
    }
}

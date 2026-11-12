<?php

namespace App\Controller\Admin;

use App\Entity\Home;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends AbstractCrudController<Home>
 */
class HomeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Home::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Salon')
            ->setEntityLabelInPlural('Salon');
    }

    public function configureFields(string $pageName): iterable
    {
        yield ImageField::new('picture', 'Photo')
            ->setBasePath(Home::UPLOAD_PATH)
            ->setUploadDir('public/'.Home::UPLOAD_PATH)
            ->setUploadedFileNamePattern('[randomhash].[extension]')
            ->setRequired(false);
        yield TextField::new('name', 'Nom');
        yield TextField::new('address', 'Adresse')->hideOnIndex();
        yield TextField::new('zipCode', 'Code postal')->hideOnIndex();
        yield TextField::new('city', 'Ville');
        yield TelephoneField::new('phoneNumber', 'Téléphone');
        yield BooleanField::new('isOpen', 'Ouvert')->renderAsSwitch();
    }
}

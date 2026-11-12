<?php

namespace App\Controller\Admin;

use App\Entity\Service;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends AbstractCrudController<Service>
 */
class ServiceCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Service::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Prestation')
            ->setEntityLabelInPlural('Prestations')
            ->setDefaultSort(['name' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield ImageField::new('picture', 'Image')
            ->setBasePath(Service::UPLOAD_PATH)
            ->setUploadDir('public/'.Service::UPLOAD_PATH)
            ->setUploadedFileNamePattern('[randomhash].[extension]')
            ->setRequired(false);
        yield TextField::new('name', 'Nom');
        yield AssociationField::new('category', 'Catégorie');
        // La colonne est un DECIMAL : Doctrine la renvoie sous forme de chaîne
        yield NumberField::new('price', 'Prix (€)')
            ->setStoredAsString()
            ->setNumDecimals(2)
            ->setHelp('Laisser vide pour une prestation sur devis.');
        yield TextareaField::new('description', 'Description')->hideOnIndex();
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters->add('category');
    }
}

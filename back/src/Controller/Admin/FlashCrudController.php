<?php

namespace App\Controller\Admin;

use App\Entity\Flash;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Validator\Constraints\Image;

/**
 * @extends AbstractCrudController<Flash>
 */
class FlashCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Flash::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Flash')
            ->setEntityLabelInPlural('Flashs')
            ->setPageTitle(Crud::PAGE_INDEX, 'Flashs (motifs prêts à tatouer)')
            ->setDefaultSort(['isAvailable' => 'DESC', 'position' => 'ASC', 'id' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield ImageField::new('picture', 'Dessin')
            ->setBasePath(Flash::UPLOAD_PATH)
            ->setUploadDir('public/'.Flash::UPLOAD_PATH)
            ->setUploadedFileNamePattern('[randomhash].[extension]')
            ->setFileConstraints(new Image(maxSize: '8M'))
            // Obligatoire à la création seulement : en modification, on garde l'image existante
            ->setRequired(Crud::PAGE_NEW === $pageName)
            ->setHelp('Le dessin sur fond clair, cadré au plus près. Il est affiché en entier, sans recadrage.');
        yield TextField::new('name', 'Nom du motif');
        yield BooleanField::new('isAvailable', 'Disponible')
            ->renderAsSwitch()
            ->setHelp('Décochez quand le flash est réservé : il reste affiché, avec le badge « Réservé ».');
        yield TextField::new('size', 'Taille')
            ->setHelp('Exemple : « Environ 12 cm »')
            ->hideOnIndex();
        yield TextField::new('placement', 'Emplacements conseillés')
            ->setHelp('Exemple : « Avant-bras, mollet »')
            ->hideOnIndex();
        yield NumberField::new('price', 'Prix (€)')
            ->setStoredAsString()
            ->setNumDecimals(2)
            ->setHelp('Laisser vide pour « Sur devis ».');
        yield IntegerField::new('position', 'Ordre')
            ->setHelp("Du plus petit au plus grand. Les flashs disponibles s'affichent toujours avant les réservés.");
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters->add('isAvailable');
    }
}

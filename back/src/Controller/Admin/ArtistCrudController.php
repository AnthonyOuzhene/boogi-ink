<?php

namespace App\Controller\Admin;

use App\Entity\Artist;
use App\Repository\ArtistRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Validator\Constraints\Image;

/**
 * @extends AbstractCrudController<Artist>
 */
class ArtistCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly ArtistRepository $artistRepository,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Artist::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular("L'artiste")
            ->setEntityLabelInPlural("L'artiste")
            ->setPageTitle(Crud::PAGE_EDIT, "Modifier la présentation de l'artiste");
    }

    public function configureActions(Actions $actions): Actions
    {
        // Le site n'affiche qu'une fiche : une fois créée, on ne peut plus en ajouter
        if ($this->artistRepository->count() > 0) {
            $actions->disable(Action::NEW);
        }

        return $actions;
    }

    public function configureFields(string $pageName): iterable
    {
        yield ImageField::new('picture', 'Photo')
            ->setBasePath(Artist::UPLOAD_PATH)
            ->setUploadDir('public/'.Artist::UPLOAD_PATH)
            ->setUploadedFileNamePattern('[randomhash].[extension]')
            ->setFileConstraints(new Image(maxSize: '8M'))
            ->setRequired(false)
            ->setHelp('Portrait vertical de préférence. Il est affiché en entier, sans recadrage, et passé en noir et blanc sur le site.');
        yield TextField::new('firstName', 'Prénom');
        yield TextField::new('lastName', 'Nom');
        yield TextareaField::new('quote', 'Citation')
            ->setHelp('Une phrase courte, mise en avant sous le nom (300 caractères maximum).')
            ->setNumOfRows(3)
            ->hideOnIndex();
        yield TextareaField::new('biography', 'Présentation')
            ->setHelp('Parcours, façon de travailler… Laissez une ligne vide entre deux paragraphes.')
            ->setNumOfRows(12)
            ->hideOnIndex();
    }
}

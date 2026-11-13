<?php

namespace App\Controller\Admin;

use App\Entity\Artist;
use App\Repository\ArtistRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
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
        yield ImageField::new('picture', "Photo de la page d'accueil")
            ->setBasePath(Artist::UPLOAD_PATH)
            ->setUploadDir('public/'.Artist::UPLOAD_PATH)
            ->setUploadedFileNamePattern('[randomhash].[extension]')
            ->setFileConstraints(new Image(maxSize: '8M'))
            ->setRequired(false)
            ->setHelp("Photo de la page d'accueil. Portrait vertical de préférence : il est affiché en entier, sans recadrage, et passé en noir et blanc sur le site.");
        yield ImageField::new('atelierPicture', "Photo de la page L'atelier")
            ->setBasePath(Artist::UPLOAD_PATH)
            ->setUploadDir('public/'.Artist::UPLOAD_PATH)
            ->setUploadedFileNamePattern('[randomhash].[extension]')
            ->setFileConstraints(new Image(maxSize: '8M'))
            ->setRequired(false)
            ->setHelp("Facultative : si elle est vide, L'atelier reprend la photo de l'accueil. Mêmes conseils de format.")
            ->hideOnIndex();
        yield TextField::new('firstName', 'Prénom');
        yield TextField::new('lastName', 'Nom');
        yield TextareaField::new('quote', 'Citation')
            ->setHelp('Une phrase courte, mise en avant sous le nom (300 caractères maximum).')
            ->setNumOfRows(3)
            ->hideOnIndex();
        yield TextareaField::new('shortBio', "Présentation courte (page d'accueil)")
            ->setHelp('Deux ou trois phrases : son style, ce qui le distingue (400 caractères maximum).')
            ->setNumOfRows(3)
            ->hideOnIndex();
        yield TextareaField::new('biography', "Biographie complète (page L'atelier)")
            ->setHelp('Débuts, formation, façon de travailler… Laissez une ligne vide entre deux paragraphes. Les dates clés de son parcours se saisissent dans « Frises ».')
            ->setNumOfRows(12)
            ->hideOnIndex();
        // ArrayField : une ligne de saisie par élément, avec des boutons Ajouter / Supprimer
        yield ArrayField::new('influences', 'Influences')
            ->setHelp('Affichées en pastilles sous la biographie. Exemple : « Old school américain ». Pas de virgule dans un élément.')
            ->hideOnIndex();
    }
}

<?php

namespace App\Controller\Admin;

use App\Entity\Home;
use App\Repository\HomeRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use Symfony\Component\Validator\Constraints\Image;

/**
 * Fiche unique du salon : coordonnées, horaires, réseaux sociaux et haut de la page d'accueil.
 *
 * @extends AbstractCrudController<Home>
 */
class HomeCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly HomeRepository $homeRepository,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Home::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Salon')
            ->setEntityLabelInPlural('Salon')
            ->setPageTitle(Crud::PAGE_EDIT, 'Informations du salon');
    }

    public function configureActions(Actions $actions): Actions
    {
        // Le site n'affiche qu'un salon : une fois la fiche créée, on ne peut plus en ajouter
        if ($this->homeRepository->count() > 0) {
            $actions->disable(Action::NEW);
        }

        return $actions;
    }

    public function configureFields(string $pageName): iterable
    {
        // Les "fieldsets" regroupent les champs en blocs titrés dans le formulaire
        yield FormField::addFieldset('Coordonnées')->setIcon('fa fa-location-dot');
        yield TextField::new('name', 'Nom du salon');
        yield TextField::new('address', 'Adresse')->hideOnIndex();
        yield TextField::new('zipCode', 'Code postal')->hideOnIndex();
        yield TextField::new('city', 'Ville');
        yield TelephoneField::new('phoneNumber', 'Téléphone');
        yield EmailField::new('email', 'E-mail de contact')
            ->setHelp('Affiché sur le site et utilisé par le formulaire de contact.');

        yield FormField::addFieldset('Horaires')->setIcon('fa fa-clock');
        yield TextareaField::new('openingHours', "Horaires d'ouverture")
            ->setHelp('Une ligne par créneau. Exemple : « Mardi – Samedi : 10h – 19h », puis à la ligne : « Dimanche et lundi : fermé ».')
            ->setNumOfRows(5)
            ->hideOnIndex();
        yield BooleanField::new('isOpen', 'Salon ouvert')
            ->renderAsSwitch()
            ->setHelp('Décochez pendant les vacances ou une fermeture exceptionnelle : un message s\'affiche sur la page Contact.');

        yield FormField::addFieldset('Réseaux sociaux')->setIcon('fa fa-share-nodes');
        yield UrlField::new('instagramUrl', 'Instagram')->hideOnIndex();
        yield UrlField::new('facebookUrl', 'Facebook')->hideOnIndex();

        yield FormField::addFieldset("Haut de la page d'accueil")->setIcon('fa fa-image');
        yield ImageField::new('picture', 'Grande photo')
            ->setBasePath(Home::UPLOAD_PATH)
            ->setUploadDir('public/'.Home::UPLOAD_PATH)
            ->setUploadedFileNamePattern('[randomhash].[extension]')
            ->setFileConstraints(new Image(maxSize: '10M'))
            ->setRequired(false)
            ->setHelp('Format paysage, au moins 1920 px de large. Elle est passée en noir et blanc teinté de bleu sur le site.')
            ->hideOnIndex();
        yield TextField::new('heroTitle', 'Accroche — première ligne')
            ->setHelp('En blanc. Exemple : « L\'encre qui »')
            ->hideOnIndex();
        yield TextField::new('heroHighlight', 'Accroche — seconde ligne')
            ->setHelp('En cyan lumineux. Exemple : « vous ressemble »')
            ->hideOnIndex();

        yield FormField::addFieldset("Page L'atelier")->setIcon('fa fa-store');
        yield TextareaField::new('storyIntro', "Introduction de « L'histoire »")
            ->setHelp('Comment est né le salon, d\'où vient le nom… Les dates se saisissent dans « Frises ».')
            ->setNumOfRows(5)
            ->hideOnIndex();
        yield TextareaField::new('placeDescription', "Texte de « Le lieu »")
            ->setHelp('Une ou deux phrases sur l\'ambiance du salon, à côté des photos.')
            ->setNumOfRows(3)
            ->hideOnIndex();
    }
}

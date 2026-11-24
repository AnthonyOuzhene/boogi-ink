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
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

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
        // Libellés avec le logo du réseau : icônes Font Awesome "brands", déjà fournies par EasyAdmin.
        // label_html : le formulaire Symfony affiche le libellé comme du HTML (et non comme du texte brut).
        yield UrlField::new('instagramUrl', '<i class="fa-brands fa-instagram"></i> Instagram')
            ->setFormTypeOption('label_html', true)
            ->hideOnIndex();
        yield UrlField::new('facebookUrl', '<i class="fa-brands fa-facebook"></i> Facebook')
            ->setFormTypeOption('label_html', true)
            ->hideOnIndex();

        // La vidéo du haut de l'accueil est un fichier fixe du site (front/src/assets/videos) :
        // seule l'accroche se règle ici, et uniquement par le super admin (le développeur).
        // setPermission : pour un simple admin, ces champs n'existent pas dans le formulaire,
        // ils ne peuvent donc ni être vus ni être modifiés (même en trafiquant la page).
        yield FormField::addFieldset("Haut de la page d'accueil")->setIcon('fa fa-heading')
            ->setPermission('ROLE_SUPER_ADMIN');
        yield TextField::new('heroTitle', 'Accroche — première ligne')
            ->setHelp('En blanc. Exemple : « L\'encre qui »')
            ->setPermission('ROLE_SUPER_ADMIN')
            ->hideOnIndex();
        yield TextField::new('heroHighlight', 'Accroche — seconde ligne')
            ->setHelp('En cyan lumineux. Exemple : « vous ressemble »')
            ->setPermission('ROLE_SUPER_ADMIN')
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

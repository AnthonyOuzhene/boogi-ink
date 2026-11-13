<?php

namespace App\Controller\Admin;

use App\Entity\HomeCard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * Cartes "à jouer" de l'accueil, sous la grande photo.
 *
 * @extends AbstractCrudController<HomeCard>
 */
class HomeCardCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return HomeCard::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Carte')
            ->setEntityLabelInPlural("Cartes de l'accueil")
            ->setPageTitle(Crud::PAGE_INDEX, "Cartes de l'accueil")
            ->setHelp(Crud::PAGE_INDEX, "Les cartes à jouer sous la grande photo de l'accueil. Quatre cartes donnent le meilleur rendu (éventail sur ordinateur, grille 2 × 2 sur téléphone).")
            ->setDefaultSort(['position' => 'ASC', 'id' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IntegerField::new('position', 'Ordre')
            ->setHelp('De gauche à droite : la plus petite valeur en premier (10, 20, 30… pour pouvoir glisser une carte entre deux).');
        // Liste déroulante : le libellé est affiché, la clé (quill, swallow…) est enregistrée
        yield ChoiceField::new('icon', 'Symbole')
            ->setChoices(HomeCard::ICONS)
            ->setHelp('Dessiné dans deux coins opposés de la carte, comme sur une carte à jouer.');
        yield TextField::new('title', 'Titre')
            ->setHelp('Court : 2 ou 3 mots (60 caractères maximum).');
        yield TextareaField::new('text', 'Texte')
            ->setNumOfRows(3)
            ->setMaxLength(255)
            ->setHelp('Une ou deux phrases, environ 120 caractères : la carte est petite, surtout sur téléphone.');
    }
}

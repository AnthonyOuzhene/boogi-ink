<?php

namespace App\Controller\Admin;

use App\Entity\Milestone;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;

/**
 * @extends AbstractCrudController<Milestone>
 */
class MilestoneCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Milestone::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Date')
            ->setEntityLabelInPlural('Frises (histoire & parcours)')
            ->setDefaultSort(['timeline' => 'DESC', 'position' => 'ASC', 'id' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield ChoiceField::new('timeline', 'Frise')
            ->setChoices(Milestone::TIMELINES)
            ->renderExpanded()
            ->setHelp("« Histoire du salon » : frise verticale. « Parcours de l'artiste » : frise horizontale sous sa biographie (4 dates conseillées).");
        yield TextField::new('year', 'Année')
            ->setHelp('Texte libre : « 2012 », « 2015 – 2018 », « Aujourd\'hui »…');
        yield TextField::new('title', 'Titre');
        yield TextareaField::new('description', 'Détail')
            ->setNumOfRows(4)
            ->hideOnIndex();
        yield IntegerField::new('position', 'Ordre')
            ->setHelp('Dans chaque frise, de la plus petite à la plus grande valeur.');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters->add(ChoiceFilter::new('timeline', 'Frise')->setChoices(Milestone::TIMELINES));
    }
}

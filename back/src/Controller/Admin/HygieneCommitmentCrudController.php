<?php

namespace App\Controller\Admin;

use App\Entity\HygieneCommitment;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends AbstractCrudController<HygieneCommitment>
 */
class HygieneCommitmentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return HygieneCommitment::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Engagement')
            ->setEntityLabelInPlural('Hygiène & sécurité')
            ->setDefaultSort(['position' => 'ASC', 'id' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IntegerField::new('position', 'Ordre');
        yield ChoiceField::new('icon', 'Pictogramme')->setChoices(HygieneCommitment::ICONS);
        yield TextField::new('title', 'Titre');
        yield TextareaField::new('description', 'Description')->setNumOfRows(3);
    }
}

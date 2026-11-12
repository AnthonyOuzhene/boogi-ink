<?php

namespace App\Controller\Admin;

use App\Entity\Faq;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends AbstractCrudController<Faq>
 */
class FaqCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Faq::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Question')
            ->setEntityLabelInPlural('FAQ')
            ->setPageTitle(Crud::PAGE_INDEX, 'Questions fréquentes')
            // Même ordre que sur le site
            ->setDefaultSort(['position' => 'ASC', 'id' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IntegerField::new('position', 'Ordre')
            ->setHelp("Les questions s'affichent de la plus petite à la plus grande valeur.");
        yield TextField::new('question', 'Question');
        yield TextareaField::new('answer', 'Réponse')
            ->setHelp('Laissez une ligne vide entre deux paragraphes.')
            ->setNumOfRows(8)
            ->hideOnIndex();
    }
}

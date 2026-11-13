<?php

namespace App\Controller\Admin;

use App\Entity\SessionStep;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends AbstractCrudController<SessionStep>
 */
class SessionStepCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return SessionStep::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Étape')
            ->setEntityLabelInPlural("Déroulé d'une séance")
            ->setDefaultSort(['position' => 'ASC', 'id' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IntegerField::new('position', 'Ordre')
            ->setHelp('Le numéro affiché (01, 02…) suit cet ordre.');
        yield TextField::new('title', 'Titre');
        yield TextField::new('summary', "Résumé pour l'accueil")
            ->setHelp("Une phrase courte. L'accueil affiche les 5 premières étapes.");
        yield TextareaField::new('description', 'Description complète (L\'atelier)')
            ->setNumOfRows(5)
            ->hideOnIndex();
    }
}

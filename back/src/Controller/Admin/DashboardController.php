<?php

namespace App\Controller\Admin;

use App\Repository\ArtistRepository;
use App\Repository\CommentRepository;
use App\Repository\GalleryRepository;
use App\Repository\HomeRepository;
use App\Repository\ServiceRepository;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Menu\ControllerMenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

// Cet attribut crée la route /admin (nommée "admin") et toutes les routes des CRUD :
// admin_service_index, admin_service_edit, etc. (voir `ddev console debug:router`)
#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    // Injection de dépendances : Symfony fournit automatiquement les repositories
    public function __construct(
        private readonly CommentRepository $commentRepository,
        private readonly GalleryRepository $galleryRepository,
        private readonly ServiceRepository $serviceRepository,
        private readonly ArtistRepository $artistRepository,
        private readonly HomeRepository $homeRepository,
    ) {
    }

    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig', [
            'pendingComments' => $this->commentRepository->count(['isPublished' => false]),
            'galleryCount' => $this->galleryRepository->count(),
            'serviceCount' => $this->serviceRepository->count(),
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle("Boogi'ink");
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-home');

        yield MenuItem::section('Le salon');
        // Fiches uniques (salon, artiste) : le menu ouvre directement le formulaire
        yield $this->singleRecordMenuItem(HomeCrudController::class, 'Salon', 'fa fa-store', $this->homeRepository->findOneBy([]));
        yield $this->singleRecordMenuItem(ArtistCrudController::class, "L'artiste", 'fa fa-user-pen', $this->artistRepository->findOneBy([]));
        yield MenuItem::linkTo(ServiceCrudController::class, 'Prestations', 'fa fa-pen-nib');
        yield MenuItem::linkTo(CategoryCrudController::class, 'Catégories', 'fa fa-tags');
        yield MenuItem::linkTo(GalleryCrudController::class, 'Réalisations', 'fa fa-images');

        yield MenuItem::linkTo(FlashCrudController::class, 'Flashs', 'fa fa-bolt');

        yield MenuItem::section("Page L'atelier");
        yield MenuItem::linkTo(MilestoneCrudController::class, 'Frises (histoire & parcours)', 'fa fa-timeline');
        yield MenuItem::linkTo(StudioPhotoCrudController::class, 'Photos du salon', 'fa fa-camera');
        yield MenuItem::linkTo(SessionStepCrudController::class, "Déroulé d'une séance", 'fa fa-list-ol');
        yield MenuItem::linkTo(HygieneCommitmentCrudController::class, 'Hygiène & sécurité', 'fa fa-shield-halved');

        yield MenuItem::section('Site');
        yield MenuItem::linkTo(FaqCrudController::class, 'FAQ', 'fa fa-circle-question');
        yield MenuItem::linkTo(PageCrudController::class, 'Pages', 'fa fa-file-lines');

        yield MenuItem::section('Clients');
        yield MenuItem::linkTo(CommentCrudController::class, "Livre d'or", 'fa fa-comments');
        yield MenuItem::linkTo(UserCrudController::class, 'Utilisateurs', 'fa fa-users');

        // PictureCrudController n'est pas dans le menu : les photos se gèrent
        // directement dans le formulaire d'une réalisation (Gallery).
    }

    /**
     * Lien de menu vers une fiche unique : son formulaire d'édition si elle existe,
     * sinon le formulaire de création.
     */
    private function singleRecordMenuItem(string $crudController, string $label, string $icon, ?object $record): ControllerMenuItem
    {
        $item = MenuItem::linkTo($crudController, $label, $icon);

        return null === $record
            ? $item->setAction(Action::NEW)
            : $item->setAction(Action::EDIT)->setEntityId($record->getId());
    }
}

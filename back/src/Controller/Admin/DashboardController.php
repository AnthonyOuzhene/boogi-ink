<?php

namespace App\Controller\Admin;

use App\Repository\ArtistRepository;
use App\Repository\CommentRepository;
use App\Repository\GalleryRepository;
use App\Repository\ServiceRepository;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
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
        yield MenuItem::linkTo(HomeCrudController::class, 'Salon', 'fa fa-store');
        // Une seule fiche artiste : le menu ouvre directement son formulaire (ou la création s'il n'y en a pas)
        $artist = $this->artistRepository->findOneBy([]);
        yield null === $artist
            ? MenuItem::linkTo(ArtistCrudController::class, "L'artiste", 'fa fa-user-pen')->setAction(Action::NEW)
            : MenuItem::linkTo(ArtistCrudController::class, "L'artiste", 'fa fa-user-pen')->setAction(Action::EDIT)->setEntityId($artist->getId());
        yield MenuItem::linkTo(ServiceCrudController::class, 'Prestations', 'fa fa-pen-nib');
        yield MenuItem::linkTo(CategoryCrudController::class, 'Catégories', 'fa fa-tags');
        yield MenuItem::linkTo(GalleryCrudController::class, 'Réalisations', 'fa fa-images');
        yield MenuItem::linkTo(FaqCrudController::class, 'FAQ', 'fa fa-circle-question');

        yield MenuItem::section('Clients');
        yield MenuItem::linkTo(CommentCrudController::class, "Livre d'or", 'fa fa-comments');
        yield MenuItem::linkTo(UserCrudController::class, 'Utilisateurs', 'fa fa-users');

        // PictureCrudController n'est pas dans le menu : les photos se gèrent
        // directement dans le formulaire d'une réalisation (Gallery).
    }
}

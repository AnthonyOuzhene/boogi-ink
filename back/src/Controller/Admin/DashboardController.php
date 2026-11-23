<?php

namespace App\Controller\Admin;

use App\Entity\Comment;
use App\Repository\ArtistRepository;
use App\Repository\CommentRepository;
use App\Repository\FlashRepository;
use App\Repository\GalleryRepository;
use App\Repository\HomeRepository;
use App\Service\ContentChecklist;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Menu\ControllerMenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;

// Cet attribut crée la route /admin (nommée "admin") et toutes les routes des CRUD :
// admin_flash_index, admin_flash_edit, etc. (voir `ddev console debug:router`)
#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    // Injection de dépendances : Symfony fournit automatiquement les repositories et services
    public function __construct(
        private readonly CommentRepository $commentRepository,
        private readonly GalleryRepository $galleryRepository,
        private readonly FlashRepository $flashRepository,
        private readonly ArtistRepository $artistRepository,
        private readonly HomeRepository $homeRepository,
        private readonly ContentChecklist $contentChecklist,
        // Valeur lue dans .env (FRONT_URL) grâce à l'attribut Autowire
        #[Autowire('%env(FRONT_URL)%')]
        private readonly string $frontUrl,
    ) {
    }

    public function index(): Response
    {
        // "Vendredi 13 novembre" : IntlDateFormatter formate les dates dans la langue voulue
        $today = (new \IntlDateFormatter('fr_FR', pattern: 'EEEE d MMMM'))->format(new \DateTimeImmutable());

        return $this->render('admin/dashboard.html.twig', [
            'today' => $today,
            'salon' => $this->homeRepository->findOneBy([]),
            'pendingCount' => $this->commentRepository->count(['isPublished' => false]),
            'pendingComments' => $this->commentRepository->findBy(['isPublished' => false], ['createdAt' => 'DESC'], 3),
            'flashes' => $this->flashRepository->findBy([], ['isAvailable' => 'DESC', 'position' => 'ASC', 'id' => 'ASC'], 5),
            'availableFlashCount' => $this->flashRepository->count(['isAvailable' => true]),
            'reservedFlashCount' => $this->flashRepository->count(['isAvailable' => false]),
            'unpricedFlashCount' => $this->flashRepository->count(['price' => null]),
            'galleryCount' => $this->galleryRepository->count(),
            'checklist' => $this->contentChecklist->check(),
        ]);
    }

    /**
     * Bouton "Publier" du bloc "Avis à valider".
     * Une route Symfony classique (pas un CRUD EasyAdmin) : /admin/… est déjà réservé aux admins
     * par access_control, et le jeton CSRF prouve que le clic vient bien du tableau de bord.
     */
    #[Route('/admin/tableau-de-bord/avis/{id}/publier', name: 'admin_dashboard_publish_comment', methods: ['POST'])]
    public function publishComment(Comment $comment, Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isCsrfTokenValid('publish-comment-'.$comment->getId(), $request->getPayload()->getString('_token'))) {
            $this->addFlash('danger', 'Le formulaire a expiré, merci de réessayer.');

            return $this->redirectToRoute('admin');
        }

        $comment->setIsPublished(true);
        $entityManager->flush();
        $this->addFlash('success', sprintf('L\'avis « %s » est publié sur le site.', $comment->getTitle()));

        return $this->redirectToRoute('admin');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            // Le titre accepte du HTML : logo + nom aux couleurs du site
            ->setTitle('<img src="/admin-assets/logo.png" alt="" class="bo-logo"> Boogi\'<span class="bo-cyan">ink</span>')
            ->setFaviconPath('admin-assets/logo.png')
            ->setDefaultColorScheme('dark');
    }

    public function configureAssets(): Assets
    {
        // Feuille de style ajoutée à toutes les pages du backoffice (fichier dans public/admin-assets/ : pas public/admin/, qui masquerait la route /admin)
        return Assets::new()->addCssFile('admin-assets/admin.css');
    }

    public function configureUserMenu(UserInterface $user): UserMenu
    {
        return parent::configureUserMenu($user)
            ->addMenuItems([
                MenuItem::linkToUrl('Voir le site', 'fa fa-arrow-up-right-from-square', $this->frontUrl)->setLinkTarget('_blank'),
            ]);
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-home');

        yield MenuItem::section('Le salon');
        // Fiches uniques (salon, artiste) : le menu ouvre directement le formulaire
        yield $this->singleRecordMenuItem(HomeCrudController::class, 'Salon', 'fa fa-store', $this->homeRepository->findOneBy([]));
        yield $this->singleRecordMenuItem(ArtistCrudController::class, "L'artiste", 'fa fa-user-pen', $this->artistRepository->findOneBy([]));
        yield MenuItem::linkTo(CategoryCrudController::class, 'Catégories', 'fa fa-tags');
        yield MenuItem::linkTo(GalleryCrudController::class, 'Réalisations', 'fa fa-images');
        yield MenuItem::linkTo(FlashCrudController::class, 'Flashs', 'fa fa-bolt');

        yield MenuItem::section("Page L'atelier");
        yield MenuItem::linkTo(MilestoneCrudController::class, 'Frises (histoire & parcours)', 'fa fa-timeline');
        yield MenuItem::linkTo(StudioPhotoCrudController::class, 'Photos du salon', 'fa fa-camera');
        yield MenuItem::linkTo(SessionStepCrudController::class, "Déroulé d'une séance", 'fa fa-list-ol');
        yield MenuItem::linkTo(HygieneCommitmentCrudController::class, 'Hygiène & sécurité', 'fa fa-shield-halved');

        yield MenuItem::section('Site');
        yield MenuItem::linkTo(HomeCardCrudController::class, "Cartes de l'accueil", 'fa fa-layer-group');
        yield MenuItem::linkTo(FaqCrudController::class, 'FAQ', 'fa fa-circle-question');
        yield MenuItem::linkTo(PageCrudController::class, 'Pages', 'fa fa-file-lines');

        yield MenuItem::section('Avis & accès');
        // Pastille orange avec le nombre d'avis en attente (rien quand il n'y en a pas)
        $pending = $this->commentRepository->count(['isPublished' => false]);
        $guestbook = MenuItem::linkTo(CommentCrudController::class, "Livre d'or", 'fa fa-comments');
        yield $pending > 0 ? $guestbook->setBadge($pending, 'warning') : $guestbook;
        yield MenuItem::linkTo(UserCrudController::class, 'Administrateurs', 'fa fa-user-shield');

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

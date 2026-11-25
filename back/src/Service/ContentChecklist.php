<?php

namespace App\Service;

use App\Controller\Admin\ArtistCrudController;
use App\Controller\Admin\FaqCrudController;
use App\Controller\Admin\FlashCrudController;
use App\Controller\Admin\HomeCrudController;
use App\Controller\Admin\HygieneCommitmentCrudController;
use App\Controller\Admin\MilestoneCrudController;
use App\Controller\Admin\PageCrudController;
use App\Controller\Admin\SessionStepCrudController;
use App\Controller\Admin\StudioPhotoCrudController;
use App\Entity\Artist;
use App\Entity\Faq;
use App\Entity\Flash;
use App\Entity\Home;
use App\Entity\HygieneCommitment;
use App\Entity\Milestone;
use App\Entity\Page;
use App\Entity\SessionStep;
use App\Entity\StudioPhoto;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Repère les textes "à remplir" laissés dans le contenu du site : tout ce qui est entre crochets,
 * comme "[Année]" ou "[À remplacer]". Utilisé par le tableau de bord du backoffice.
 */
class ContentChecklist
{
    /** Un texte entre crochets, sur une seule ligne : [Année], [Réponse à rédiger.]… */
    private const PLACEHOLDER = '/\[[^\[\]\n]{2,}\]/u';

    /**
     * Ce qu'on vérifie : entité, nom dans le backoffice, contrôleur CRUD, champs (propriété => libellé).
     * Pour ajouter une rubrique, il suffit d'ajouter une ligne ici.
     */
    private const SOURCES = [
        [Home::class, 'Salon', HomeCrudController::class, ['openingHours' => 'horaires', 'heroTitle' => 'accroche', 'heroHighlight' => 'accroche', 'storyIntro' => "introduction de l'histoire", 'placeDescription' => 'texte du lieu']],
        [Artist::class, "L'artiste", ArtistCrudController::class, ['quote' => 'citation', 'shortBio' => 'présentation courte', 'biography' => 'biographie', 'influences' => 'influences']],
        [Milestone::class, 'Frises', MilestoneCrudController::class, ['year' => 'année', 'title' => 'titre', 'description' => 'détail']],
        [StudioPhoto::class, 'Photos du salon', StudioPhotoCrudController::class, ['caption' => 'légende']],
        [SessionStep::class, "Déroulé d'une séance", SessionStepCrudController::class, ['title' => 'titre', 'summary' => 'résumé', 'description' => 'description']],
        [HygieneCommitment::class, 'Hygiène & sécurité', HygieneCommitmentCrudController::class, ['title' => 'titre', 'description' => 'description']],
        [Faq::class, 'FAQ', FaqCrudController::class, ['question' => 'question', 'answer' => 'réponse']],
        [Page::class, 'Pages', PageCrudController::class, ['title' => 'titre', 'content' => 'contenu']],
        [Flash::class, 'Flashs', FlashCrudController::class, ['name' => 'nom', 'size' => 'taille', 'placement' => 'emplacements']],
    ];

    /**
     * Contenus réservés au super admin (le développeur) : l'admin du salon ne peut pas les modifier,
     * on ne les lui signale donc pas (le lien mènerait à une page interdite).
     * Garder en accord avec security.yaml (access_control) et les setPermission() des CRUD.
     */
    private const SUPER_ADMIN_ONLY_SOURCES = [HygieneCommitment::class];
    private const SUPER_ADMIN_ONLY_FIELDS = [Home::class => ['heroTitle', 'heroHighlight']];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly Security $security,
    ) {
    }

    /**
     * @return array{
     *     items: list<array{label: string, detail: string, count: int, url: string}>,
     *     total: int,
     *     progress: int,
     * }
     */
    public function check(): array
    {
        $accessor = PropertyAccess::createPropertyAccessor();
        $items = [];
        $filled = 0;   // champs remplis (avec ou sans crochets)
        $pending = 0;  // champs qui contiennent encore des crochets
        $isSuperAdmin = $this->security->isGranted('ROLE_SUPER_ADMIN');

        foreach (self::SOURCES as [$class, $label, $crudController, $fields]) {
            if (!$isSuperAdmin) {
                if (in_array($class, self::SUPER_ADMIN_ONLY_SOURCES, true)) {
                    continue;
                }
                $fields = array_diff_key($fields, array_flip(self::SUPER_ADMIN_ONLY_FIELDS[$class] ?? []));
            }

            $records = $this->entityManager->getRepository($class)->findAll();
            $todoFields = [];   // libellés des champs concernés (fiche unique)
            $todoRecords = [];  // fiches concernées (listes)
            $count = 0;

            foreach ($records as $record) {
                foreach ($fields as $property => $fieldLabel) {
                    $value = $accessor->getValue($record, $property);
                    $text = is_array($value) ? implode("\n", $value) : (string) $value;
                    if ('' === trim($text)) {
                        continue; // champ vide : facultatif, ou déjà signalé par le formulaire
                    }
                    ++$filled;
                    if (preg_match(self::PLACEHOLDER, $text)) {
                        ++$count;
                        $todoFields[$fieldLabel] = $fieldLabel;
                        $todoRecords[spl_object_id($record)] = (string) $record;
                    }
                }
            }

            if (0 === $count) {
                continue;
            }
            $pending += $count;

            // Une seule fiche (salon, artiste) : on liste les champs et on ouvre directement le formulaire.
            // Une liste : on cite les premières fiches concernées et on ouvre la liste.
            $single = 1 === count($records);
            $items[] = [
                'label' => $label,
                'detail' => $single
                    ? ucfirst(implode(', ', $todoFields))
                    : $this->summarize(array_values($todoRecords)),
                'count' => $count,
                'url' => $this->adminUrlGenerator
                    ->unsetAll()
                    ->setController($crudController)
                    ->setAction($single ? Action::EDIT : Action::INDEX)
                    ->setEntityId($single ? $records[0]->getId() : null)
                    ->generateUrl(),
            ];
        }

        return [
            'items' => $items,
            'total' => $pending,
            'progress' => $filled > 0 ? (int) floor(100 * ($filled - $pending) / $filled) : 100,
        ];
    }

    /** ["[Les débuts]", "Apprentissage", …] → "8 fiches : [Les débuts], Apprentissage…" */
    private function summarize(array $names): string
    {
        $shown = implode(', ', array_slice($names, 0, 2));

        return sprintf('%d fiche%s : %s%s', count($names), count($names) > 1 ? 's' : '', $shown, count($names) > 2 ? '…' : '');
    }
}

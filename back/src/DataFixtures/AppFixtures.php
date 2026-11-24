<?php

namespace App\DataFixtures;

use App\Entity\Artist;
use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Faq;
use App\Entity\Flash;
use App\Entity\Gallery;
use App\Content\LegalPages;
use App\Entity\Home;
use App\Entity\HomeCard;
use App\Entity\HygieneCommitment;
use App\Entity\Milestone;
use App\Entity\Page;
use App\Entity\Picture;
use App\Entity\SessionStep;
use App\Entity\StudioPhoto;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Données de démonstration pour le développement.
 *
 *   ddev console doctrine:fixtures:load
 *
 * ⚠ Cette commande VIDE la base avant de la remplir.
 *
 * Compte créé (le seul : le site n'a pas d'espace client) :
 *   - admin@boogi-ink.local / admin1234  (administrateur, accès au backoffice)
 */
class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // --- Le salon ---
        $home = (new Home())
            ->setName("Boogi'ink Tattoo")
            ->setAddress('332 rue Louis Gillain')
            ->setZipCode('27210')
            ->setCity('Beuzeville')
            ->setPhoneNumber('09 80 49 43 32')
            ->setEmail('ladyboogiink@gmail.com')
            ->setInstagramUrl('https://www.instagram.com/julienbourgarit/')
            ->setFacebookUrl('https://www.facebook.com/julienbourgarit/')
            ->setOpeningHours("[Mardi – Samedi : horaires à préciser]\n[Dimanche et lundi : fermé]")
            ->setHeroTitle("L'encre qui")
            ->setHeroHighlight('te ressemble')
            ->setStoryIntro("[Comment est né Boogi'ink, et d'où vient le nom.]")
            ->setPlaceDescription("[Une phrase sur l'ambiance du lieu.]")
            ->setIsOpen(true);
        $manager->persist($home);

        // --- L'artiste (textes à compléter dans le backoffice) ---
        $manager->persist((new Artist())
            ->setFirstName('Julien')
            ->setLastName('Bourgarit')
            ->setPicture($this->copyImage('artist.jpg', Artist::UPLOAD_PATH))
            // Photo de Julien dans l'atelier (page L'atelier)
            ->setAtelierPicture($this->copyImage('artist-atelier.jpg', Artist::UPLOAD_PATH))
            ->setQuote('[Une phrase du tatoueur qui résume sa vision du tatouage.]')
            ->setShortBio("[Deux lignes d'accroche sur Julien : son style, ce qui le distingue.]")
            ->setInfluences(['[Influence 1]', '[Influence 2]', '[Influence 3]'])
            ->setBiography("[Son parcours : depuis quand il tatoue, comment il a appris, ce qui l'a amené à ouvrir Boogi'ink.]\n\n[Sa façon de travailler : l'échange avec le client, le dessin sur mesure, l'accompagnement jusqu'à la cicatrisation.]"));

        // --- FAQ (réponses à rédiger dans le backoffice) ---
        $questions = [
            "Quel est le prix d'un tatouage ?",
            'Quelles sont les zones les plus douloureuses ?',
            'Quels sont les soins pendant la cicatrisation ?',
        ];
        foreach ($questions as $position => $question) {
            $manager->persist((new Faq())
                ->setQuestion($question)
                ->setAnswer('[Réponse à rédiger.]')
                ->setPosition(($position + 1) * 10)); // 10, 20, 30 : on peut glisser une question entre deux
        }

        // --- Pages légales : même texte de départ qu'en production (App\Content\LegalPages) ---
        foreach (LegalPages::all() as $page) {
            $manager->persist((new Page())->setTitle($page['title'])->setSlug($page['slug'])->setContent($page['content']));
        }

        // --- Catégories (styles relevés sur l'Instagram du salon) ---
        $categories = [];
        foreach (['Graphique', 'Sketch', 'Mandala', 'Pattern', 'Pop culture', 'Dotwork', 'Fine line', 'Floral'] as $name) {
            $categories[$name] = (new Category())->setName($name);
            $manager->persist($categories[$name]);
        }

        // --- Accueil : cartes à jouer sous la grande photo (textes à valider avec le salon) ---
        $homeCards = [
            ['quill', 'Dessins sur mesure', 'Chaque tatouage est dessiné pour toi, à partir de ton idée. Pas de copie, pas de modèle tout fait.'],
            ['swallow', 'Sur rendez-vous', "Je prends le temps d'échanger sur ton projet avant de tatouer, au calme et sans attente."],
            ['needle', 'Hygiène irréprochable', 'Aiguilles stériles à usage unique, poste désinfecté à chaque séance : ta peau est entre de bonnes mains.'],
            ['compass', 'Conseils & suivi', "Avant, pendant et après la séance : je t'accompagne jusqu'à la cicatrisation complète."],
        ];
        foreach ($homeCards as $position => [$icon, $title, $text]) {
            $manager->persist((new HomeCard())
                ->setPosition(($position + 1) * 10)
                ->setIcon($icon)
                ->setTitle($title)
                ->setText($text));
        }

        // --- Réalisations (photos copiées dans public/uploads/galleries) ---
        $galleries = [
            ['Portrait sketch', 'tattoo1.jpg', 'Sketch', '-10 days', ['tattoo3.jpg', 'tattoo4.jpg']],
            ['Hirondelle dotwork', 'tattoo2.png', 'Dotwork', '-25 days', []],
            ['Fleur fine line', 'tattoo3.jpg', 'Fine line', '-40 days', []],
            ['Manchette graphique', 'tattoo4.jpg', 'Graphique', '-2 months', ['tattoo6.jpg']],
            ['Rose', 'tattoo5.jpg', 'Floral', '-3 months', []],
            ['Ancre', 'tattoo6.jpg', 'Pattern', '-5 months', []],
        ];
        foreach ($galleries as [$name, $mainPicture, $category, $date, $extraPictures]) {
            $gallery = (new Gallery())
                ->setName($name)
                // Démo du curseur "du dessin à la peau" sur la première réalisation
                ->setSketch('Portrait sketch' === $name ? $this->copyImage('tattoo4.jpg') : null)
                // Démo de l'annotation "coup de cœur ♥"
                ->setIsFavorite('Fleur fine line' === $name)
                ->setDescription("[Texte de démo] Le projet du client, la zone tatouée, le nombre de séances.\n\n[Un mot sur le style ou la technique utilisée.]")
                ->setMainPicture($this->copyImage($mainPicture))
                ->setCategory($categories[$category])
                ->setRealisedAt(new \DateTimeImmutable($date));
            foreach ($extraPictures as $position => $file) {
                $gallery->addPicture((new Picture())
                    ->setFilename($this->copyImage($file))
                    ->setPosition($position + 1));
            }
            $manager->persist($gallery); // les photos suivent grâce à cascade: ['persist']
        }

        // --- L'atelier : frises (dates à compléter avec le salon) ---
        $milestones = [
            [Milestone::TIMELINE_SALON, '[Année]', '[Les débuts]', '[Premier atelier, premiers clients…]'],
            [Milestone::TIMELINE_SALON, '[Année]', "[Ouverture de Boogi'ink]", "[L'installation à Beuzeville, rue Louis Gillain.]"],
            [Milestone::TIMELINE_SALON, '[Année]', '[Une étape marquante]', '[Agrandissement, nouveau style, récompense…]'],
            [Milestone::TIMELINE_SALON, "Aujourd'hui", '[Le salon aujourd\'hui]', "[Ce qui fait Boogi'ink en quelques mots.]"],
            [Milestone::TIMELINE_ARTIST, '[Année]', 'Premier tatouage', '[Détail]'],
            [Milestone::TIMELINE_ARTIST, '[Année]', 'Apprentissage', '[Où, avec qui]'],
            [Milestone::TIMELINE_ARTIST, '[Année]', 'Conventions', '[Lesquelles]'],
            [Milestone::TIMELINE_ARTIST, '[Année]', "Boogi'ink", '[Ouverture du salon]'],
        ];
        foreach ($milestones as $position => [$timeline, $year, $title, $description]) {
            $manager->persist((new Milestone())
                ->setTimeline($timeline)
                ->setYear($year)
                ->setTitle($title)
                ->setDescription($description)
                ->setPosition(($position + 1) * 10));
        }

        // --- L'atelier : photos du lieu (images de démo, à remplacer par de vraies photos du salon) ---
        $studioPhotos = [
            ['banner.jpg', "[À remplacer] L'entrée du salon"],
            ['tattoo1.jpg', '[À remplacer] Le poste de travail'],
            ['tattoo3.jpg', '[À remplacer] Le matériel'],
            ['tattoo5.jpg', "[À remplacer] L'espace d'attente"],
            ['tattoo6.jpg', '[À remplacer] Un détail de déco'],
        ];
        foreach ($studioPhotos as $position => [$file, $caption]) {
            $manager->persist((new StudioPhoto())
                ->setPicture($this->copyImage($file, StudioPhoto::UPLOAD_PATH))
                ->setCaption($caption)
                ->setPosition(($position + 1) * 10));
        }

        // --- L'atelier : déroulé d'une séance ---
        $steps = [
            ['On parle de ton projet', 'Ton idée, l\'emplacement, la taille.', "Par message ou au salon : ton idée, l'emplacement, la taille, tes références."],
            ['Julien dessine ton motif', 'Un dessin créé sur mesure.', "Un dessin sur mesure, ajusté avec toi jusqu'à ce qu'il te ressemble."],
            ['Devis et rendez-vous', 'Prix et date validés ensemble.', 'Le prix et la date de la séance sont fixés ensemble. [Acompte : montant et conditions.]'],
            ['Le jour J', 'Dans un cadre propre et serein.', "Pose du calque, dernières retouches, puis la séance. [Conseils : bien manger, dormir, éviter l'alcool la veille…]"],
            ['Les soins à la maison', 'Les conseils de cicatrisation.', 'Tu repars avec les consignes de cicatrisation. [Fiche de soins à télécharger.]'],
            ['La retouche', 'Pour un résultat parfait.', '[Conditions de la retouche : délai, gratuite ou non.]'],
        ];
        foreach ($steps as $position => [$title, $summary, $description]) {
            $manager->persist((new SessionStep())
                ->setTitle($title)
                ->setSummary($summary)
                ->setDescription($description)
                ->setPosition(($position + 1) * 10));
        }

        // --- L'atelier : hygiène & sécurité (engagements à faire valider par le salon) ---
        $commitments = [
            ['shield', 'Matériel stérile', 'Aiguilles et cartouches à usage unique, ouvertes devant toi.'],
            ['ink', 'Encres conformes', 'Des encres respectant la réglementation européenne.'],
            ['training', 'Formation hygiène', 'Formation hygiène et salubrité suivie par le tatoueur.'],
            ['clean', 'Poste désinfecté', 'Surfaces nettoyées et protégées entre chaque client.'],
        ];
        foreach ($commitments as $position => [$icon, $title, $description]) {
            $manager->persist((new HygieneCommitment())
                ->setIcon($icon)
                ->setTitle($title)
                ->setDescription($description)
                ->setPosition(($position + 1) * 10));
        }

        // --- Flashs (prix fixe obligatoire ; montants de démo, à remplacer par les vrais) ---
        $flashes = [
            ['Montre à gousset', 'tattoo2.png', 'Environ 12 cm', 'Avant-bras, mollet', '150.00', true],
            ['Calavera', 'tattoo4.jpg', 'Environ 15 cm', 'Bras, cuisse', '180.00', true],
            ['Montre à gousset — mini', 'tattoo2.png', 'Environ 6 cm', 'Poignet, cheville', '90.00', false],
        ];
        foreach ($flashes as $position => [$name, $file, $size, $placement, $price, $available]) {
            $manager->persist((new Flash())
                ->setName($name)
                ->setPicture($this->copyImage($file, Flash::UPLOAD_PATH))
                ->setSize($size)
                ->setPlacement($placement)
                ->setPrice($price)
                ->setIsAvailable($available)
                ->setPosition(($position + 1) * 10));
        }

        // --- Administrateur (seul type de compte : le site n'a plus d'espace client) ---
        $manager->persist($this->createUser('admin@boogi-ink.local', 'Admin', 'admin1234', ['ROLE_ADMIN']));

        // --- Livre d'or (avis déposés par le formulaire public : un simple nom, pas de compte) ---
        $comments = [
            ['Julie', 'Une super expérience', "Accueil chaleureux, de très bons conseils et un résultat au-delà de mes attentes. Je reviendrai !", 'Rose sur l\'avant-bras', true],
            ['Paul', 'Très pro', 'Salon impeccable, respect strict de l\'hygiène, et un dessin vraiment personnalisé.', 'Ancre old school', true],
            ['Julie', 'Retouche parfaite', 'Retouche rapide et soignée, merci !', null, true],
            ['Paul', 'En attente de validation', "Cet avis n'apparaît pas sur le site tant qu'il n'est pas publié dans le backoffice.", null, false],
        ];
        foreach ($comments as [$authorName, $title, $message, $project, $published]) {
            $manager->persist((new Comment())
                ->setAuthorName($authorName)
                ->setTitle($title)
                ->setMessage($message)
                ->setProjectName($project)
                ->setIsPublished($published));
        }

        $manager->flush();
    }

    private function createUser(string $email, string $name, string $plainPassword, array $roles = []): User
    {
        $user = (new User())->setEmail($email)->setName($name)->setRoles($roles);

        return $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));
    }

    /** Copie une image de fixtures/images vers public/<dossier d'upload> et renvoie son nom */
    private function copyImage(string $file, string $uploadPath = Gallery::UPLOAD_PATH): string
    {
        $target = 'fixture-'.$file;
        (new Filesystem())->copy(
            $this->projectDir.'/fixtures/images/'.$file,
            $this->projectDir.'/public/'.$uploadPath.'/'.$target,
        );

        return $target;
    }
}

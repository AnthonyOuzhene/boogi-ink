<?php

namespace App\DataFixtures;

use App\Entity\Artist;
use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Faq;
use App\Entity\Gallery;
use App\Entity\Home;
use App\Entity\Page;
use App\Entity\Picture;
use App\Entity\Service;
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
 * Comptes créés :
 *   - admin@boogi-ink.local / admin1234  (administrateur, accès au backoffice)
 *   - julie@example.com     / client1234 (cliente)
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
            ->setPicture($this->copyImage('banner.jpg', Home::UPLOAD_PATH))
            ->setHeroTitle("L'encre qui")
            ->setHeroHighlight('vous ressemble')
            ->setIsOpen(true);
        $manager->persist($home);

        // --- L'artiste (textes à compléter dans le backoffice) ---
        $manager->persist((new Artist())
            ->setFirstName('Julien')
            ->setLastName('Bourgarit')
            ->setPicture($this->copyImage('artist.jpg', Artist::UPLOAD_PATH))
            ->setQuote('[Une phrase du tatoueur qui résume sa vision du tatouage.]')
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

        // --- Pages de contenu (texte juridique à rédiger avec le salon) ---
        $pages = [
            ['Mentions légales', 'mentions-legales', "## Éditeur du site\n\n[Nom de l'entreprise, forme juridique, adresse du siège, numéro SIRET, nom du responsable de la publication.]\n\n## Hébergement\n\n[Nom, adresse et téléphone de l'hébergeur.]\n\n## Propriété intellectuelle\n\n[Les photos des réalisations sont la propriété du salon et ne peuvent pas être réutilisées sans autorisation.]"],
            ['Politique de confidentialité', 'politique-de-confidentialite', "## Données collectées\n\n[Création de compte : pseudo, e-mail, mot de passe chiffré. Livre d'or : avis publiés.]\n\n## Utilisation\n\n[À quoi servent ces données, combien de temps elles sont conservées.]\n\n## Vos droits\n\n[Accès, rectification, suppression : comment exercer ses droits (adresse e-mail de contact).]"],
            ['Règles sanitaires', 'regles-sanitaires', "## Avant la séance\n\n[Conseils de préparation.]\n\n## Hygiène au salon\n\n[Matériel stérile à usage unique, désinfection…]\n\n## Après la séance\n\n[Soins de cicatrisation.]"],
        ];
        foreach ($pages as [$title, $slug, $content]) {
            $manager->persist((new Page())->setTitle($title)->setSlug($slug)->setContent($content));
        }

        // --- Catégories ---
        $categories = [];
        foreach (['Réaliste', 'Old school', 'Graphique', 'Lettrage'] as $name) {
            $categories[$name] = (new Category())->setName($name);
            $manager->persist($categories[$name]);
        }

        // --- Prestations (prix null = sur devis) ---
        $services = [
            ['Petit tatouage', '80.00', 'Motif de moins de 5 cm. Idéal pour un premier tatouage.', 'Graphique'],
            ['Tatouage moyen', '150.00', 'Motif de 5 à 15 cm, dessin personnalisé inclus.', 'Réaliste'],
            ['Grande pièce', null, 'Bras, dos, jambe… Projet sur plusieurs séances, établi sur devis.', 'Réaliste'],
            ['Lettrage', '60.00', 'Prénom, date ou citation, dans la typographie de votre choix.', 'Lettrage'],
            ['Recouvrement (cover)', null, "Recouvrir ou retravailler un ancien tatouage. Sur devis après rendez-vous.", null],
            ['Retouche', '40.00', "Retouche d'un tatouage réalisé au salon.", null],
        ];
        foreach ($services as [$name, $price, $description, $category]) {
            $manager->persist((new Service())
                ->setName($name)
                ->setPrice($price)
                ->setDescription($description)
                ->setCategory($category ? $categories[$category] : null));
        }

        // --- Réalisations (photos copiées dans public/uploads/galleries) ---
        $galleries = [
            ['Portrait réaliste', 'tattoo1.jpg', 'Réaliste', '-10 days', ['tattoo3.jpg', 'tattoo4.jpg']],
            ['Hirondelle old school', 'tattoo2.png', 'Old school', '-25 days', []],
            ['Fleur fine line', 'tattoo3.jpg', 'Graphique', '-40 days', []],
            ['Manchette graphique', 'tattoo4.jpg', 'Graphique', '-2 months', ['tattoo6.jpg']],
            ['Rose et lettrage', 'tattoo5.jpg', 'Lettrage', '-3 months', []],
            ['Ancre traditionnelle', 'tattoo6.jpg', 'Old school', '-5 months', []],
        ];
        foreach ($galleries as [$name, $mainPicture, $category, $date, $extraPictures]) {
            $gallery = (new Gallery())
                ->setName($name)
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

        // --- Utilisateurs ---
        $admin = $this->createUser('admin@boogi-ink.local', 'Admin', 'admin1234', ['ROLE_ADMIN']);
        $julie = $this->createUser('julie@example.com', 'Julie', 'client1234');
        $paul = $this->createUser('paul@example.com', 'Paul', 'client1234');
        $manager->persist($admin);
        $manager->persist($julie);
        $manager->persist($paul);

        // --- Livre d'or ---
        $comments = [
            [$julie, 'Une super expérience', "Accueil chaleureux, de très bons conseils et un résultat au-delà de mes attentes. Je reviendrai !", 'Rose sur l\'avant-bras', true],
            [$paul, 'Très pro', 'Salon impeccable, respect strict de l\'hygiène, et un dessin vraiment personnalisé.', 'Ancre old school', true],
            [$julie, 'Retouche parfaite', 'Retouche rapide et soignée, merci !', null, true],
            [$paul, 'En attente de validation', "Cet avis n'apparaît pas sur le site tant qu'il n'est pas publié dans le backoffice.", null, false],
        ];
        foreach ($comments as [$author, $title, $message, $project, $published]) {
            $manager->persist((new Comment())
                ->setAuthor($author)
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

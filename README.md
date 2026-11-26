# Boogi'ink

Site du salon de tatouage Boogi'ink.

| Dossier | Contenu |
|---|---|
| `back/` | Symfony 7.4 LTS : API publique (API Platform + un contrôleur pour le livre d'or) et backoffice (EasyAdmin), MariaDB, le tout dans DDEV |
| `front/` | React 19 + Vite : le site public (pas d'espace client) |

## Prérequis

- [DDEV](https://ddev.com) (avec Docker ou Colima)
- Node.js 22 ou plus récent

## Démarrer le back

```bash
cd back
ddev start                               # conteneurs PHP 8.4 + MariaDB 11.4
ddev composer install                    # dépendances PHP
ddev console doctrine:migrations:migrate # crée / met à jour les tables
ddev console doctrine:fixtures:load      # données de démo (⚠ vide la base)
```

Les fixtures créent un compte de démo par rôle (identifiants dans `back/src/DataFixtures/AppFixtures.php`, à n'utiliser qu'en local). Le site n'a pas d'espace client : seuls existent des comptes du backoffice.

**Deux rôles** (`role_hierarchy` dans `back/config/packages/security.yaml`) :

- `ROLE_ADMIN` (le salon) : tout le backoffice, **sauf** l'accroche du haut de l'accueil (2 titres, menu Salon), les cartes de l'accueil, les engagements « Hygiène & sécurité » de L'atelier, et les comptes super admin (qu'il ne voit pas dans « Administrateurs »).
- `ROLE_SUPER_ADMIN` (le développeur) : tous les droits de l'admin, plus ceux-là.

Restrictions : `setPermission('ROLE_SUPER_ADMIN')` sur les champs de `HomeCrudController` ; règle `access_control` (adresses `home-card` et `hygiene-commitment`) dans `security.yaml` + menus masqués dans `DashboardController` ; le voter `back/src/Security/Voter/AdminUserVoter.php` pour les comptes. Le tableau de bord (`ContentChecklist`) ne signale à l'admin que les contenus qu'il peut modifier. Pour réserver une autre rubrique : ajouter son adresse à la règle `access_control`, `->setPermission('ROLE_SUPER_ADMIN')` sur son menu, et l'entité dans `ContentChecklist::SUPER_ADMIN_ONLY_SOURCES`. Un compte créé depuis le backoffice est toujours admin ; un super admin se crée en ligne de commande.

Pour un vrai compte : `ddev console app:create-admin vous@exemple.fr "Votre nom"` (admin), ou avec `--super` (super admin).

Le bouton « Voir le site » du backoffice pointe vers `FRONT_URL` (`back/.env`, par défaut `http://localhost:5180`, le port du front fixé dans `front/vite.config.js`). Pour une autre adresse, surchargez-la dans `back/.env.local`.

- Backoffice : adresse volontairement peu devinable (pas `/admin`, testée par les robots), définie par le `routePath` de `DashboardController` ; le même préfixe doit figurer dans `access_control` (`back/config/packages/security.yaml`).
- Documentation de l'API (partie API Platform) : https://boogi-ink.ddev.site/api/docs

## Démarrer le front

```bash
cd front
npm install
npm run dev        # http://localhost:5180 (port fixé dans front/vite.config.js)
```

L'adresse du back est dans `front/.env` (`VITE_BACKEND_URL`).

### Organisation du front (`front/src/`)

```
main.jsx, App.jsx
api/  context/  hooks/  utils/  assets/
styles/        styles communs à tout le site : global.scss importe _variables, _base, _layout,
               _typography, _paper, _utilities, _filters, _buttons, _forms, _toastify
components/    rangés par sujet, chaque composant = son .jsx + son .scss côte à côte
  layout/      Header, Footer, MobileBookingBar
  ui/          PageHero, Scribble, SocialLinks (petites briques réutilisables)
  gallery/     GalleryGrid, GalleryViewer, SketchCompare
  flash/       FlashGallery
  guestbook/   GuestbookCarousel, CommentForm
  faq/         FaqList, FaqSection
  salon/       SalonCards, ArtistSection, SessionSteps, InstagramFeed
  atelier/     Timeline, StudioGallery, HygieneCards
pages/         une page = son .jsx + son .scss (HomePage, ContactPage, VisitorBookPage…)
```

- **Où ranger un style ?** Commun à tout le site → un fichier de `styles/` ; propre à un composant ou à une page → son `.scss`, à côté de son `.jsx`.
- **Imports** : `@/` = `src/` (`import GalleryGrid from '@/components/gallery/GalleryGrid'`), réglé dans `vite.config.js` et `jsconfig.json`. Un fichier du même dossier s'importe en `./`.
- Les couleurs et tailles sont des variables CSS (`--cyan`…, dans `styles/_variables.scss`) : utilisables dans tous les `.scss` sans import. Pas de `@extend` entre fichiers (chaque `.scss` est compilé à part).

## L'API

Toutes les routes commencent par `/api`. Elles sont déclarées à **deux endroits**, selon une règle simple :

> **Lire la vitrine publique → API Platform** (attribut `#[ApiResource]` sur l'entité).
> **Déposer un avis dans le livre d'or → `GuestbookController`** (`back/src/Controller/Api/`), la seule écriture possible.

### Vitrine publique (API Platform, lecture seule)

Déclarées par `#[ApiResource]` dans `back/src/Entity/`. Aucune écriture possible par l'API : le contenu se gère dans le backoffice.

| Route | Contenu | Entité |
|---|---|---|
| `GET /api/homes`, `/api/homes/{id}` | Fiche du salon : coordonnées, horaires, réseaux, accroche de l'accueil | `Home` |
| `GET /api/artists`, `/api/artists/{id}` | Présentation du tatoueur | `Artist` |
| `GET /api/categories`, `/api/categories/{id}` | Styles de tatouage | `Category` |
| `GET /api/galleries` (`?category=fine-line`, le slug du style), `/api/galleries/{id}` | Réalisations : `slug` (titre dans l'URL du site : `?realisation=manchette-graphique`), photos, dessin préparatoire, timelapse (`timelapseUrl`), lien Instagram (`instagramUrl`) | `Gallery`, `Picture` |
| `GET /api/comments`, `/api/comments/{id}` | Livre d'or : **avis publiés uniquement** (filtre `Doctrine/PublishedCommentExtension`) | `Comment` |
| `GET /api/home_cards` | Cartes à jouer de l'accueil (points forts du salon) | `HomeCard` |
| `GET /api/faqs` | Questions fréquentes, triées | `Faq` |
| `GET /api/flashes` | Flashs (motifs prêts à tatouer), disponibles d'abord | `Flash` |
| `GET /api/milestones` (`?timeline=salon` ou `artist`) | Dates des frises de L'atelier : histoire du salon, parcours de l'artiste | `Milestone` |
| `GET /api/session_steps` | Déroulé d'une séance | `SessionStep` |
| `GET /api/studio_photos` | Photos du salon (« Le lieu en images ») | `StudioPhoto` |
| `GET /api/hygiene_commitments` | Engagements d'hygiène et de sécurité | `HygieneCommitment` |
| `GET /api/pages`, `/api/pages/{slug}` | Pages de contenu (mentions légales…), identifiées par leur slug | `Page` |
| `GET /api/instagram_posts` | Les 6 derniers posts Instagram (bande de l'accueil), copiés par `app:instagram:sync` | `InstagramPost` |

Les champs renvoyés sont choisis par les **groupes de sérialisation** (`#[Groups(['gallery:read'])]`…) posés sur les propriétés des entités : un champ sans groupe n'est jamais exposé (par exemple le mot de passe).

### Livre d'or : dépôt d'un avis (`GuestbookController`)

Fichier : `back/src/Controller/Api/GuestbookController.php`, formulaire reçu : DTO `back/src/Controller/Api/Input/CommentInput.php`.

| Route | Accès | Rôle | DTO |
|---|---|---|---|
| `POST /api/comments` | public, sans compte | Déposer un avis : nom, titre, message, projet (facultatif). Réponse `202` : reçu, en attente. | `CommentInput` |

Fonctionnement :

- **Modération** : chaque avis est créé **en attente** ; il n'apparaît dans `GET /api/comments` qu'une fois publié dans le backoffice. Envoyer `isPublished` dans le JSON n'a aucun effet.
- **Anti-spam** : le champ caché `website` (« pot de miel ») est invisible pour les visiteurs ; s'il est rempli, c'est un robot : la réponse est la même (`202`), mais rien n'est enregistré.
- **Validation** : `#[MapRequestPayload]` transforme le JSON en DTO et le valide avant d'entrer dans la méthode.
- **Sécurité** : l'API est entièrement publique (pare-feu `api` sans authentification dans `back/config/packages/security.yaml`). Seul le backoffice demande une connexion (session, tentatives limitées par `login_throttling`).

### Codes de réponse

| Code | Signification |
|---|---|
| `200` / `202` | Succès (lecture / avis reçu, en attente de validation) |
| `400` | JSON mal formé |
| `404` | Ressource introuvable |
| `405` | Méthode non autorisée (ex. `DELETE` sur un avis) |
| `422` | Formulaire invalide : la liste des erreurs est dans `violations` |

Format d'une erreur `422`, champ par champ :

```json
{ "violations": [ { "propertyPath": "authorName", "title": "Cette valeur ne doit pas être vide." } ] }
```

Le texte de l'erreur est dans `title` pour `GuestbookController`, et dans `message` pour API Platform. Côté React, `getViolations()` (`front/src/api/client.js`) lit les deux.

### Trouver une route

- `ddev console debug:router` liste toutes les routes : `_api_…` pour API Platform, `api_…` pour `GuestbookController`.
- Swagger (`/api/docs`) ne documente que la partie API Platform.
- `debug:router` affiche aussi `GET /api/faqs/{id}` (et de même pour les flashs, frises, étapes, photos du salon, engagements) : c'est une route technique créée par API Platform pour identifier chaque élément ; elle n'est pas exposée (404).

## Photos et vidéos

### Photos envoyées depuis le backoffice : converties en WebP

Chaque photo envoyée (réalisations et leurs photos supplémentaires, dessins préparatoires, flashs, photos du salon, artiste) est **automatiquement** réduite à 2 000 px au plus sur le grand côté, convertie en WebP (qualité 80) ; l'original est supprimé. Une photo de téléphone de plusieurs Mo passe à quelques centaines de Ko.

- Conversion : `back/src/Service/ImageOptimizer.php` (extension GD de PHP). La constante `IMAGE_FIELDS` liste les champs photo traités : **un nouveau champ photo doit y être ajouté**.
- Déclenchement : `back/src/EventListener/ImageWebpListener.php`, à chaque enregistrement en base (événement Doctrine `onFlush`).
- Photos envoyées avant cette conversion : `ddev console app:images:convert-webp` (sans risque à relancer : les WebP sont ignorés).

### Timelapses des réalisations

Champ « Vidéo timelapse » d'une réalisation : fichier **MP4, 20 Mo maximum**, sans conversion côté serveur (la vidéo doit être exportée déjà légère : 720p ou 1080p, sans son). Sur le site : badge « Timelapse » sur la vignette, aperçu au survol (à la souris), et diapositive vidéo dans la visionneuse. Le champ « Lien Instagram » (post de la réalisation) ajoute un bouton « Voir sur Instagram ».

### Vidéo de l'accueil

Fichier fixe du front (pas dans le backoffice) : `front/src/assets/videos/hero-tattoo.mp4`, avec son image fixe `front/src/assets/images/hero-tattoo-poster.jpg` (affichée pendant le chargement, et à la place de la vidéo si « réduire les animations » est activé). Pour la changer : remplacer les deux fichiers en gardant leurs noms (sinon, modifier les imports en haut de `front/src/pages/HomePage.jsx`), puis reconstruire le front. Format conseillé : MP4 H.264 sans son, quelques secondes qui bouclent, 1 à 3 Mo.

## Flux Instagram de l'accueil

Bande « Sur Instagram » en bas de l'accueil (`front/src/components/InstagramFeed.jsx`) : les 6 derniers posts du salon. Elle ne s'affiche pas tant qu'il n'y a aucun post.

- **Récupération côté serveur** : `back/src/Service/InstagramSync.php` interroge l'API Instagram (« Instagram API with Instagram Login »), copie les images dans `public/uploads/instagram/` (converties en WebP) et les enregistre (`InstagramPost`). Le visiteur ne contacte jamais Instagram : pas de cookie, pas de bandeau de consentement.
- **Mise à jour** : `ddev console app:instagram:sync`, lancée toutes les 3 heures par une tâche planifiée en production. En cas de panne, les derniers posts restent affichés et le tableau de bord du backoffice affiche une alerte.
- **Jeton d'accès** : enregistré une fois avec `ddev console app:instagram:token` (saisie masquée), puis renouvelé automatiquement avant ses 60 jours. Il s'obtient depuis une application Meta (« Instagram API with Instagram Login »), avec un compte Instagram professionnel.
- **Sans jeton (développement)** : `ddev console app:instagram:sync --demo` affiche 6 posts d'exemple (photos des fixtures), remplacés par les vrais à la première synchronisation.

## Mise en production

À vérifier sur l'hébergement :

- **PHP** : extension GD avec WebP (`php -r 'var_dump(gd_info()["WebP Support"]);'` doit afficher `true`), `memory_limit` d'au moins 256M (conversion des grandes photos).
- **Envoi de fichiers** : `upload_max_filesize` et `post_max_size` d'au moins 25M (timelapses de 20 Mo), et la limite du serveur web (`client_max_body_size` sous nginx).
- **Après chaque mise à jour** : `php bin/console doctrine:migrations:migrate`.
- **Une seule fois** : `php bin/console app:images:convert-webp` pour convertir les photos déjà en ligne.

## Commandes utiles

| Commande | Rôle |
|---|---|
| `ddev console debug:router` | Lister toutes les routes |
| `ddev console make:entity` | Créer / modifier une entité |
| `ddev console make:migration` | Générer la migration après un changement d'entité |
| `ddev console doctrine:migrations:migrate` | Appliquer les migrations |
| `ddev console doctrine:fixtures:load` | Recharger les données de démo (⚠ vide la base) |
| `ddev console cache:clear` | Vider le cache |
| `ddev console app:create-admin email "Nom"` | Créer un compte administrateur du backoffice (`--super` : super administrateur) |
| `ddev console app:images:convert-webp` | Convertir en WebP les photos déjà envoyées |
| `ddev console app:pages:install` | Créer les pages légales manquantes (texte de départ : `back/src/Content/LegalPages.php` ; `--force` remplace aussi les existantes) |
| `ddev console app:instagram:sync` | Mettre à jour les posts Instagram de l'accueil (`--demo` : posts d'exemple) |
| `ddev console app:instagram:token` | Enregistrer le jeton d'accès Instagram |
| `npm run lint` (front) | Vérifier le code React |
| `npm run build` (front) | Construire le site pour la production (`front/dist/`) |

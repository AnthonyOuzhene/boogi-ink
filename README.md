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

Compte de démo créé par les fixtures (le seul type de compte : le site n'a pas d'espace client) :

| Email | Mot de passe | Rôle |
|---|---|---|
| `admin@boogi-ink.local` | `admin1234` | administrateur (backoffice) |

Pour un vrai compte admin : `ddev console app:create-admin vous@exemple.fr "Votre nom"`.

Le bouton « Voir le site » du backoffice pointe vers `FRONT_URL` (`back/.env`, par défaut `http://localhost:5180`, le port du front fixé dans `front/vite.config.js`). Pour une autre adresse, surchargez-la dans `back/.env.local`.

- Backoffice : https://boogi-ink.ddev.site/admin
- Documentation de l'API (partie API Platform) : https://boogi-ink.ddev.site/api/docs

## Démarrer le front

```bash
cd front
npm install
npm run dev        # http://localhost:5180 (port fixé dans front/vite.config.js)
```

L'adresse du back est dans `front/.env` (`VITE_BACKEND_URL`).

## L'API

Toutes les routes commencent par `/api`. Elles sont déclarées à **deux endroits**, selon une règle simple :

> **Lire la vitrine publique → API Platform** (attribut `#[ApiResource]` sur l'entité).
> **Déposer un avis dans le livre d'or → `GuestbookController`** (`back/src/Controller/Api/`), la seule écriture possible.

### Vitrine publique (API Platform, lecture seule)

Déclarées par `#[ApiResource]` dans `back/src/Entity/`. Aucune écriture possible par l'API : le contenu se gère dans le backoffice.

| Route | Contenu | Entité |
|---|---|---|
| `GET /api/homes`, `/api/homes/{id}` | Fiche du salon : coordonnées, horaires, réseaux, accroche et photo de l'accueil | `Home` |
| `GET /api/artists`, `/api/artists/{id}` | Présentation du tatoueur | `Artist` |
| `GET /api/categories`, `/api/categories/{id}` | Styles de tatouage | `Category` |
| `GET /api/galleries` (`?category=fine-line`, le slug du style), `/api/galleries/{id}` | Réalisations et leurs photos | `Gallery`, `Picture` |
| `GET /api/comments`, `/api/comments/{id}` | Livre d'or : **avis publiés uniquement** (filtre `Doctrine/PublishedCommentExtension`) | `Comment` |
| `GET /api/home_cards` | Cartes à jouer de l'accueil (points forts du salon) | `HomeCard` |
| `GET /api/faqs` | Questions fréquentes, triées | `Faq` |
| `GET /api/flashes` | Flashs (motifs prêts à tatouer), disponibles d'abord | `Flash` |
| `GET /api/milestones` (`?timeline=salon` ou `artist`) | Dates des frises de L'atelier : histoire du salon, parcours de l'artiste | `Milestone` |
| `GET /api/session_steps` | Déroulé d'une séance | `SessionStep` |
| `GET /api/studio_photos` | Photos du salon (« Le lieu en images ») | `StudioPhoto` |
| `GET /api/hygiene_commitments` | Engagements d'hygiène et de sécurité | `HygieneCommitment` |
| `GET /api/pages`, `/api/pages/{slug}` | Pages de contenu (mentions légales…), identifiées par leur slug | `Page` |

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
- **Sécurité** : l'API est entièrement publique (pare-feu `api` sans authentification dans `back/config/packages/security.yaml`). Seul le backoffice demande une connexion (session, page `/boogi-0806/login`, tentatives limitées par `login_throttling`). Le backoffice est sur `/boogi-0806` (et non `/admin`, testé par les robots).

$1

| Code | Signification |
|---|---|
| `200` / `202` | Succès (lecture / avis reçu, en attente de validation) |
| `400` | JSON mal formé |
| `404` | Ressource introuvable |
| `405` | Méthode non autorisée (ex. `DELETE` sur un avis) |
| `422` | Formulaire invalide : la liste des erreurs est dans `violations` |

Format d'une erreur `422`, champ par champ :

```json
{ "violations": [ { "propertyPath": "email", "title": "Un compte existe déjà avec cet email." } ] }
```

Le texte de l'erreur est dans `title` pour `GuestbookController`, et dans `message` pour API Platform. Côté React, `getViolations()` (`front/src/api/client.js`) lit les deux.

### Trouver une route

- `ddev console debug:router` liste toutes les routes : `_api_…` pour API Platform, `api_…` pour `GuestbookController`.
- Swagger (`/api/docs`) ne documente que la partie API Platform.
- `debug:router` affiche aussi `GET /api/faqs/{id}` (et de même pour les flashs, frises, étapes, photos du salon, engagements) : c'est une route technique créée par API Platform pour identifier chaque élément ; elle n'est pas exposée (404).

## Commandes utiles

| Commande | Rôle |
|---|---|
| `ddev console debug:router` | Lister toutes les routes |
| `ddev console make:entity` | Créer / modifier une entité |
| `ddev console make:migration` | Générer la migration après un changement d'entité |
| `ddev console doctrine:migrations:migrate` | Appliquer les migrations |
| `ddev console doctrine:fixtures:load` | Recharger les données de démo (⚠ vide la base) |
| `ddev console cache:clear` | Vider le cache |
| `npm run lint` (front) | Vérifier le code React |
| `npm run build` (front) | Construire le site pour la production (`front/dist/`) |

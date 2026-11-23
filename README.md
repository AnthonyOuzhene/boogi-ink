# Boogi'ink

Site du salon de tatouage Boogi'ink.

| Dossier | Contenu |
|---|---|
| `back/` | Symfony 7.4 LTS : API (API Platform + contrôleurs, authentification JWT) et backoffice (EasyAdmin), MariaDB, le tout dans DDEV |
| `front/` | React 19 + Vite : le site public et l'espace client |

## Prérequis

- [DDEV](https://ddev.com) (avec Docker ou Colima)
- Node.js 22 ou plus récent

## Démarrer le back

```bash
cd back
ddev start                               # conteneurs PHP 8.4 + MariaDB 11.4
ddev composer install                    # dépendances PHP
ddev console lexik:jwt:generate-keypair  # clés JWT (une seule fois, non versionnées)
ddev console doctrine:migrations:migrate # crée / met à jour les tables
ddev console doctrine:fixtures:load      # données de démo (⚠ vide la base)
```

Comptes de démo créés par les fixtures :

| Email | Mot de passe | Rôle |
|---|---|---|
| `admin@boogi-ink.local` | `admin1234` | administrateur (backoffice) |
| `julie@example.com` | `client1234` | cliente |
| `paul@example.com` | `client1234` | client (avec un avis en attente de validation) |

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
> **Tout ce que fait un visiteur ou un client → `AccountController`** (`back/src/Controller/Api/`).

### Vitrine publique (API Platform, lecture seule)

Déclarées par `#[ApiResource]` dans `back/src/Entity/`. Aucune écriture possible par l'API : le contenu se gère dans le backoffice.

| Route | Contenu | Entité |
|---|---|---|
| `GET /api/homes`, `/api/homes/{id}` | Fiche du salon : coordonnées, horaires, réseaux, accroche et photo de l'accueil | `Home` |
| `GET /api/artists`, `/api/artists/{id}` | Présentation du tatoueur | `Artist` |
| `GET /api/categories`, `/api/categories/{id}` | Styles de tatouage | `Category` |
| `GET /api/galleries` (`?category=2`), `/api/galleries/{id}` | Réalisations et leurs photos | `Gallery`, `Picture` |
| `GET /api/comments`, `/api/comments/{id}` | Livre d'or : **avis publiés uniquement** (filtre `Doctrine/PublishedCommentExtension`) | `Comment` |
| `GET /api/faqs` | Questions fréquentes, triées | `Faq` |
| `GET /api/flashes` | Flashs (motifs prêts à tatouer), disponibles d'abord | `Flash` |
| `GET /api/milestones` (`?timeline=salon` ou `artist`) | Dates des frises de L'atelier : histoire du salon, parcours de l'artiste | `Milestone` |
| `GET /api/session_steps` | Déroulé d'une séance | `SessionStep` |
| `GET /api/studio_photos` | Photos du salon (« Le lieu en images ») | `StudioPhoto` |
| `GET /api/hygiene_commitments` | Engagements d'hygiène et de sécurité | `HygieneCommitment` |
| `GET /api/pages`, `/api/pages/{slug}` | Pages de contenu (mentions légales…), identifiées par leur slug | `Page` |
| `GET /api/users/{id}` | Un compte : soi-même ou un admin (sert aussi à identifier les auteurs en JSON-LD) | `User` |

Les champs renvoyés sont choisis par les **groupes de sérialisation** (`#[Groups(['gallery:read'])]`…) posés sur les propriétés des entités : un champ sans groupe n'est jamais exposé (par exemple le mot de passe).

### Compte client (`AccountController`)

Fichier : `back/src/Controller/Api/AccountController.php`.
Les formulaires reçus sont des DTO rangés à côté, dans `back/src/Controller/Api/Input/`.

| Route | Accès | Rôle | DTO |
|---|---|---|---|
| `POST /api/users` | public | Inscription (compte client) | `RegisterInput` |
| `POST /api/login` | public | Connexion : renvoie le jeton JWT (géré par le pare-feu, pas par un contrôleur) | — |
| `GET /api/me` | connecté | Profil : id, e-mail, rôles, nom | — |
| `POST /api/me/password` | connecté | Changer son mot de passe | `ChangePasswordInput` |
| `GET /api/me/comments` | connecté | Ses avis, publiés **et** en attente, avec leur statut | — |
| `POST /api/me/comments` | connecté | Déposer un avis (créé en attente de validation) | `CommentInput` |
| `PATCH /api/me/comments/{id}` | connecté, auteur | Modifier son avis (il repasse en attente). Pas de suppression. | `CommentInput` |

Fonctionnement :

- **Authentification** : le front envoie le jeton reçu à la connexion dans l'en-tête `Authorization: Bearer <jeton>` (valable 1 h).
- **Protection** : tout `/api/me` est réservé aux connectés dès le pare-feu (`access_control` dans `back/config/packages/security.yaml`), et chaque méthode porte en plus `#[IsGranted('ROLE_USER')]`.
- **Sécurité des données** : l'utilisateur vient toujours du jeton (`$this->getUser()`), jamais du JSON reçu. Un client ne peut lire ou modifier que ses propres données ; envoyer `roles`, `author` ou `isPublished` n'a aucun effet.
- **Validation** : `#[MapRequestPayload]` transforme le JSON en DTO et le valide avant d'entrer dans la méthode.

### Codes de réponse et erreurs

| Code | Signification |
|---|---|
| `200` / `201` / `204` | Succès (lecture / création / action sans contenu à renvoyer) |
| `400` | JSON mal formé |
| `401` | Non connecté ou jeton expiré |
| `403` | Connecté, mais pas autorisé (ex. modifier l'avis de quelqu'un d'autre) |
| `404` | Ressource introuvable |
| `405` | Méthode non autorisée (ex. `DELETE` sur un avis) |
| `422` | Formulaire invalide : la liste des erreurs est dans `violations` |

Format d'une erreur `422`, champ par champ :

```json
{ "violations": [ { "propertyPath": "email", "title": "Un compte existe déjà avec cet email." } ] }
```

Le texte de l'erreur est dans `title` pour `AccountController`, et dans `message` pour API Platform. Côté React, `getViolations()` (`front/src/api/client.js`) lit les deux.

### Trouver une route

- `ddev console debug:router` liste toutes les routes : `_api_…` pour API Platform, `api_…` pour `AccountController`.
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

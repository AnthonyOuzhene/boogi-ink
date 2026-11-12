# Boogi'ink

Site du salon de tatouage Boogi'ink.

| Dossier | Contenu |
|---|---|
| `back/` | Symfony 7.4 LTS : API (API Platform + JWT) et backoffice (EasyAdmin), MariaDB, le tout dans DDEV |
| `front/` | React 19 + Vite : le site public |

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

Pour un vrai compte admin : `ddev console app:create-admin vous@exemple.fr "Votre nom"`.

- Backoffice : https://boogi-ink.ddev.site/admin
- Documentation de l'API : https://boogi-ink.ddev.site/api/docs

## Démarrer le front

```bash
cd front
npm install
npm run dev        # http://localhost:5173
```

L'adresse du back est dans `front/.env` (`VITE_BACKEND_URL`).

## Commandes utiles

| Commande | Rôle |
|---|---|
| `ddev console debug:router` | Lister toutes les routes |
| `ddev console make:entity` | Créer / modifier une entité |
| `ddev console make:migration` | Générer la migration après un changement d'entité |
| `ddev console doctrine:migrations:migrate` | Appliquer les migrations |
| `ddev console cache:clear` | Vider le cache |
| `npm run lint` (front) | Vérifier le code React |
| `npm run build` (front) | Construire le site pour la production (`front/dist/`) |

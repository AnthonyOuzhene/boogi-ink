# Mise en ligne de Boogi'ink

Check-list pour mettre le site en production, puis pour les mises à jour suivantes.
À cocher au fur et à mesure (`[ ]` → `[x]`).

---

## 1. Comment le site est découpé en ligne

Le projet a **deux parties**, mises en ligne séparément :

| Partie | Dossier | En ligne | Exemple d'adresse |
|---|---|---|---|
| Site public (React) | `front/` | Fichiers statiques construits par `npm run build` (`front/dist/`) | `https://boogi-ink.fr` |
| API + backoffice (Symfony) | `back/` | Application PHP, racine web = `back/public/` | `https://api.boogi-ink.fr` |

Les deux adresses sont liées par **trois réglages qui doivent concorder** :

- `VITE_BACKEND_URL` (front) → l'adresse du back ;
- `CORS_ALLOW_ORIGIN` (back) → autorise l'adresse du front à appeler l'API ;
- `FRONT_URL` (back) → bouton « Voir le site » du backoffice.

> Les adresses de ce document sont des exemples : remplacez-les par les vraies partout.

---

## 2. Ce que l'hébergement doit proposer

- [ ] **PHP 8.4** (comme en local avec DDEV ; 8.2 minimum)
- [ ] Extensions PHP : `ctype`, `iconv`, `intl` (dates en français du tableau de bord), `pdo_mysql`, `gd` **avec WebP**, `exif`
  - Vérifier le WebP : `php -r 'var_dump(gd_info()["WebP Support"]);'` doit afficher `true`
- [ ] **MariaDB** 10.11 ou plus récent (11.4 en local)
- [ ] **Composer** et un **accès SSH** (pour lancer les commandes Symfony)
- [ ] **HTTPS** (certificat Let's Encrypt, souvent fourni) sur les deux adresses
- [ ] Possibilité de choisir le **dossier racine** du domaine de l'API : `back/public/`
- [ ] Réglages PHP :
  - `memory_limit` ≥ **256M** (conversion des grandes photos en WebP)
  - `upload_max_filesize` et `post_max_size` ≥ **25M** (timelapses de 20 Mo)
- [ ] Limite d'envoi du serveur web ≥ 25 Mo (`client_max_body_size 25M;` sous nginx)
- [ ] **Tâches planifiées (cron)** : nécessaires pour le flux Instagram de l'accueil (point 6)
- [ ] Le serveur peut **appeler des sites extérieurs** en HTTPS (`graph.instagram.com`) : parfois bloqué sur les offres d'entrée de gamme

Si le serveur est **Apache** : installer le `.htaccess` de Symfony avec `composer require symfony/apache-pack` (à faire une fois, en local, puis versionner).

---

## 3. Back (Symfony) : première mise en ligne

### 3.1 Récupérer le code et les dépendances

```bash
git clone <dépôt> boogi-ink && cd boogi-ink/back
composer install --no-dev --optimize-autoloader
```

### 3.2 Variables d'environnement

Créer `back/.env.local` (jamais versionné) :

```dotenv
APP_ENV=prod
APP_SECRET=<chaîne aléatoire de 32 caractères>
DEFAULT_URI=https://api.boogi-ink.fr
DATABASE_URL="mysql://<utilisateur>:<mot_de_passe>@<hôte>:3306/<base>?serverVersion=<version>-MariaDB&charset=utf8mb4"
CORS_ALLOW_ORIGIN='^https://(www\.)?boogi-ink\.fr$'
FRONT_URL=https://boogi-ink.fr
```

- [ ] `APP_SECRET` généré : `php -r 'echo bin2hex(random_bytes(16)), PHP_EOL;'`
- [ ] `serverVersion` = la version exacte de MariaDB de l'hébergeur (ex. `10.11.6-MariaDB`)
- [ ] `CORS_ALLOW_ORIGIN` = l'adresse du **front** (expression régulière : les points sont précédés de `\`)

### 3.3 Base de données, dossiers, cache

```bash
php bin/console doctrine:migrations:migrate --no-interaction   # crée les tables
mkdir -p public/uploads/{artist,flashes,galleries,studio,instagram} # dossiers des envois (et des images Instagram)
php bin/console assets:install public                            # styles et scripts d'EasyAdmin (public/bundles)
php bin/console cache:clear
```

- [ ] `var/` et `public/uploads/` sont **accessibles en écriture** par PHP
- [ ] Ne **pas** lancer `doctrine:fixtures:load` en production (données de démo, vide la base ; et les fixtures ne sont pas installées avec `--no-dev`)

### 3.4 Compte administrateur

```bash
php bin/console app:create-admin vous@exemple.fr "Votre nom"
```

- [ ] Connexion testée sur `https://api.boogi-ink.fr/boogi-0806/login`

---

## 4. Front (React) : construire et envoyer

À faire **sur votre machine** (Node.js 22 ou plus récent), pas sur le serveur.

1. Créer `front/.env.production.local` (non versionné) :

   ```dotenv
   VITE_BACKEND_URL=https://api.boogi-ink.fr
   ```

2. Construire :

   ```bash
   cd front
   npm ci
   npm run build        # produit front/dist/
   ```

3. Envoyer **le contenu** de `front/dist/` à la racine du domaine du site (FTP/SFTP ou `rsync`).

4. **Redirection obligatoire vers `index.html`** : le site gère ses pages en JavaScript (`/livre-d-or`, `/realisations`…). Sans cette règle, recharger une page autre que l'accueil donne une erreur 404.

   - Apache : fichier `.htaccess` à la racine du site

     ```apache
     RewriteEngine On
     RewriteCond %{REQUEST_FILENAME} !-f
     RewriteCond %{REQUEST_FILENAME} !-d
     RewriteRule ^ index.html [L]
     ```

   - nginx : `location / { try_files $uri $uri/ /index.html; }`

> `VITE_BACKEND_URL` est **inscrite dans les fichiers au moment du build** : si l'adresse du back change, il faut reconstruire et renvoyer le front.

---

## 5. Contenu de départ

Les fixtures ne tournent pas en production : la base est vide après la première mise en ligne. Dans le backoffice (`/boogi-0806`), renseigner au minimum :

- [ ] **Salon** : nom, adresse, téléphone, e-mail, horaires, Instagram / Facebook, accroche de l'accueil
- [ ] **L'artiste** : présentation, photos (accueil et L'atelier)
- [ ] **Cartes de l'accueil**, **Déroulé d'une séance**, **Engagements d'hygiène**, **FAQ**
- [ ] **Styles** (catégories), puis les premières **Réalisations** et **Flashs**
- [ ] **Pages** : mentions légales (obligatoires), politique de confidentialité
- [ ] Le tableau de bord du backoffice affiche une liste de contrôle du contenu manquant : la suivre jusqu'au bout

> Idée en attente : une commande `app:install` qui créerait ce contenu de départ automatiquement.

---

## 6. Brancher Instagram (bande de l'accueil)

Sans cette étape, le site fonctionne : la bande « Sur Instagram » ne s'affiche simplement pas.

### 6.1 Côté client (une seule fois)

- [ ] Compte Instagram du salon passé en **compte professionnel** (« Créateur » ou « Entreprise ») : application Instagram → Paramètres → Type de compte et outils → Passer à un compte professionnel. Gratuit.
- [ ] Sur [developers.facebook.com](https://developers.facebook.com) : créer une **application Meta** (type « Business » / « Autre »), puis ajouter le produit **Instagram** → « API setup with Instagram login ».
- [ ] Dans cette application, **ajouter le compte Instagram du salon** (rôles → testeur Instagram, à accepter depuis l'application Instagram du client si le compte n'est pas le vôtre).
- [ ] **Générer le jeton** (« Generate token ») en se connectant avec le compte du salon. Le copier : c'est un jeton **longue durée** (60 jours).

> Les écrans de Meta changent souvent : si un intitulé ne correspond plus, chercher « Instagram API with Instagram Login » dans la documentation Meta. Seule autorisation nécessaire : `instagram_business_basic` (lecture du profil et des posts).

### 6.2 Côté serveur

```bash
php bin/console app:instagram:token      # coller le jeton (saisie masquée) : enregistre + 1re synchronisation
```

- [ ] La commande affiche « Connecté : 6 posts Instagram affichés sur l'accueil »
- [ ] **Tâche planifiée** toutes les 3 heures (cron) — adapter le chemin de PHP et du projet :

  ```cron
  0 */3 * * * cd /chemin/vers/boogi-ink/back && php bin/console app:instagram:sync --no-interaction >> var/log/instagram.log 2>&1
  ```

Ensuite, plus rien à faire : le jeton se renouvelle tout seul 10 jours avant son expiration. Si une mise à jour échoue (jeton révoqué, compte repassé en personnel, mot de passe changé…), **une alerte s'affiche sur le tableau de bord du backoffice** et les derniers posts restent visibles : refaire alors 6.1 (générer un jeton) et 6.2.

---

## 7. Vérifications après la mise en ligne

- [ ] L'accueil s'affiche, **la vidéo se lance** (et l'image fixe s'affiche si « réduire les animations » est activé)
- [ ] Les pages Réalisations, Flashs, L'atelier, Livre d'or, Contact s'affichent, **y compris en rechargeant la page** (règle du point 4.4)
- [ ] Pas d'erreur **CORS** dans la console du navigateur (sinon : vérifier `CORS_ALLOW_ORIGIN`)
- [ ] Livre d'or : un avis déposé arrive « en attente » dans le backoffice, puis apparaît sur le site une fois publié
- [ ] Backoffice : `/admin` et `/login` répondent **404** ; `/boogi-0806` redirige vers la connexion
- [ ] Connexion : 6 mauvais mots de passe d'affilée → blocage temporaire (limite des tentatives)
- [ ] Envoi d'une photo de téléphone : elle est enregistrée en **.webp**, réduite à 2 000 px
- [ ] Envoi d'un timelapse MP4 (jusqu'à 20 Mo) : accepté, et lu dans la visionneuse
- [ ] Bouton « Voir le site » du backoffice → mène bien au front (`FRONT_URL`)
- [ ] Sur téléphone : menu burger, barre « Prendre rendez-vous » en bas, vidéo d'accueil
- [ ] Bande « Sur Instagram » en bas de l'accueil (si le point 6 est fait) ; au clic, le post s'ouvre sur Instagram

---

## 8. Mises à jour suivantes

**Back :**

```bash
cd boogi-ink && git pull
cd back
composer install --no-dev --optimize-autoloader
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console cache:clear
```

**Front** : reconstruire (`npm ci && npm run build`) et renvoyer `front/dist/`.

**Une seule fois**, après la mise à jour qui apporte la conversion WebP (si des photos étaient déjà en ligne) :

```bash
php bin/console app:images:convert-webp
```

---

## 9. Sauvegardes

Deux choses ne sont **pas** dans Git et doivent être sauvegardées régulièrement :

- [ ] la **base de données** (export SQL, souvent automatique chez l'hébergeur : vérifier la fréquence et la durée de conservation)
- [ ] le dossier **`back/public/uploads/`** : toutes les photos et vidéos envoyées depuis le backoffice

Sans ces deux sauvegardes, une panne fait perdre tout le contenu du site.

---

## 10. Points en attente

- [ ] **Jeton Instagram** du client (point 6.1)
- [ ] **Vidéo de l'accueil** : remplacer par une version nette (vidéo d'origine) en paysage 1920 px + une version portrait, voir README « Vidéo de l'accueil »
- [ ] **Commande `app:install`** pour le contenu de départ (point 5)

<?php

namespace App\Content;

/**
 * Texte de départ des pages légales (mentions légales, confidentialité, règles sanitaires).
 *
 * Utilisé par les fixtures et par la commande app:pages:install (production). Ensuite, les pages
 * se modifient dans le backoffice (menu "Pages") : ce fichier ne sert plus qu'à les recréer.
 *
 * Format du texte (voir ContentPage.jsx) : une ligne vide = nouveau paragraphe,
 * une ligne qui commence par "## " = intertitre. Les retours à la ligne simples sont ignorés :
 * chaque élément de liste est donc un paragraphe qui commence par "• ".
 *
 * Les passages entre crochets [À compléter : …] sont à remplacer dans le backoffice.
 * Ces textes sont des modèles : à faire relire si besoin (ils ne remplacent pas un conseil juridique).
 */
final class LegalPages
{
    /** @return list<array{title: string, slug: string, content: string}> */
    public static function all(): array
    {
        return [
            ['title' => 'Mentions légales', 'slug' => 'mentions-legales', 'content' => self::legalNotice()],
            ['title' => 'Politique de confidentialité', 'slug' => 'politique-de-confidentialite', 'content' => self::privacyPolicy()],
            ['title' => 'Règles sanitaires', 'slug' => 'regles-sanitaires', 'content' => self::healthRules()],
        ];
    }

    private static function legalNotice(): string
    {
        return <<<'TXT'
            Conformément à l'article 6 de la loi n° 2004-575 du 21 juin 2004 pour la confiance dans l'économie numérique (LCEN), voici les informations relatives à l'éditeur et à l'hébergeur de ce site.

            ## Éditeur du site

            Boogi'ink, salon de tatouage

            [À compléter : nom et prénom de l'exploitant, ou dénomination sociale]

            [À compléter : forme juridique (entreprise individuelle, micro-entreprise, SARL…) et, pour une société, montant du capital]

            Adresse : 332 rue Louis Gillain, 27210 Beuzeville

            Téléphone : 09 80 49 43 32

            E-mail : ladyboogiink@gmail.com

            [À compléter : numéro SIRET, et numéro d'immatriculation au Registre national des entreprises (RNE)]

            [À compléter : numéro de TVA intracommunautaire, ou la mention « TVA non applicable, article 293 B du CGI » en franchise de TVA]

            ## Directeur de la publication

            [À compléter : nom et prénom du responsable de la publication, en général l'exploitant du salon]

            ## Hébergement

            [À compléter : nom de l'hébergeur, adresse postale et numéro de téléphone]

            ## Conception et réalisation

            [À compléter : nom du concepteur du site et lien éventuel]

            ## Propriété intellectuelle

            L'ensemble des contenus de ce site (dessins, flashs, photographies des réalisations, textes, logo) est la propriété exclusive de Boogi'ink ou de ses auteurs, et est protégé par le Code de la propriété intellectuelle.

            Toute reproduction, représentation, modification ou réutilisation, totale ou partielle, sans autorisation écrite préalable est interdite. Les dessins et flashs sont des créations originales : ils ne peuvent pas être reproduits par un autre tatoueur.

            ## Photographies des clients

            Les photographies de tatouages et de personnes sont publiées avec l'accord des personnes concernées. Si vous apparaissez sur une photo et souhaitez qu'elle soit retirée, il suffit d'en faire la demande par e-mail : elle sera supprimée dans les meilleurs délais.

            ## Liens vers d'autres sites

            Ce site contient des liens vers d'autres sites, notamment Instagram et Facebook. Boogi'ink n'est pas responsable de leur contenu ni de leurs pratiques en matière de données personnelles.

            ## Données personnelles

            Le traitement des données personnelles et l'utilisation des cookies sont détaillés dans la page « Politique de confidentialité ».
            TXT;
    }

    private static function privacyPolicy(): string
    {
        return <<<'TXT'
            Cette page explique quelles données personnelles sont collectées sur ce site, pourquoi, combien de temps elles sont conservées et comment exercer vos droits, conformément au Règlement général sur la protection des données (RGPD) et à la loi Informatique et Libertés.

            ## Responsable du traitement

            Boogi'ink, 332 rue Louis Gillain, 27210 Beuzeville — ladyboogiink@gmail.com

            [À compléter : nom de l'exploitant du salon]

            ## Ce que le site collecte (et ne collecte pas)

            Le site ne propose ni compte client, ni formulaire de contact, ni newsletter. Les prises de contact et de rendez-vous se font par téléphone, par e-mail, ou par message sur Instagram et Facebook (Messenger) : ces échanges sont alors soumis aux règles de ces services.

            La seule donnée saisie sur le site est un avis déposé dans le livre d'or.

            ## Avis du livre d'or

            • Données collectées : le prénom ou pseudo indiqué, le titre et le texte de l'avis, et, si vous le précisez, le tatouage réalisé.

            • Finalité : publier votre témoignage sur le site, après relecture par le salon (aucun avis n'est publié automatiquement).

            • Base légale : votre consentement, donné en envoyant le formulaire.

            • Conservation : tant que l'avis est publié. Un avis non publié est supprimé lors de la modération.

            • Destinataires : le salon uniquement. Une fois publié, l'avis est visible par tous les visiteurs du site.

            Évitez d'indiquer votre nom complet ou des informations sur votre santé dans un avis.

            ## Cookies et traceurs

            Le site n'utilise aucun cookie publicitaire ni outil de mesure d'audience.

            • Cookie de connexion : un cookie strictement nécessaire est déposé uniquement lors de la connexion à l'espace d'administration, réservé au salon. Il ne concerne pas les visiteurs.

            • Préférences d'affichage : le site respecte les réglages de votre appareil (par exemple « réduire les animations ») sans rien enregistrer.

            ## Services extérieurs

            Certains éléments du site sont fournis par d'autres sociétés, qui reçoivent alors des informations techniques (votre adresse IP, votre navigateur) :

            • Google Maps, sur la page Contact : la carte est affichée par Google, qui peut déposer ses propres cookies. Voir la politique de confidentialité de Google : policies.google.com/privacy

            • Google Fonts : les polices de caractères du site sont chargées depuis les serveurs de Google.

            • Instagram : les dernières publications du salon affichées sur l'accueil sont copiées sur le serveur du site. Votre navigateur ne contacte Instagram que si vous cliquez sur une publication ou sur un lien vers Instagram ou Facebook.

            [À vérifier : si la carte Google Maps et les polices Google Fonts sont retirées ou hébergées autrement, mettre à jour ce paragraphe.]

            ## Hébergement et journaux techniques

            Le site est hébergé par [À compléter : hébergeur], qui conserve des journaux de connexion (adresse IP, date, page consultée) à des fins de sécurité, pour une durée limitée [À compléter : durée indiquée par l'hébergeur, en général de quelques semaines à un an].

            ## Vos droits

            Vous disposez d'un droit d'accès, de rectification, d'effacement, de limitation et d'opposition sur vos données, ainsi que du droit de retirer votre consentement à tout moment (par exemple pour faire retirer votre avis).

            Pour les exercer, écrivez à ladyboogiink@gmail.com. Une réponse vous sera apportée dans un délai d'un mois.

            Si vous estimez que vos droits ne sont pas respectés, vous pouvez adresser une réclamation à la CNIL (cnil.fr).

            ## Données collectées au salon

            Les documents remplis au salon (consentement éclairé, autorisation parentale pour les mineurs, informations utiles à la séance) ne passent pas par ce site. Ils sont conservés par le salon de manière confidentielle, pour la durée nécessaire au suivi de la prestation et au respect de ses obligations.
            TXT;
    }

    private static function healthRules(): string
    {
        return <<<'TXT'
            Le tatouage est un acte qui traverse la peau : il est encadré par le Code de la santé publique (articles R1311-1 et suivants). Voici les règles d'hygiène appliquées au salon, et ce qu'il faut savoir avant, pendant et après une séance.

            ## Un salon déclaré et formé

            • Activité déclarée auprès de l'Agence régionale de santé (ARS) de Normandie.

            • Formation aux conditions d'hygiène et de salubrité suivie par le tatoueur, comme l'exige la réglementation.

            [À compléter : année de la formation hygiène et, si vous le souhaitez, organisme de formation]

            ## Hygiène pendant la séance

            • Aiguilles, cartouches, gants et protections à usage unique, ouverts devant vous et jetés après la séance.

            • Matériel réutilisable nettoyé puis stérilisé selon un protocole conforme aux bonnes pratiques.

            • Poste de travail nettoyé et désinfecté avant et après chaque client, zones protégées par des films à usage unique.

            • Encres conformes à la réglementation européenne (règlement REACH), versées dans des godets à usage unique, jamais réutilisées d'un client à l'autre.

            • Déchets coupants et souillés éliminés par une filière agréée (DASRI).

            ## Avant la séance

            • Une information écrite sur les risques et les précautions à prendre vous est remise avant le tatouage, comme le prévoit la loi. Prenez le temps de la lire et de poser vos questions.

            • Venez reposé(e), après avoir mangé, sans avoir bu d'alcool dans les 24 heures.

            • Signalez toute maladie, allergie, traitement en cours (notamment anticoagulants), grossesse ou allaitement : le tatouage peut être déconseillé ou reporté.

            • La zone à tatouer doit être saine : pas de coup de soleil, de plaie, d'irritation ni de grain de beauté suspect.

            ## Mineurs

            Le tatouage d'une personne mineure n'est possible qu'avec le consentement écrit d'un parent ou du tuteur légal, présenté le jour de la séance (article R1311-11 du Code de la santé publique). [À compléter : âge minimum accepté par le salon, s'il y en a un]

            ## Après la séance : les soins

            • Gardez le pansement le temps indiqué par le tatoueur, puis lavez-vous les mains avant de toucher le tatouage.

            • Nettoyez doucement à l'eau tiède et au savon neutre, séchez en tamponnant, appliquez une fine couche de crème conseillée.

            • Pendant la cicatrisation (2 à 4 semaines) : pas de bain, de piscine, de mer, de sauna ni d'exposition au soleil. Ne grattez pas et n'arrachez pas les croûtes.

            • En cas de rougeur qui s'étend, de chaleur, de douleur inhabituelle ou de fièvre, consultez un médecin et prévenez le salon.

            ## Une question ?

            N'hésitez pas à poser toutes vos questions avant de réserver : le salon vous répondra par téléphone, par e-mail ou sur Instagram.
            TXT;
    }
}

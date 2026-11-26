<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261123160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Salon : suppression de la grande photo de l'accueil (remplacée par une vidéo fixe du site)";
    }

    public function up(Schema $schema): void
    {
        // Le nom du fichier envoyé disparaît ; les fichiers eux-mêmes (public/uploads/home/)
        // ne sont pas touchés par la migration : à supprimer à la main si besoin
        $this->addSql('ALTER TABLE home DROP picture');
    }

    public function down(Schema $schema): void
    {
        // Retour en arrière : la colonne revient, vide (le nom de la photo est perdu)
        $this->addSql('ALTER TABLE home ADD picture VARCHAR(255) DEFAULT NULL');
    }
}

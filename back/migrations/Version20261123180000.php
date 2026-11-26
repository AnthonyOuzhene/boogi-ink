<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261123180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Réalisations : le "lien vidéo" devient le "lien Instagram" (colonne renommée, liens conservés)';
    }

    public function up(Schema $schema): void
    {
        // CHANGE renomme la colonne en gardant son contenu (un DROP + ADD effacerait les liens)
        $this->addSql('ALTER TABLE gallery CHANGE video instagram_url VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE gallery CHANGE instagram_url video VARCHAR(255) DEFAULT NULL');
    }
}

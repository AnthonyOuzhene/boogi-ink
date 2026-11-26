<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261123170000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Réalisations : vidéo timelapse facultative (fichier MP4 envoyé depuis le backoffice)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE gallery ADD timelapse VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // Les fichiers vidéo (public/uploads/galleries/) restent sur le disque
        $this->addSql('ALTER TABLE gallery DROP timelapse');
    }
}

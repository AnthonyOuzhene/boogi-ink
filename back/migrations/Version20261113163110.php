<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261113163110 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Réalisations : coup de cœur de l\'artiste (annotation au feutre sur le site)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE gallery ADD is_favorite TINYINT DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE gallery DROP is_favorite');
    }
}

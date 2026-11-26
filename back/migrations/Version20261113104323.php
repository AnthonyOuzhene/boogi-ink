<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261113104323 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Artiste : photo spécifique à la page L\'atelier';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE artist ADD atelier_picture VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE artist DROP atelier_picture');
    }
}

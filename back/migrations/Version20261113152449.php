<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261113152449 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Flashs : prix obligatoire (un flash a toujours un prix fixe)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE flash CHANGE price price NUMERIC(10, 2) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE flash CHANGE price price NUMERIC(10, 2) DEFAULT NULL');
    }
}

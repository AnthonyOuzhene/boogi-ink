<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261113160830 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Réalisations : dessin préparatoire facultatif (curseur « du dessin à la peau »)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE gallery ADD sketch VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE gallery DROP sketch');
    }
}

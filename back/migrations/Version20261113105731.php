<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261113105731 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Utilisateurs : date d\'inscription';
    }

    public function up(Schema $schema): void
    {
        // Les comptes existants reçoivent la date du jour, puis on retire la valeur par défaut
        // (les nouveaux comptes la reçoivent du constructeur de User)
        $this->addSql('ALTER TABLE user ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE created_at created_at DATETIME NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user DROP created_at');
    }
}

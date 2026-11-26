<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261112163749 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE page (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(150) NOT NULL, slug VARCHAR(160) NOT NULL, content LONGTEXT NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_140AB620989D9B62 (slug), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE home ADD email VARCHAR(180) DEFAULT NULL, ADD instagram_url VARCHAR(255) DEFAULT NULL, ADD facebook_url VARCHAR(255) DEFAULT NULL, ADD opening_hours LONGTEXT DEFAULT NULL, ADD hero_title VARCHAR(100) DEFAULT NULL, ADD hero_highlight VARCHAR(100) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE page');
        $this->addSql('ALTER TABLE home DROP email, DROP instagram_url, DROP facebook_url, DROP opening_hours, DROP hero_title, DROP hero_highlight');
    }
}

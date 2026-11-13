<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261113102724 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'L\'atelier v2 : frises, étapes de séance, photos du lieu, flashs, engagements d\'hygiène';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE flash (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(150) NOT NULL, picture VARCHAR(255) NOT NULL, size VARCHAR(50) DEFAULT NULL, placement VARCHAR(150) DEFAULT NULL, price NUMERIC(10, 2) DEFAULT NULL, is_available TINYINT NOT NULL, position INT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE hygiene_commitment (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(150) NOT NULL, description LONGTEXT NOT NULL, icon VARCHAR(30) NOT NULL, position INT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE milestone (id INT AUTO_INCREMENT NOT NULL, timeline VARCHAR(20) NOT NULL, year VARCHAR(30) NOT NULL, title VARCHAR(150) NOT NULL, description LONGTEXT DEFAULT NULL, position INT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE session_step (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(150) NOT NULL, summary VARCHAR(255) DEFAULT NULL, description LONGTEXT NOT NULL, position INT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE studio_photo (id INT AUTO_INCREMENT NOT NULL, picture VARCHAR(255) NOT NULL, caption VARCHAR(150) DEFAULT NULL, position INT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE artist ADD short_bio LONGTEXT DEFAULT NULL, ADD influences LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE home ADD story_intro LONGTEXT DEFAULT NULL, ADD place_description LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE service ADD position INT DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE flash');
        $this->addSql('DROP TABLE hygiene_commitment');
        $this->addSql('DROP TABLE milestone');
        $this->addSql('DROP TABLE session_step');
        $this->addSql('DROP TABLE studio_photo');
        $this->addSql('ALTER TABLE artist DROP short_bio, DROP influences');
        $this->addSql('ALTER TABLE home DROP story_intro, DROP place_description');
        $this->addSql('ALTER TABLE service DROP position');
    }
}

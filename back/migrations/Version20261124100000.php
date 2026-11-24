<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261124100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Flux Instagram de l'accueil : derniers posts copiés, et connexion au compte (jeton)";
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE instagram_post (id INT AUTO_INCREMENT NOT NULL, instagram_id VARCHAR(64) NOT NULL, permalink VARCHAR(255) NOT NULL, caption LONGTEXT DEFAULT NULL, media_type VARCHAR(20) NOT NULL, image VARCHAR(255) NOT NULL, posted_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_AA7E08A59C19920F (instagram_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE instagram_connection (id INT AUTO_INCREMENT NOT NULL, access_token LONGTEXT DEFAULT NULL, token_expires_at DATETIME DEFAULT NULL, last_sync_at DATETIME DEFAULT NULL, last_error LONGTEXT DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        // Les images copiées (public/uploads/instagram/) restent sur le disque
        $this->addSql('DROP TABLE instagram_post');
        $this->addSql('DROP TABLE instagram_connection');
    }
}

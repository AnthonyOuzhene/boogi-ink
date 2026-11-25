<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261123110852 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Fin de l'espace client : avis avec un simple nom d'auteur, suppression des favoris";
    }

    public function up(Schema $schema): void
    {
        // 1. Favoris : la fonctionnalité disparaît, avec sa table de liaison
        $this->addSql('ALTER TABLE user_favorite_flash DROP FOREIGN KEY `FK_72C3AB7E25F8D5EA`');
        $this->addSql('ALTER TABLE user_favorite_flash DROP FOREIGN KEY `FK_72C3AB7EA76ED395`');
        $this->addSql('DROP TABLE user_favorite_flash');

        // 2. Avis : le nom de l'auteur devient un simple texte.
        //    D'abord une colonne facultative, remplie avec le nom du compte de chaque auteur…
        $this->addSql('ALTER TABLE comment ADD author_name VARCHAR(80) DEFAULT NULL');
        $this->addSql('UPDATE comment c JOIN user u ON u.id = c.author_id SET c.author_name = LEFT(u.name, 80)');
        //    …puis obligatoire, comme le décrit l'entité
        $this->addSql('ALTER TABLE comment MODIFY author_name VARCHAR(80) NOT NULL');

        // 3. Seulement maintenant : on coupe le lien avec le compte, et la date de modification
        //    par l'auteur disparaît (on ne peut plus modifier son avis depuis le site)
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY `FK_9474526CF675F31B`');
        $this->addSql('DROP INDEX IDX_9474526CF675F31B ON comment');
        $this->addSql('ALTER TABLE comment DROP author_id, DROP updated_at');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE user_favorite_flash (user_id INT NOT NULL, flash_id INT NOT NULL, INDEX IDX_72C3AB7E25F8D5EA (flash_id), INDEX IDX_72C3AB7EA76ED395 (user_id), PRIMARY KEY (user_id, flash_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE user_favorite_flash ADD CONSTRAINT `FK_72C3AB7E25F8D5EA` FOREIGN KEY (flash_id) REFERENCES flash (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_favorite_flash ADD CONSTRAINT `FK_72C3AB7EA76ED395` FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE comment ADD author_id INT NOT NULL, ADD updated_at DATETIME DEFAULT NULL, DROP author_name');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT `FK_9474526CF675F31B` FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_9474526CF675F31B ON comment (author_id)');
    }
}

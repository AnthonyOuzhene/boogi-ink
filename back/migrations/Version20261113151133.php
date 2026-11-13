<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261113151133 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Catégories : slug (nom pour les adresses web, ex. fine-line), rempli pour les catégories existantes';
    }

    public function up(Schema $schema): void
    {
        // 1. Colonne d'abord facultative : les catégories déjà en base n'ont pas encore de slug
        $this->addSql('ALTER TABLE category ADD slug VARCHAR(120) DEFAULT NULL');
        // 2. Slug des catégories existantes, calculé depuis le nom : minuscules, espaces → tirets.
        //    (Les noms actuels n'ont pas d'accent ; les prochains passeront par setName() et AsciiSlugger.)
        $this->addSql("UPDATE category SET slug = LOWER(REPLACE(TRIM(name), ' ', '-'))");
        // 3. Puis obligatoire et unique, comme le décrit l'entité
        $this->addSql('ALTER TABLE category MODIFY slug VARCHAR(120) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_64C19C1989D9B62 ON category (slug)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_64C19C1989D9B62 ON category');
        $this->addSql('ALTER TABLE category DROP slug');
    }
}

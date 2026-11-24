<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\String\Slugger\AsciiSlugger;

final class Version20261124140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Réalisations : slug calculé depuis le titre, pour des adresses lisibles (?realisation=manchette-graphique)';
    }

    public function up(Schema $schema): void
    {
        // 1. Colonne d'abord facultative, le temps de remplir les réalisations existantes
        $this->addSql('ALTER TABLE gallery ADD slug VARCHAR(170) DEFAULT NULL');

        // 2. Un slug par réalisation existante, calculé comme dans Gallery::setName().
        //    Deux titres identiques : le second reçoit "-2", le troisième "-3"…
        //    (les nouvelles réalisations, elles, sont refusées si le titre existe déjà)
        $slugger = new AsciiSlugger('fr');
        $used = [];
        foreach ($this->connection->fetchAllAssociative('SELECT id, name FROM gallery ORDER BY id') as $row) {
            $base = $slugger->slug($row['name'])->lower()->toString() ?: 'realisation';
            $slug = $base;
            for ($i = 2; isset($used[$slug]); ++$i) {
                $slug = $base.'-'.$i;
            }
            $used[$slug] = true;
            $this->addSql('UPDATE gallery SET slug = ? WHERE id = ?', [$slug, $row['id']]);
        }

        // 3. Puis obligatoire et unique, comme le décrit l'entité
        $this->addSql('ALTER TABLE gallery MODIFY slug VARCHAR(170) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_472B783A989D9B62 ON gallery (slug)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_472B783A989D9B62 ON gallery');
        $this->addSql('ALTER TABLE gallery DROP slug');
    }
}

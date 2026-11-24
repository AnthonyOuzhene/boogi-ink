<?php

namespace App\Command;

use App\Content\LegalPages;
use App\Entity\Page;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Usage : ddev console app:pages:install
 * Crée les pages légales (mentions légales, confidentialité, règles sanitaires) qui n'existent
 * pas encore, avec leur texte de départ (App\Content\LegalPages). À lancer une fois en production.
 *
 * Les pages déjà présentes ne sont PAS modifiées (elles ont pu être complétées dans le backoffice).
 * --force : remplace aussi le texte des pages existantes (⚠ efface les modifications faites dans le backoffice).
 */
#[AsCommand(
    name: 'app:pages:install',
    description: 'Crée les pages légales (mentions légales, confidentialité, règles sanitaires)',
)]
class InstallPagesCommand
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function __invoke(
        SymfonyStyle $io,
        #[Option('Remplace aussi le texte des pages existantes (efface les modifications du backoffice)')] bool $force = false,
    ): int {
        $repository = $this->entityManager->getRepository(Page::class);

        foreach (LegalPages::all() as $data) {
            $page = $repository->findOneBy(['slug' => $data['slug']]);

            if (null === $page) {
                $this->entityManager->persist((new Page())->setTitle($data['title'])->setSlug($data['slug'])->setContent($data['content']));
                $io->writeln(sprintf('Créée : %s', $data['title']));
            } elseif ($force) {
                $page->setTitle($data['title'])->setContent($data['content']);
                $io->writeln(sprintf('Remplacée : %s', $data['title']));
            } else {
                $io->writeln(sprintf('Déjà présente, inchangée : %s (--force pour la remplacer)', $data['title']));
            }
        }

        $this->entityManager->flush();
        $io->success('Pages légales prêtes. Complétez les passages [À compléter : …] dans le backoffice, menu « Pages ».');

        return Command::SUCCESS;
    }
}

<?php

namespace App\Command;

use App\Service\InstagramSync;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Usage : ddev console app:instagram:sync
 * Récupère les 6 derniers posts Instagram du salon pour l'accueil.
 * En production : tâche planifiée (cron) toutes les 3 heures, voir DEPLOIEMENT.md.
 *
 * ddev console app:instagram:sync --demo : posts d'exemple, en attendant le jeton.
 */
#[AsCommand(
    name: 'app:instagram:sync',
    description: 'Met à jour les derniers posts Instagram affichés sur l\'accueil',
)]
class InstagramSyncCommand
{
    public function __construct(private readonly InstagramSync $instagramSync)
    {
    }

    public function __invoke(
        SymfonyStyle $io,
        #[Option('Posts d\'exemple (photos des fixtures) en attendant le jeton Instagram')] bool $demo = false,
    ): int {
        if ($demo) {
            $count = $this->instagramSync->loadDemo();
            $io->success(sprintf('%d posts d\'exemple affichés sur l\'accueil.', $count));

            return Command::SUCCESS;
        }

        try {
            $count = $this->instagramSync->sync();
        } catch (\RuntimeException $e) {
            // Les posts déjà enregistrés restent affichés
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        $io->success(sprintf('%d posts Instagram à jour sur l\'accueil.', $count));

        return Command::SUCCESS;
    }
}

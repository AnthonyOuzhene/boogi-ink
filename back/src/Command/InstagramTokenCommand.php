<?php

namespace App\Command;

use App\Service\InstagramSync;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Usage : ddev console app:instagram:token
 * À lancer une fois avec le jeton généré depuis l'application Meta ; il se renouvelle ensuite tout seul.
 * Saisie masquée, pour qu'il n'apparaisse pas dans l'historique du terminal.
 */
#[AsCommand(
    name: 'app:instagram:token',
    description: 'Enregistre le jeton d\'accès Instagram du salon',
)]
class InstagramTokenCommand
{
    public function __construct(private readonly InstagramSync $instagramSync)
    {
    }

    public function __invoke(SymfonyStyle $io): int
    {
        $token = $io->askHidden('Jeton Instagram longue durée (saisie masquée)');
        if (null === $token || '' === trim($token)) {
            $io->error('Aucun jeton saisi.');

            return Command::FAILURE;
        }

        $this->instagramSync->saveToken($token);
        $io->writeln('Jeton enregistré. Première synchronisation…');

        try {
            $count = $this->instagramSync->sync();
        } catch (\RuntimeException $e) {
            $io->error($e->getMessage().' Vérifiez le jeton, puis relancez cette commande.');

            return Command::FAILURE;
        }

        $io->success(sprintf('Connecté : %d posts Instagram affichés sur l\'accueil.', $count));

        return Command::SUCCESS;
    }
}

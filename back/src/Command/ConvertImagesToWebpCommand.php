<?php

namespace App\Command;

use App\Service\ImageOptimizer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Usage : ddev console app:images:convert-webp
 * À lancer une fois (en local, puis en production) pour les photos envoyées avant la conversion
 * automatique (ImageWebpListener). Sans risque à relancer : les WebP sont ignorés.
 */
#[AsCommand(
    name: 'app:images:convert-webp',
    description: 'Convertit en WebP (2 000 px max) les photos déjà envoyées depuis le backoffice',
)]
class ConvertImagesToWebpCommand
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ImageOptimizer $imageOptimizer,
    ) {
    }

    public function __invoke(SymfonyStyle $io): int
    {
        $savedTotal = 0;

        foreach (array_keys(ImageOptimizer::IMAGE_FIELDS) as $class) {
            $metadata = $this->entityManager->getClassMetadata($class);
            $converted = 0;

            foreach ($this->entityManager->getRepository($class)->findAll() as $entity) {
                $before = $this->fileNames($entity, $metadata);
                $savedTotal += $this->imageOptimizer->optimizeEntity($entity, $metadata);
                if ($before !== $this->fileNames($entity, $metadata)) {
                    ++$converted;
                }
            }

            $io->writeln(sprintf('%s : %d fiche(s) mise(s) à jour', $metadata->getReflectionClass()->getShortName(), $converted));
        }

        // ImageWebpListener voit déjà des .webp et n'y touche plus
        $this->entityManager->flush();

        $io->success(sprintf('Terminé : %s gagnés.', $this->formatBytes($savedTotal)));

        return Command::SUCCESS;
    }

    /** @return list<mixed> */
    private function fileNames(object $entity, \Doctrine\ORM\Mapping\ClassMetadata $metadata): array
    {
        return array_map(
            fn (string $field) => $metadata->getFieldValue($entity, $field),
            array_keys(ImageOptimizer::IMAGE_FIELDS[$metadata->getName()]),
        );
    }

    private function formatBytes(int $bytes): string
    {
        return $bytes >= 1048576 ? round($bytes / 1048576, 1).' Mo' : round($bytes / 1024).' Ko';
    }
}

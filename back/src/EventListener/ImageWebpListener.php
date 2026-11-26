<?php

namespace App\EventListener;

use App\Service\ImageOptimizer;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;

/**
 * onFlush : EasyAdmin a déjà déposé les fichiers dans public/uploads/, on les convertit en WebP
 * et on corrige le nom de fichier avant l'écriture en base (y compris les Picture d'une réalisation).
 */
#[AsDoctrineListener(event: Events::onFlush)]
final class ImageWebpListener
{
    public function __construct(private readonly ImageOptimizer $imageOptimizer)
    {
    }

    public function onFlush(OnFlushEventArgs $args): void
    {
        $entityManager = $args->getObjectManager();
        $unitOfWork = $entityManager->getUnitOfWork();

        $entities = [...$unitOfWork->getScheduledEntityInsertions(), ...$unitOfWork->getScheduledEntityUpdates()];

        foreach ($entities as $entity) {
            $metadata = $entityManager->getClassMetadata($entity::class);
            if (!isset(ImageOptimizer::IMAGE_FIELDS[$metadata->getName()])) {
                continue;
            }

            $before = array_map(fn (string $field) => $metadata->getFieldValue($entity, $field), array_keys(ImageOptimizer::IMAGE_FIELDS[$metadata->getName()]));
            $this->imageOptimizer->optimizeEntity($entity, $metadata);
            $after = array_map(fn (string $field) => $metadata->getFieldValue($entity, $field), array_keys(ImageOptimizer::IMAGE_FIELDS[$metadata->getName()]));

            if ($before !== $after) {
                // Dans onFlush, Doctrine n'écrirait pas le nouveau nom sans ce recalcul
                $unitOfWork->recomputeSingleEntityChangeSet($metadata, $entity);
            }
        }
    }
}

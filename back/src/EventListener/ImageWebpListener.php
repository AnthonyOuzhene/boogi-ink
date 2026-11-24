<?php

namespace App\EventListener;

use App\Service\ImageOptimizer;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;

/**
 * Convertit en WebP les photos envoyées depuis le backoffice, au moment de l'enregistrement.
 *
 * "onFlush" : Doctrine s'apprête à écrire en base toutes les fiches créées ou modifiées.
 * EasyAdmin a déjà déposé les fichiers dans public/uploads/… : on les convertit, puis on
 * corrige le nom du fichier dans la fiche avant l'écriture. Ça couvre aussi les photos
 * supplémentaires des réalisations (Picture), enregistrées en même temps que la réalisation.
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

            // Un nom de fichier a changé : Doctrine doit recalculer ce qu'il va écrire
            $before = array_map(fn (string $field) => $metadata->getFieldValue($entity, $field), array_keys(ImageOptimizer::IMAGE_FIELDS[$metadata->getName()]));
            $this->imageOptimizer->optimizeEntity($entity, $metadata);
            $after = array_map(fn (string $field) => $metadata->getFieldValue($entity, $field), array_keys(ImageOptimizer::IMAGE_FIELDS[$metadata->getName()]));

            if ($before !== $after) {
                $unitOfWork->recomputeSingleEntityChangeSet($metadata, $entity);
            }
        }
    }
}

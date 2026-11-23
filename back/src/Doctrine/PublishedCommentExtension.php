<?php

namespace App\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Comment;
use Doctrine\ORM\QueryBuilder;

/**
 * Ajoute "WHERE is_published = true" à toutes les requêtes de l'API sur les avis,
 * pour la liste (GET /api/comments) comme pour le détail (GET /api/comments/12).
 *
 * Grâce à l'autoconfiguration de Symfony, implémenter ces interfaces suffit :
 * API Platform détecte et utilise cette classe tout seul.
 * EasyAdmin n'est pas concerné : il ne passe pas par API Platform (le backoffice voit
 * donc aussi les avis en attente, pour les publier).
 */
final class PublishedCommentExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        $this->onlyPublished($queryBuilder, $resourceClass);
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, ?Operation $operation = null, array $context = []): void
    {
        $this->onlyPublished($queryBuilder, $resourceClass);
    }

    private function onlyPublished(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        if (Comment::class !== $resourceClass) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $queryBuilder->andWhere(sprintf('%s.isPublished = true', $rootAlias));
    }
}

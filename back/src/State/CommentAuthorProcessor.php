<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Comment;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Exécuté sur POST /api/comments.
 * L'auteur n'est PAS envoyé par le front (il pourrait mentir) : on prend l'utilisateur
 * identifié par le jeton JWT. L'avis reste non publié tant qu'un admin ne l'a pas validé.
 *
 * @implements ProcessorInterface<Comment, Comment>
 */
final class CommentAuthorProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private readonly ProcessorInterface $persistProcessor,
        private readonly Security $security,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Comment
    {
        \assert($data instanceof Comment);

        $user = $this->security->getUser();
        \assert($user instanceof User);

        $data->setAuthor($user);
        $data->setIsPublished(false);

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}

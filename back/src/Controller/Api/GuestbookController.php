<?php

namespace App\Controller\Api;

use App\Controller\Api\Input\CommentInput;
use App\Entity\Comment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Dépôt public d'un avis, sans compte. La lecture reste assurée par API Platform (GET /api/comments).
 */
final class GuestbookController extends AbstractController
{
    /**
     * Avis toujours créé en attente : visible seulement une fois publié dans le backoffice.
     */
    #[Route('/api/comments', name: 'api_create_comment', methods: ['POST'])]
    public function create(#[MapRequestPayload] CommentInput $input, EntityManagerInterface $entityManager): Response
    {
        // Pot de miel rempli = robot : fausse réponse de succès pour ne pas lui révéler le piège
        if ('' !== trim((string) $input->website)) {
            return new Response(null, Response::HTTP_ACCEPTED);
        }

        $comment = (new Comment())
            ->setAuthorName(trim($input->authorName))
            ->setTitle(trim($input->title))
            ->setMessage(trim($input->message))
            ->setProjectName($input->projectName ? trim($input->projectName) : null)
            ->setIsPublished(false);

        $entityManager->persist($comment);
        $entityManager->flush();

        return new Response(null, Response::HTTP_ACCEPTED);
    }
}

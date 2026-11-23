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
 * Livre d'or : dépôt d'un avis par le formulaire PUBLIC du site, sans compte.
 * La lecture des avis publiés reste assurée par API Platform (GET /api/comments).
 */
final class GuestbookController extends AbstractController
{
    /**
     * POST /api/comments : déposer un avis.
     *
     * - #[MapRequestPayload] lit le JSON dans CommentInput et le valide (422 si nom ou message vide…) ;
     * - l'avis est TOUJOURS créé "en attente" : il n'apparaît dans le livre d'or qu'une fois
     *   publié dans le backoffice (le salon modère chaque avis) ;
     * - la date de dépôt est fixée par le constructeur de Comment.
     */
    #[Route('/api/comments', name: 'api_create_comment', methods: ['POST'])]
    public function create(#[MapRequestPayload] CommentInput $input, EntityManagerInterface $entityManager): Response
    {
        // Champ "pot de miel" rempli : c'est un robot. On répond comme si tout allait bien
        // (pour ne pas lui apprendre à contourner le piège), mais rien n'est enregistré.
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

        // 202 Accepted : reçu, mais pas encore visible (en attente de validation)
        return new Response(null, Response::HTTP_ACCEPTED);
    }
}

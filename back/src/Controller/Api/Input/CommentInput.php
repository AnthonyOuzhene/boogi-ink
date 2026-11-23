<?php

namespace App\Controller\Api\Input;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Formulaire PUBLIC du livre d'or (GuestbookController::create()), sans compte.
 *
 * Il ne contient QUE ce que le visiteur peut écrire : envoyer "isPublished" dans le JSON
 * n'a donc aucun effet, le statut "en attente" est fixé par le contrôleur.
 */
final class CommentInput
{
    #[Assert\NotBlank(message: 'Indique ton prénom ou un pseudo.')]
    #[Assert\Length(max: 80, maxMessage: 'Le nom ne doit pas dépasser {{ limit }} caractères.')]
    public ?string $authorName = null;

    #[Assert\NotBlank(message: 'Le titre est obligatoire.')]
    #[Assert\Length(max: 150, maxMessage: 'Le titre ne doit pas dépasser {{ limit }} caractères.')]
    public ?string $title = null;

    #[Assert\NotBlank(message: 'Le message est obligatoire.')]
    #[Assert\Length(max: 5000, maxMessage: 'Le message ne doit pas dépasser {{ limit }} caractères.')]
    public ?string $message = null;

    #[Assert\Length(max: 150, maxMessage: 'Le projet ne doit pas dépasser {{ limit }} caractères.')]
    public ?string $projectName = null;

    /**
     * Anti-spam ("pot de miel") : champ caché sur le site, un humain le laisse vide.
     * Les robots remplissent tous les champs : s'il contient quelque chose, l'avis est ignoré
     * (voir GuestbookController). Le nom "website" est volontairement tentant pour un robot.
     */
    public ?string $website = null;
}

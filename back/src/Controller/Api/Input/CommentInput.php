<?php

namespace App\Controller\Api\Input;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Uniquement les champs que le visiteur peut écrire : envoyer "isPublished" n'a aucun effet.
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
     * Pot de miel anti-robots : champ caché sur le site, doit rester vide (voir GuestbookController).
     * Le nom "website" est volontairement tentant pour un robot.
     */
    public ?string $website = null;
}

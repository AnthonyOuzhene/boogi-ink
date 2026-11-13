<?php

namespace App\Controller\Api\Input;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Formulaire d'un avis du livre d'or, pour le DÉPÔT (AccountController::createComment())
 * comme pour la MODIFICATION (AccountController::updateComment()) : ce sont les mêmes champs.
 *
 * Il ne contient QUE ce que l'auteur peut écrire : envoyer "isPublished" ou "author"
 * dans le JSON n'a donc aucun effet. L'auteur vient du jeton, le statut est fixé par le contrôleur.
 */
final class CommentInput
{
    #[Assert\NotBlank(message: 'Le titre est obligatoire.')]
    #[Assert\Length(max: 150, maxMessage: 'Le titre ne doit pas dépasser {{ limit }} caractères.')]
    public ?string $title = null;

    #[Assert\NotBlank(message: 'Le message est obligatoire.')]
    #[Assert\Length(max: 5000, maxMessage: 'Le message ne doit pas dépasser {{ limit }} caractères.')]
    public ?string $message = null;

    #[Assert\Length(max: 150, maxMessage: 'Le projet ne doit pas dépasser {{ limit }} caractères.')]
    public ?string $projectName = null;

    /** Date de passage au salon (facultative), envoyée au format "2026-10-15" */
    public ?\DateTimeImmutable $realisedAt = null;
}

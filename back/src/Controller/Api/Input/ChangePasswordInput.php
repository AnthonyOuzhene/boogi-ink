<?php

namespace App\Controller\Api\Input;

use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO (Data Transfer Object) : reçoit et valide le formulaire "changer mon mot de passe".
 * Ce n'est pas une entité : rien n'est enregistré tel quel en base.
 *
 * Utilisé par AccountController::changePassword() : l'attribut #[MapRequestPayload]
 * y place le JSON envoyé par le front, puis le valide avant d'exécuter la méthode.
 */
final class ChangePasswordInput
{
    /**
     * UserPassword : Symfony vérifie que c'est le mot de passe de l'utilisateur CONNECTÉ (jeton JWT).
     * Sequentially : les règles sont vérifiées dans l'ordre et on s'arrête à la première erreur
     * (un champ vide n'affiche donc pas aussi "mot de passe incorrect").
     */
    #[Assert\Sequentially([
        new Assert\NotBlank(message: 'Saisissez votre mot de passe actuel.'),
        new UserPassword(message: 'Le mot de passe actuel est incorrect.'),
    ])]
    public ?string $currentPassword = null;

    #[Assert\NotBlank(message: 'Saisissez un nouveau mot de passe.')]
    #[Assert\Length(min: 8, max: 4096, minMessage: 'Le nouveau mot de passe doit contenir au moins {{ limit }} caractères.')]
    #[Assert\NotEqualTo(propertyPath: 'currentPassword', message: "Le nouveau mot de passe doit être différent de l'actuel.")]
    public ?string $newPassword = null;
}

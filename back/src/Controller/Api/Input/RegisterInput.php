<?php

namespace App\Controller\Api\Input;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Formulaire d'inscription (AccountController::register()).
 *
 * Il ne contient QUE ce qu'un visiteur peut choisir : envoyer "roles" dans le JSON
 * n'a aucun effet, un compte créé depuis le site est toujours un simple client.
 */
final class RegisterInput
{
    #[Assert\NotBlank(message: 'Choisis un pseudo.')]
    #[Assert\Length(max: 100, maxMessage: 'Le pseudo ne doit pas dépasser {{ limit }} caractères.')]
    public ?string $name = null;

    // Sequentially : un champ vide n'affiche que "Saisissez…", pas aussi "pas valide".
    // L'unicité de l'e-mail est vérifiée ensuite, sur l'entité User (#[UniqueEntity]).
    #[Assert\Sequentially([
        new Assert\NotBlank(message: 'Saisis ton adresse e-mail.'),
        new Assert\Email(message: "Cette adresse e-mail n'est pas valide."),
        new Assert\Length(max: 180),
    ])]
    public ?string $email = null;

    #[Assert\NotBlank(message: 'Choisis un mot de passe.')]
    #[Assert\Length(min: 8, max: 4096, minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères.')]
    public ?string $plainPassword = null;
}

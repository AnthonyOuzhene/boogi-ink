<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Un admin peut gérer tous les comptes sauf celui d'un super admin ; le super admin gère tout.
 * Appliqué par UserCrudController (setEntityPermission) à la liste, la fiche, la modification et la suppression.
 */
final class AdminUserVoter extends Voter
{
    public const MANAGE = 'ADMIN_USER_MANAGE';

    public function __construct(private readonly Security $security)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        // Sans sujet (bouton "Ajouter") : autorisé, le nouveau compte sera admin
        return self::MANAGE === $attribute && (null === $subject || $subject instanceof User);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        if ($this->security->isGranted('ROLE_SUPER_ADMIN')) {
            return true;
        }

        return !($subject instanceof User && in_array('ROLE_SUPER_ADMIN', $subject->getRoles(), true));
    }
}

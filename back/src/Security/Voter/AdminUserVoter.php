<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Qui peut voir, modifier ou supprimer un compte du backoffice (menu "Administrateurs") ?
 *
 * Un "voter" est consulté par Symfony à chaque isGranted('ADMIN_USER_MANAGE', $compte) :
 * - le super admin (développeur) peut gérer tous les comptes ;
 * - un admin (le salon) peut gérer les comptes admin, mais PAS celui d'un super admin :
 *   il ne le voit même pas dans la liste.
 *
 * Utilisé par UserCrudController (setEntityPermission) : EasyAdmin l'applique à la liste,
 * à la fiche, à la modification et à la suppression.
 */
final class AdminUserVoter extends Voter
{
    public const MANAGE = 'ADMIN_USER_MANAGE';

    public function __construct(private readonly Security $security)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        // Sans fiche précise (ex. bouton "Ajouter") : on laisse passer, le nouveau compte sera admin
        return self::MANAGE === $attribute && (null === $subject || $subject instanceof User);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        if ($this->security->isGranted('ROLE_SUPER_ADMIN')) {
            return true;
        }

        // Admin : tout compte, sauf celui d'un super admin
        return !($subject instanceof User && in_array('ROLE_SUPER_ADMIN', $subject->getRoles(), true));
    }
}

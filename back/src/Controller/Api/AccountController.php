<?php

namespace App\Controller\Api;

use App\Controller\Api\Input\ChangePasswordInput;
use App\Controller\Api\Input\CommentInput;
use App\Controller\Api\Input\RegisterInput;
use App\Entity\Comment;
use App\Entity\User;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Le compte client : inscription (publique) et espace client /api/me/... (connecté).
 *
 * Les routes commencent par /api : elles passent par le pare-feu "api" (security.yaml),
 * l'utilisateur est donc identifié par son jeton JWT, comme pour le reste de l'API.
 * La protection est posée méthode par méthode : l'inscription doit rester ouverte à tous.
 */
#[Route('/api', name: 'api_')]
final class AccountController extends AbstractController
{
    /**
     * Comment transformer un avis en JSON pour son auteur (liste ET modification) :
     * - groups : les champs publics (comment:read) + statut et date de modification (comment:owner) ;
     * - skip_null_values : un champ vide est omis du JSON, comme avec API Platform
     *   (le front reçoit exactement le même format qu'avant).
     */
    /** Un compte en JSON : id, e-mail, rôles et nom (groupe user:read), jamais le mot de passe */
    private const USER_CONTEXT = [
        'groups' => ['user:read'],
        AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
    ];

    private const OWNER_COMMENT_CONTEXT = [
        'groups' => ['comment:read', 'comment:owner'],
        AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
    ];

    /**
     * POST /api/users : inscription d'un nouveau client. Route PUBLIQUE (pas de #[IsGranted]).
     *
     * La validation se fait en deux temps :
     * 1. #[MapRequestPayload] valide le FORMAT (RegisterInput) : pseudo, e-mail bien formé,
     *    mot de passe d'au moins 8 caractères → 422 sinon, sans entrer dans la méthode ;
     * 2. on valide ensuite l'ENTITÉ User, dont le #[UniqueEntity] vérifie en base
     *    que l'e-mail n'est pas déjà utilisé → 422 sinon.
     * (Si les deux sont faux en même temps, l'erreur d'e-mail arrive au second envoi :
     *  depuis le site, le navigateur bloque déjà les erreurs de l'étape 1.)
     *
     * Le compte créé est toujours un simple client : le rôle n'est pas choisi par le visiteur.
     */
    #[Route('/users', name: 'register', methods: ['POST'])]
    public function register(
        #[MapRequestPayload] RegisterInput $input,
        ValidatorInterface $validator,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        $user = (new User())
            ->setName($input->name)
            ->setEmail($input->email)
            ->setRoles([]); // ROLE_USER est ajouté automatiquement par User::getRoles()

        // Étape 2 : règles de l'entité, dont #[UniqueEntity] (e-mail déjà utilisé ?)
        $violations = $validator->validate($user);
        if (count($violations) > 0) {
            // Même réponse 422 (et même format JSON) que les erreurs de #[MapRequestPayload]
            throw new UnprocessableEntityHttpException('', new ValidationFailedException($user, $violations));
        }

        $user->setPassword($passwordHasher->hashPassword($user, $input->plainPassword));

        $entityManager->persist($user); // nouvel objet : il faut le signaler à Doctrine…
        $entityManager->flush();        // …avant d'exécuter l'INSERT

        // 201 Created + le compte créé (mêmes champs que GET /api/me, jamais le mot de passe)
        return $this->json($user, Response::HTTP_CREATED, [], self::USER_CONTEXT);
    }

    /**
     * GET /api/me : le profil de l'utilisateur connecté (identifié par son jeton JWT).
     *
     * Utilisé par le front au démarrage et après la connexion (AuthProvider) :
     * nom affiché, e-mail, et rôles (pour savoir s'il faut afficher le lien "Backoffice").
     * Le groupe user:read ne contient QUE ces champs : le mot de passe haché n'est jamais renvoyé.
     */
    #[Route('/me', name: 'me_profile', methods: ['GET'])]
    #[IsGranted('ROLE_USER')] // sans jeton valide → 401
    public function profile(): JsonResponse
    {
        return $this->json($this->getUser(), Response::HTTP_OK, [], self::USER_CONTEXT);
    }

    /**
     * POST /api/me/password : changer son mot de passe.
     *
     * #[MapRequestPayload] transforme le JSON reçu en ChangePasswordInput et le VALIDE
     * (mot de passe actuel correct, nouveau mot de passe d'au moins 8 caractères…).
     * Si une règle n'est pas respectée, Symfony répond 422 avec la liste des erreurs,
     * sans même entrer dans la méthode.
     */
    #[Route('/me/password', name: 'me_change_password', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function changePassword(
        #[MapRequestPayload] ChangePasswordInput $input,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
    ): Response {
        // L'utilisateur modifié est TOUJOURS celui du jeton : impossible de changer le mot de passe d'un autre
        /** @var User $user */
        $user = $this->getUser();

        $user->setPassword($passwordHasher->hashPassword($user, $input->newPassword));
        $entityManager->flush(); // l'utilisateur vient déjà de la base : pas besoin de persist()

        return new Response(null, Response::HTTP_NO_CONTENT); // 204 : succès, rien à renvoyer
    }

    /**
     * GET /api/me/comments : TOUS mes avis, publiés ou en attente de validation, du plus récent au plus ancien.
     *
     * L'auteur vient toujours du jeton JWT : impossible de lire les avis de quelqu'un d'autre.
     * Les groupes de sérialisation choisissent les champs renvoyés : comment:owner ajoute
     * le statut (isPublished) et la date de modification, absents du livre d'or public.
     */
    #[Route('/me/comments', name: 'me_comments', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function listComments(CommentRepository $commentRepository): JsonResponse
    {
        $comments = $commentRepository->findBy(
            ['author' => $this->getUser()],
            ['createdAt' => 'DESC', 'id' => 'DESC'],
        );

        return $this->json($comments, Response::HTTP_OK, [], self::OWNER_COMMENT_CONTEXT);
    }

    /**
     * POST /api/me/comments : déposer un avis dans le livre d'or.
     *
     * - l'auteur est TOUJOURS l'utilisateur du jeton (il n'est pas lu dans le JSON) ;
     * - l'avis est créé "en attente" : il n'apparaît dans le livre d'or qu'une fois validé
     *   dans le backoffice ;
     * - la date de dépôt est fixée par le constructeur de Comment.
     */
    #[Route('/me/comments', name: 'me_create_comment', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function createComment(
        #[MapRequestPayload] CommentInput $input,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();

        $comment = (new Comment())
            ->setAuthor($user)
            ->setIsPublished(false);
        $this->fillComment($comment, $input);

        $entityManager->persist($comment); // nouvel objet : à signaler à Doctrine avant le flush
        $entityManager->flush();

        // 201 Created + l'avis créé (mêmes champs que GET /api/me/comments)
        return $this->json($comment, Response::HTTP_CREATED, [], self::OWNER_COMMENT_CONTEXT);
    }

    /**
     * PATCH /api/me/comments/{id} : modifier un de ses avis (pas de suppression possible).
     *
     * - #[MapEntity] charge l'avis depuis la base à partir de {id} (404 s'il n'existe pas) ;
     * - on vérifie que c'est bien l'avis de l'utilisateur connecté (403 sinon) ;
     * - #[MapRequestPayload] valide le formulaire (422 si titre vide…) ;
     * - l'avis repasse en attente : le salon doit valider le nouveau texte.
     */
    #[Route('/me/comments/{id}', name: 'me_update_comment', requirements: ['id' => '\d+'], methods: ['PATCH'])]
    #[IsGranted('ROLE_USER')]
    public function updateComment(
        #[MapEntity(id: 'id')] Comment $comment,
        #[MapRequestPayload] CommentInput $input,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        if ($comment->getAuthor() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Tu ne peux modifier que tes propres avis.');
        }

        // Le texte change : l'avis repasse en attente de validation
        $this->fillComment($comment, $input);
        $comment
            ->setIsPublished(false)
            ->setUpdatedAt(new \DateTimeImmutable());
        $entityManager->flush();

        // On renvoie l'avis à jour, avec les mêmes champs que GET /api/me/comments
        return $this->json($comment, Response::HTTP_OK, [], self::OWNER_COMMENT_CONTEXT);
    }

    /**
     * Recopie le formulaire sur l'avis (dépôt et modification).
     * Seuls les champs du DTO sont recopiés : auteur et statut ne viennent jamais du JSON.
     */
    private function fillComment(Comment $comment, CommentInput $input): void
    {
        $comment
            ->setTitle($input->title)
            ->setMessage($input->message)
            ->setProjectName($input->projectName ?: null)
            ->setRealisedAt($input->realisedAt);
    }
}

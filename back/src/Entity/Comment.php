<?php

namespace App\Entity;

use App\Repository\CommentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    // API Platform : le livre d'or PUBLIC, en lecture seule (avis publiés uniquement,
    // voir PublishedCommentExtension).
    // Dépôt d'un avis par le formulaire public (sans compte) : POST /api/comments,
    // GuestbookController. L'avis arrive en attente, publié depuis le backoffice.
    operations: [
        new Get(),
        new GetCollection(order: ['createdAt' => 'DESC']),
    ],
    normalizationContext: ['groups' => ['comment:read']],
)]
#[ORM\Entity(repositoryClass: CommentRepository::class)]
class Comment
{
    #[Groups(['comment:read'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['comment:read'])]
    #[ORM\Column(length: 150)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 150)]
    private ?string $title = null;

    #[Groups(['comment:read'])]
    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 5000)]
    private ?string $message = null;

    #[Groups(['comment:read'])]
    #[ORM\Column(length: 150, nullable: true)]
    #[Assert\Length(max: 150)]
    private ?string $projectName = null;

    #[Groups(['comment:read'])]
    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $realisedAt = null;

    #[Groups(['comment:read'])]
    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private bool $isPublished = false;

    /**
     * Nom affiché sous l'avis, tapé dans le formulaire public du livre d'or.
     * (Plus de compte client : l'avis n'est lié à aucun utilisateur.)
     */
    #[Groups(['comment:read'])]
    #[ORM\Column(length: 80)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 80)]
    private ?string $authorName = null;

    public function __construct()
    {
        // La date de l'avis est fixée automatiquement à la création
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function getProjectName(): ?string
    {
        return $this->projectName;
    }

    public function setProjectName(?string $projectName): static
    {
        $this->projectName = $projectName;

        return $this;
    }

    public function getRealisedAt(): ?\DateTimeImmutable
    {
        return $this->realisedAt;
    }

    public function setRealisedAt(?\DateTimeImmutable $realisedAt): static
    {
        $this->realisedAt = $realisedAt;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    // Pas de groupe : le statut n'est jamais envoyé par l'API (seuls les avis publiés sont listés)
    public function isPublished(): ?bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): static
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    public function getAuthorName(): ?string
    {
        return $this->authorName;
    }

    public function setAuthorName(?string $authorName): static
    {
        $this->authorName = $authorName;

        return $this;
    }

    public function __toString(): string
    {
        return (string) $this->title;
    }
}

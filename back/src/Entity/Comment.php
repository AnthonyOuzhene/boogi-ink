<?php

namespace App\Entity;

use App\Repository\CommentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    // API Platform : le livre d'or PUBLIC, en lecture seule (avis publiés uniquement,
    // voir PublishedCommentExtension).
    // Tout ce que fait un client (déposer, lister SES avis, les modifier) : AccountController,
    // sous /api/me/comments. Pas de suppression possible.
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

    #[Groups(['comment:read'])]
    #[ORM\ManyToOne(inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    private ?User $author = null;

    /** Date de la dernière modification par l'auteur (null si jamais modifié) */
    #[Groups(['comment:owner'])]
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

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

    // Le getter s'appelle isPublished() : sans SerializedName, le JSON l'exposerait sous le nom "published".
    // Groupe comment:owner : visible uniquement par l'auteur (AccountController, /api/me/comments)
    #[Groups(['comment:owner'])]
    #[SerializedName('isPublished')]
    public function isPublished(): ?bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): static
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function __toString(): string
    {
        return (string) $this->title;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}

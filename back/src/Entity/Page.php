<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\PageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Page de contenu simple (mentions légales, confidentialité, règles sanitaires…).
 *
 * L'API identifie une page par son slug et non par son id :
 *   GET /api/pages                    → liste (titre + slug), pour le pied de page
 *   GET /api/pages/mentions-legales   → une page complète
 */
#[ApiResource(
    operations: [
        new GetCollection(
            order: ['title' => 'ASC'],
            normalizationContext: ['groups' => ['page:list']],
            paginationEnabled: false,
        ),
        new Get(normalizationContext: ['groups' => ['page:list', 'page:read']]),
    ],
)]
#[ORM\Entity(repositoryClass: PageRepository::class)]
#[ORM\HasLifecycleCallbacks] // active les méthodes #[ORM\PrePersist] / #[ORM\PreUpdate] ci-dessous
#[UniqueEntity(fields: ['slug'], message: 'Une page utilise déjà cette adresse.')]
class Page
{
    // identifier: false → l'id reste interne, ce n'est pas lui qui apparaît dans l'URL de l'API
    #[ApiProperty(identifier: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['page:list'])]
    #[ORM\Column(length: 150)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 150)]
    private ?string $title = null;

    /** Partie de l'adresse : /pages/mentions-legales */
    #[ApiProperty(identifier: true)]
    #[Groups(['page:list'])]
    #[ORM\Column(length: 160, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 160)]
    #[Assert\Regex(pattern: '/^[a-z0-9]+(?:-[a-z0-9]+)*$/', message: 'Uniquement des minuscules, des chiffres et des tirets (ex. : mentions-legales).')]
    private ?string $slug = null;

    /** Texte de la page. Une ligne vide = nouveau paragraphe ; une ligne commençant par "## " = intertitre. */
    #[Groups(['page:read'])]
    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    private ?string $content = null;

    #[Groups(['page:read'])]
    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function __toString(): string
    {
        return (string) $this->title;
    }
}

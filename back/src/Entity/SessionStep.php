<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\SessionStepRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Étape du déroulé d'une séance ("Comment ça se passe").
 * Le titre et le début de la description sont repris en aperçu sur l'accueil,
 * le texte complet est sur L'atelier.
 */
#[ApiResource(
    operations: [new GetCollection(order: ['position' => 'ASC', 'id' => 'ASC'])],
    normalizationContext: ['groups' => ['session_step:read']],
    paginationEnabled: false,
)]
#[ORM\Entity(repositoryClass: SessionStepRepository::class)]
class SessionStep
{
    #[Groups(['session_step:read'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['session_step:read'])]
    #[ORM\Column(length: 150)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 150)]
    private ?string $title = null;

    /** Phrase courte, affichée sur l'accueil */
    #[Groups(['session_step:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $summary = null;

    #[Groups(['session_step:read'])]
    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    private ?string $description = null;

    /** Ordre d'affichage : la plus petite valeur en premier */
    #[ORM\Column]
    private int $position = 0;

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

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function setSummary(?string $summary): static
    {
        $this->summary = $summary;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(?int $position): static
    {
        $this->position = $position ?? 0;

        return $this;
    }

    public function __toString(): string
    {
        return (string) $this->title;
    }
}

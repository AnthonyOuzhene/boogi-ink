<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\HygieneCommitmentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Engagement d'hygiène et de sécurité, affiché en carte sur L'atelier.
 */
#[ApiResource(
    operations: [new GetCollection(order: ['position' => 'ASC', 'id' => 'ASC'])],
    normalizationContext: ['groups' => ['hygiene:read']],
    paginationEnabled: false,
)]
#[ORM\Entity(repositoryClass: HygieneCommitmentRepository::class)]
class HygieneCommitment
{
    /** Pictogrammes dessinés côté React (front/src/components/HygieneIcon.jsx) */
    public const ICONS = [
        'Bouclier (stérile)' => 'shield',
        'Flacon (encres)' => 'ink',
        'Diplôme (formation)' => 'training',
        'Pulsation (désinfection)' => 'clean',
    ];

    #[Groups(['hygiene:read'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['hygiene:read'])]
    #[ORM\Column(length: 150)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 150)]
    private ?string $title = null;

    #[Groups(['hygiene:read'])]
    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    private ?string $description = null;

    #[Groups(['hygiene:read'])]
    #[ORM\Column(length: 30)]
    #[Assert\Choice(choices: self::ICONS)]
    private ?string $icon = 'shield';

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): static
    {
        $this->icon = $icon;

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

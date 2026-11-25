<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\HomeCardRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/** Carte "à jouer" de l'accueil, sous la grande photo (symbole de tatouage dans les coins). */
#[ApiResource(
    operations: [new GetCollection(order: ['position' => 'ASC', 'id' => 'ASC'])],
    normalizationContext: ['groups' => ['home_card:read']],
    paginationEnabled: false,
)]
#[ORM\Entity(repositoryClass: HomeCardRepository::class)]
class HomeCard
{
    /** Libellé backoffice => clé en base. Clés à garder synchronisées avec ICONS dans front/src/components/salon/SalonCards.jsx */
    public const ICONS = [
        'Plume et encre' => 'quill',
        'Hirondelle' => 'swallow',
        'Aiguille' => 'needle',
        'Ancre' => 'anchor',
        'Rose' => 'rose',
        'Cœur' => 'heart',
        'Boussole' => 'compass',
        "Goutte d'encre" => 'drop',
    ];

    #[Groups(['home_card:read'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['home_card:read'])]
    #[ORM\Column(length: 60)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 60)]
    private ?string $title = null;

    /** Environ 120 caractères conseillés : la carte est petite */
    #[Groups(['home_card:read'])]
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $text = null;

    #[Groups(['home_card:read'])]
    #[ORM\Column(length: 20)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: self::ICONS)]
    private ?string $icon = null;

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

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): static
    {
        $this->text = $text;

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

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }

    /** Libellé dans les listes du backoffice */
    public function __toString(): string
    {
        return (string) $this->title;
    }
}

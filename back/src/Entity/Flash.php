<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\FlashRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Flash : motif dessiné à l'avance par l'artiste, prêt à être tatoué une seule fois.
 * Les disponibles sont listés avant les réservés.
 */
#[ApiResource(
    operations: [new GetCollection(order: ['isAvailable' => 'DESC', 'position' => 'ASC', 'id' => 'ASC'])],
    normalizationContext: ['groups' => ['flash:read']],
    paginationEnabled: false,
)]
#[ORM\Entity(repositoryClass: FlashRepository::class)]
class Flash
{
    public const UPLOAD_PATH = 'uploads/flashes';

    #[Groups(['flash:read'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['flash:read'])]
    #[ORM\Column(length: 150)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 150)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $picture = null;

    /** Taille indicative : "Environ 12 cm" */
    #[Groups(['flash:read'])]
    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\Length(max: 50)]
    private ?string $size = null;

    /** Emplacements conseillés : "Avant-bras, mollet" */
    #[Groups(['flash:read'])]
    #[ORM\Column(length: 150, nullable: true)]
    #[Assert\Length(max: 150)]
    private ?string $placement = null;

    /** DECIMAL : Doctrine le renvoie en chaîne ("120.00") pour ne pas perdre de précision. Vide = sur devis */
    #[Groups(['flash:read'])]
    /** Prix fixe : un flash est un dessin déjà fait, il n'est jamais "sur devis" */
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank(message: 'Un flash a toujours un prix fixe.')]
    #[Assert\Positive]
    private ?string $price = null;

    #[ORM\Column]
    private bool $isAvailable = true;

    #[ORM\Column]
    private int $position = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(?string $picture): static
    {
        $this->picture = $picture;

        return $this;
    }

    #[Groups(['flash:read'])]
    public function getPictureUrl(): ?string
    {
        return $this->picture ? '/'.self::UPLOAD_PATH.'/'.$this->picture : null;
    }

    public function getSize(): ?string
    {
        return $this->size;
    }

    public function setSize(?string $size): static
    {
        $this->size = $size;

        return $this;
    }

    public function getPlacement(): ?string
    {
        return $this->placement;
    }

    public function setPlacement(?string $placement): static
    {
        $this->placement = $placement;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(?string $price): static
    {
        $this->price = $price;

        return $this;
    }

    // Le getter s'appelle isAvailable() : sans SerializedName, le JSON l'exposerait sous le nom "available"
    #[Groups(['flash:read'])]
    #[SerializedName('isAvailable')]
    public function isAvailable(): bool
    {
        return $this->isAvailable;
    }

    public function setIsAvailable(?bool $isAvailable): static
    {
        $this->isAvailable = (bool) $isAvailable;

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
        return (string) $this->name;
    }
}

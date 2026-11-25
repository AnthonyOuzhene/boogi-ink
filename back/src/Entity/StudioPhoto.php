<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\StudioPhotoRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/** Photo de "Le lieu en images" (L'atelier) ; la première de la liste est affichée en grand. */
#[ApiResource(
    operations: [new GetCollection(order: ['position' => 'ASC', 'id' => 'ASC'])],
    normalizationContext: ['groups' => ['studio_photo:read']],
    paginationEnabled: false,
)]
#[ORM\Entity(repositoryClass: StudioPhotoRepository::class)]
class StudioPhoto
{
    public const UPLOAD_PATH = 'uploads/studio';

    #[Groups(['studio_photo:read'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** Nom de fichier seul ; l'API expose l'URL via getPictureUrl() */
    #[ORM\Column(length: 255)]
    private ?string $picture = null;

    /** Sert aussi de texte alternatif de l'image */
    #[Groups(['studio_photo:read'])]
    #[ORM\Column(length: 150, nullable: true)]
    #[Assert\Length(max: 150)]
    private ?string $caption = null;

    #[ORM\Column]
    private int $position = 0;

    public function getId(): ?int
    {
        return $this->id;
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

    #[Groups(['studio_photo:read'])]
    public function getPictureUrl(): ?string
    {
        return $this->picture ? '/'.self::UPLOAD_PATH.'/'.$this->picture : null;
    }

    public function getCaption(): ?string
    {
        return $this->caption;
    }

    public function setCaption(?string $caption): static
    {
        $this->caption = $caption;

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
        return (string) ($this->caption ?? $this->picture);
    }
}

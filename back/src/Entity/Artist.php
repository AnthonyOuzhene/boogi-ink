<?php

namespace App\Entity;

use App\Repository\ArtistRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/** Section "L'artiste" du site : une seule fiche (le backoffice empêche d'en créer une deuxième) */
#[ApiResource(
    operations: [new Get(), new GetCollection()],
    normalizationContext: ['groups' => ['artist:read']],
)]
#[ORM\Entity(repositoryClass: ArtistRepository::class)]
class Artist
{
    /** Dossier de la photo, relatif à public/ (utilisé par l'API et par EasyAdmin) */
    public const UPLOAD_PATH = 'uploads/artist';

    #[Groups(['artist:read'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['artist:read'])]
    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private ?string $firstName = null;

    #[Groups(['artist:read'])]
    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private ?string $lastName = null;

    #[Groups(['artist:read'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 300)]
    private ?string $quote = null;

    #[Groups(['artist:read'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $biography = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $picture = null;

    /** Présentation courte, affichée sur la page d'accueil (la biographie complète est sur L'atelier) */
    #[Groups(['artist:read'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 400)]
    private ?string $shortBio = null;

    /**
     * Affichées en pastilles. SIMPLE_ARRAY : stocké en base en texte séparé par des virgules.
     *
     * @var list<string>|null
     */
    #[Groups(['artist:read'])]
    #[ORM\Column(type: Types::SIMPLE_ARRAY, nullable: true)]
    private ?array $influences = null;

    /** Photo de la page L'atelier. Vide = on reprend la photo principale ($picture, utilisée sur l'accueil) */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $atelierPicture = null;
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getQuote(): ?string
    {
        return $this->quote;
    }

    public function setQuote(?string $quote): static
    {
        $this->quote = $quote;

        return $this;
    }

    public function getBiography(): ?string
    {
        return $this->biography;
    }

    public function setBiography(?string $biography): static
    {
        $this->biography = $biography;

        return $this;
    }

    public function __toString(): string
    {
        return trim($this->firstName.' '.$this->lastName);
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

    #[Groups(['artist:read'])]
    public function getPictureUrl(): ?string
    {
        return $this->picture ? '/'.self::UPLOAD_PATH.'/'.$this->picture : null;
    }

    public function getShortBio(): ?string
    {
        return $this->shortBio;
    }

    public function setShortBio(?string $shortBio): static
    {
        $this->shortBio = $shortBio;

        return $this;
    }

    public function getInfluences(): ?array
    {
        return $this->influences;
    }

    public function setInfluences(?array $influences): static
    {
        $this->influences = $influences;

        return $this;
    }

    public function getAtelierPicture(): ?string
    {
        return $this->atelierPicture;
    }

    public function setAtelierPicture(?string $atelierPicture): static
    {
        $this->atelierPicture = $atelierPicture;

        return $this;
    }

    #[Groups(['artist:read'])]
    public function getAtelierPictureUrl(): ?string
    {
        return $this->atelierPicture ? '/'.self::UPLOAD_PATH.'/'.$this->atelierPicture : null;
    }
}

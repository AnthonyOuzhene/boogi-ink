<?php

namespace App\Entity;

use App\Repository\HomeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [new Get(), new GetCollection()],
    normalizationContext: ['groups' => ['home:read']],
)]
#[ORM\Entity(repositoryClass: HomeRepository::class)]
class Home
{
    /** Dossier des images, relatif à public/ (utilisé par l'API et par EasyAdmin) */
    // La photo du salon sert de grande image en haut de l'accueil
    public const UPLOAD_PATH = 'uploads/home';

    #[Groups(['home:read'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['home:read'])]
    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[Groups(['home:read'])]
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $address = null;

    #[Groups(['home:read'])]
    #[ORM\Column(length: 10)]
    #[Assert\NotBlank]
    private ?string $zipCode = null;

    #[Groups(['home:read'])]
    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    private ?string $city = null;

    #[Groups(['home:read'])]
    #[ORM\Column(length: 20)]
    #[Assert\NotBlank]
    private ?string $phoneNumber = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $picture = null;

    #[ORM\Column]
    private bool $isOpen = true;

    #[Groups(['home:read'])]
    #[ORM\Column(length: 180, nullable: true)]
    #[Assert\Email]
    #[Assert\Length(max: 180)]
    private ?string $email = null;

    #[Groups(['home:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url(requireTld: true)]
    #[Assert\Length(max: 255)]
    private ?string $instagramUrl = null;

    #[Groups(['home:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url(requireTld: true)]
    #[Assert\Length(max: 255)]
    private ?string $facebookUrl = null;

    #[Groups(['home:read'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $openingHours = null;

    #[Groups(['home:read'])]
    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\Length(max: 100)]
    private ?string $heroTitle = null;

    #[Groups(['home:read'])]
    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\Length(max: 100)]
    private ?string $heroHighlight = null;

    /** L'atelier : introduction de "L'histoire" (naissance du salon, origine du nom) */
    #[Groups(['home:read'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $storyIntro = null;

    /** L'atelier : phrase sur l'ambiance du lieu, à côté des photos */
    #[Groups(['home:read'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $placeDescription = null;

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

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getZipCode(): ?string
    {
        return $this->zipCode;
    }

    public function setZipCode(?string $zipCode): static
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;

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

    // Le getter s'appelle isOpen() : sans SerializedName, le JSON l'exposerait sous le nom "open"
    #[Groups(['home:read'])]
    #[SerializedName('isOpen')]
    public function isOpen(): ?bool
    {
        return $this->isOpen;
    }

    public function setIsOpen(bool $isOpen): static
    {
        $this->isOpen = $isOpen;

        return $this;
    }

    public function __toString(): string
    {
        return (string) $this->name;
    }

    #[Groups(['home:read'])]
    public function getPictureUrl(): ?string
    {
        return $this->picture ? '/'.self::UPLOAD_PATH.'/'.$this->picture : null;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getInstagramUrl(): ?string
    {
        return $this->instagramUrl;
    }

    public function setInstagramUrl(?string $instagramUrl): static
    {
        $this->instagramUrl = $instagramUrl;

        return $this;
    }

    public function getFacebookUrl(): ?string
    {
        return $this->facebookUrl;
    }

    public function setFacebookUrl(?string $facebookUrl): static
    {
        $this->facebookUrl = $facebookUrl;

        return $this;
    }

    public function getOpeningHours(): ?string
    {
        return $this->openingHours;
    }

    public function setOpeningHours(?string $openingHours): static
    {
        $this->openingHours = $openingHours;

        return $this;
    }

    public function getHeroTitle(): ?string
    {
        return $this->heroTitle;
    }

    public function setHeroTitle(?string $heroTitle): static
    {
        $this->heroTitle = $heroTitle;

        return $this;
    }

    public function getHeroHighlight(): ?string
    {
        return $this->heroHighlight;
    }

    public function setHeroHighlight(?string $heroHighlight): static
    {
        $this->heroHighlight = $heroHighlight;

        return $this;
    }

    public function getStoryIntro(): ?string
    {
        return $this->storyIntro;
    }

    public function setStoryIntro(?string $storyIntro): static
    {
        $this->storyIntro = $storyIntro;

        return $this;
    }

    public function getPlaceDescription(): ?string
    {
        return $this->placeDescription;
    }

    public function setPlaceDescription(?string $placeDescription): static
    {
        $this->placeDescription = $placeDescription;

        return $this;
    }
}

<?php

namespace App\Entity;

use App\Repository\GalleryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;
use ApiPlatform\Metadata\QueryParameter;
use ApiPlatform\Doctrine\Orm\Filter\ExactFilter;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(
            order: ['realisedAt' => 'DESC'],
            // GET /api/galleries?category=fine-line (slug de la catégorie, lisible dans l'URL du site)
            parameters: ['category' => new QueryParameter(filter: new ExactFilter(), property: 'category.slug')],
        ),
    ],
    normalizationContext: ['groups' => ['gallery:read']],
)]
#[ORM\Entity(repositoryClass: GalleryRepository::class)]
class Gallery
{
    /** Partagé avec Picture : toutes les photos de réalisations sont dans le même dossier */
    public const UPLOAD_PATH = 'uploads/galleries';

    #[Groups(['gallery:read'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['gallery:read'])]
    #[ORM\Column(length: 150)]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $mainPicture = null;

    #[Groups(['gallery:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $video = null;

    #[Groups(['gallery:read'])]
    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $realisedAt = null;

    #[Groups(['gallery:read'])]
    #[ORM\ManyToOne(inversedBy: 'galleries')]
    private ?Category $category = null;

    /**
     * @var Collection<int, Picture>
     */
    #[Groups(['gallery:read'])]
    #[ORM\OneToMany(targetEntity: Picture::class, mappedBy: 'gallery', cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $pictures;

    /** Texte affiché à côté de la photo dans la visionneuse (facultatif) */
    #[Groups(['gallery:read'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    /**
     * Dessin préparatoire (nom du fichier, même dossier que les photos) : facultatif.
     * Renseigné, la visionneuse propose un curseur "du dessin à la peau" (voir getSketchUrl()).
     */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $sketch = null;

    /**
     * Vidéo timelapse de la séance (nom du fichier MP4, même dossier que les photos) : facultative.
     * Sur le site : badge "Timelapse" sur la vignette, et une diapositive vidéo dans la visionneuse.
     */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $timelapse = null;

    /** Coup de cœur de l'artiste : annotation "coup de cœur ♥" écrite au feutre sur le site */
    #[ORM\Column(options: ['default' => false])]
    private bool $isFavorite = false;

    public function __construct()
    {
        $this->pictures = new ArrayCollection();
    }

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

    public function getMainPicture(): ?string
    {
        return $this->mainPicture;
    }

    public function setMainPicture(?string $mainPicture): static
    {
        $this->mainPicture = $mainPicture;

        return $this;
    }

    public function getVideo(): ?string
    {
        return $this->video;
    }

    public function setVideo(?string $video): static
    {
        $this->video = $video;

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

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection<int, Picture>
     */
    public function getPictures(): Collection
    {
        return $this->pictures;
    }

    public function addPicture(Picture $picture): static
    {
        if (!$this->pictures->contains($picture)) {
            $this->pictures->add($picture);
            $picture->setGallery($this);
        }

        return $this;
    }

    public function removePicture(Picture $picture): static
    {
        if ($this->pictures->removeElement($picture)) {
            // set the owning side to null (unless already changed)
            if ($picture->getGallery() === $this) {
                $picture->setGallery(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return (string) $this->name;
    }

    #[Groups(['gallery:read'])]
    public function getMainPictureUrl(): ?string
    {
        return $this->mainPicture ? '/'.self::UPLOAD_PATH.'/'.$this->mainPicture : null;
    }

    /** Adresse du dessin préparatoire pour l'API (null s'il n'y en a pas : pas de curseur) */
    #[Groups(['gallery:read'])]
    public function getSketchUrl(): ?string
    {
        return $this->sketch ? '/'.self::UPLOAD_PATH.'/'.$this->sketch : null;
    }

    /** Adresse du timelapse pour l'API (null s'il n'y en a pas : ni badge, ni vidéo) */
    #[Groups(['gallery:read'])]
    public function getTimelapseUrl(): ?string
    {
        return $this->timelapse ? '/'.self::UPLOAD_PATH.'/'.$this->timelapse : null;
    }

    public function getTimelapse(): ?string
    {
        return $this->timelapse;
    }

    public function setTimelapse(?string $timelapse): static
    {
        $this->timelapse = $timelapse;

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

    public function getSketch(): ?string
    {
        return $this->sketch;
    }

    public function setSketch(?string $sketch): static
    {
        $this->sketch = $sketch;

        return $this;
    }

    // Le getter s'appelle isFavorite() : sans SerializedName, le JSON l'exposerait sous le nom "favorite"
    #[Groups(['gallery:read'])]
    #[SerializedName('isFavorite')]
    public function isFavorite(): bool
    {
        return $this->isFavorite;
    }

    public function setIsFavorite(?bool $isFavorite): static
    {
        $this->isFavorite = (bool) $isFavorite;

        return $this;
    }
}

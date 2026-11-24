<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

/**
 * Un des derniers posts Instagram du salon, affiché en bas de l'accueil.
 *
 * Ces fiches ne se saisissent pas dans le backoffice : elles sont remplies par la commande
 * app:instagram:sync (tâche planifiée), qui interroge Instagram et copie les images ici,
 * en WebP (le visiteur ne contacte jamais Instagram : pas de cookie, chargement rapide).
 */
#[ApiResource(
    operations: [new GetCollection(order: ['postedAt' => 'DESC'], paginationEnabled: false)],
    normalizationContext: ['groups' => ['instagram:read']],
)]
#[ORM\Entity]
class InstagramPost
{
    /** Images copiées depuis Instagram, relatif à public/ */
    public const UPLOAD_PATH = 'uploads/instagram';

    #[Groups(['instagram:read'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** Identifiant du post chez Instagram : évite de copier deux fois le même post */
    #[ORM\Column(length: 64, unique: true)]
    private ?string $instagramId = null;

    /** Adresse du post sur Instagram (ouverte au clic) */
    #[Groups(['instagram:read'])]
    #[ORM\Column(length: 255)]
    private ?string $permalink = null;

    #[Groups(['instagram:read'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $caption = null;

    /** IMAGE, VIDEO (reel) ou CAROUSEL_ALBUM (plusieurs photos) */
    #[Groups(['instagram:read'])]
    #[ORM\Column(length: 20)]
    private ?string $mediaType = null;

    /** Nom du fichier image (converti en WebP par ImageWebpListener) */
    #[ORM\Column(length: 255)]
    private ?string $image = null;

    #[Groups(['instagram:read'])]
    #[ORM\Column]
    private ?\DateTimeImmutable $postedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInstagramId(): ?string
    {
        return $this->instagramId;
    }

    public function setInstagramId(string $instagramId): static
    {
        $this->instagramId = $instagramId;

        return $this;
    }

    public function getPermalink(): ?string
    {
        return $this->permalink;
    }

    public function setPermalink(string $permalink): static
    {
        $this->permalink = $permalink;

        return $this;
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

    public function getMediaType(): ?string
    {
        return $this->mediaType;
    }

    public function setMediaType(string $mediaType): static
    {
        $this->mediaType = $mediaType;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getPostedAt(): ?\DateTimeImmutable
    {
        return $this->postedAt;
    }

    public function setPostedAt(\DateTimeImmutable $postedAt): static
    {
        $this->postedAt = $postedAt;

        return $this;
    }

    #[Groups(['instagram:read'])]
    public function getImageUrl(): ?string
    {
        return $this->image ? '/'.self::UPLOAD_PATH.'/'.$this->image : null;
    }
}

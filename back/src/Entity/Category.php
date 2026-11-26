<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [new Get(), new GetCollection(order: ['name' => 'ASC'])],
    normalizationContext: ['groups' => ['category:read']],
)]
#[ORM\Entity(repositoryClass: CategoryRepository::class)]
// Le slug est calculé à partir du nom : deux noms qui donnent le même slug sont refusés
#[UniqueEntity(fields: ['slug'], message: 'Une catégorie porte déjà ce nom (ou un nom très proche).', errorPath: 'name')]
class Category
{
    #[Groups(['category:read', 'gallery:read'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['category:read', 'gallery:read'])]
    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    private ?string $name = null;

    /**
     * @var Collection<int, Gallery>
     */
    // EXTRA_LAZY : count() fait un SELECT COUNT sans charger les réalisations (voir getGalleryCount())
    #[ORM\OneToMany(targetEntity: Gallery::class, mappedBy: 'category', fetch: 'EXTRA_LAZY')]
    private Collection $galleries;

    /** Dans l'URL du site à la place de l'id (/realisations?category=fine-line) ; calculé dans setName() */
    #[Groups(['category:read', 'gallery:read'])]
    #[ORM\Column(length: 120, unique: true)]
    private ?string $slug = null;

    public function __construct()
    {
        $this->galleries = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Le front n'affiche que les styles qui ont au moins une réalisation.
     * Champ calculé : un getter avec un groupe de sérialisation suffit, pas de colonne en base.
     */
    #[Groups(['category:read'])]
    public function getGalleryCount(): int
    {
        return $this->galleries->count();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;
        // AsciiSlugger : retire les accents, remplace espaces et ponctuation par des tirets
        $this->slug = $name ? (new AsciiSlugger('fr'))->slug($name)->lower()->toString() : null;

        return $this;
    }

    /**
     * @return Collection<int, Gallery>
     */
    public function getGalleries(): Collection
    {
        return $this->galleries;
    }

    public function addGallery(Gallery $gallery): static
    {
        if (!$this->galleries->contains($gallery)) {
            $this->galleries->add($gallery);
            $gallery->setCategory($this);
        }

        return $this;
    }

    public function removeGallery(Gallery $gallery): static
    {
        if ($this->galleries->removeElement($gallery)) {
            if ($gallery->getCategory() === $this) {
                $gallery->setCategory(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return (string) $this->name;
    }

    // Pas de setSlug() : le slug suit toujours le nom (voir setName())
    public function getSlug(): ?string
    {
        return $this->slug;
    }
}

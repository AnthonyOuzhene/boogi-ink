<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ApiResource(
    operations: [
        // Profil de l'utilisateur connecté (GET /api/me) : voir AccountController
        // GET /api/users/{id} : seulement son propre compte (ou un admin)
        new Get(security: "is_granted('ROLE_ADMIN') or object == user"),
        // Inscription (POST /api/users) : voir AccountController
    ],
    normalizationContext: ['groups' => ['user:read']],
)]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'Un compte existe déjà avec cet email.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[Groups(['user:read'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['user:read'])]
    #[ORM\Column(length: 180)]
    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[Groups(['user:read'])]
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    /**
     * Mot de passe en clair saisi dans le backoffice.
     * Pas de #[ORM\Column] : cette propriété n'est JAMAIS enregistrée en base,
     * elle sert seulement à transporter la saisie jusqu'au hachage.
     */
    private ?string $plainPassword = null;

    #[Groups(['comment:read', 'user:read'])]
    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private ?string $name = null;

    /**
     * @var Collection<int, Comment>
     */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'author', orphanRemoval: true)]
    private Collection $comments;

    /** Date d'inscription, remplie automatiquement à la création (voir le constructeur) */
    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * Flashs mis en favori par le client (cœur sur le site, section "Mes favoris").
     * Relation à sens unique : un flash n'a pas besoin de connaître ses fans.
     * Table de liaison user_favorite_flash : supprimer un flash ou un compte
     * supprime aussi ses lignes de favoris (ON DELETE CASCADE, ajouté par Doctrine).
     *
     * @var Collection<int, Flash>
     */
    #[ORM\ManyToMany(targetEntity: Flash::class)]
    #[ORM\JoinTable(name: 'user_favorite_flash')]
    private Collection $favoriteFlashes;
    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->favoriteFlashes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0" . self::class . "\0password"] = hash('crc32c', $this->password);
        
        return $data;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): static
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    public function __toString(): string
    {
        return (string) $this->name;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
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

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setAuthor($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getAuthor() === $this) {
                $comment->setAuthor(null);
            }
        }

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection<int, Flash>
     */
    public function getFavoriteFlashes(): Collection
    {
        return $this->favoriteFlashes;
    }

    /**
     * Numéros (id) des flashs favoris, renvoyés par GET /api/me : le site sait ainsi
     * quels cœurs afficher pleins, sans charger tous les flashs.
     *
     * @return list<int>
     */
    #[Groups(['user:read'])]
    public function getFavoriteFlashIds(): array
    {
        return $this->favoriteFlashes->map(fn (Flash $flash) => $flash->getId())->getValues();
    }

    public function addFavoriteFlash(Flash $favoriteFlash): static
    {
        if (!$this->favoriteFlashes->contains($favoriteFlash)) {
            $this->favoriteFlashes->add($favoriteFlash);
        }

        return $this;
    }

    public function removeFavoriteFlash(Flash $favoriteFlash): static
    {
        $this->favoriteFlashes->removeElement($favoriteFlash);

        return $this;
    }
}

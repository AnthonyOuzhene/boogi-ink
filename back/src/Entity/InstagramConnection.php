<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Une seule fiche : jeton Instagram + état de la dernière synchro (tableau de bord du backoffice).
 * Jamais exposée par l'API. Jeton à renseigner avec la commande app:instagram:token (saisie masquée).
 */
#[ORM\Entity]
class InstagramConnection
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** Valable 60 jours, renouvelé automatiquement 10 jours avant expiration */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $accessToken = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $tokenExpiresAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $lastSyncAt = null;

    /** Null si la dernière synchronisation a réussi */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $lastError = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function setAccessToken(?string $accessToken): static
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    public function getTokenExpiresAt(): ?\DateTimeImmutable
    {
        return $this->tokenExpiresAt;
    }

    public function setTokenExpiresAt(?\DateTimeImmutable $tokenExpiresAt): static
    {
        $this->tokenExpiresAt = $tokenExpiresAt;

        return $this;
    }

    public function getLastSyncAt(): ?\DateTimeImmutable
    {
        return $this->lastSyncAt;
    }

    public function setLastSyncAt(?\DateTimeImmutable $lastSyncAt): static
    {
        $this->lastSyncAt = $lastSyncAt;

        return $this;
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    public function setLastError(?string $lastError): static
    {
        $this->lastError = $lastError;

        return $this;
    }

    public function isConnected(): bool
    {
        return null !== $this->accessToken;
    }

    /** Moins de 7 jours alors que le renouvellement est prévu à 10 : il a échoué (alerte du tableau de bord) */
    public function isTokenExpiringSoon(): bool
    {
        return null !== $this->tokenExpiresAt && $this->tokenExpiresAt < new \DateTimeImmutable('+7 days');
    }
}

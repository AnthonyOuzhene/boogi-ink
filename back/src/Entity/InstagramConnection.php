<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Connexion au compte Instagram du salon (une seule fiche) : la clé d'accès (jeton) et l'état
 * de la dernière synchronisation, affiché sur le tableau de bord du backoffice.
 * Jamais exposée par l'API. Le jeton se renseigne avec : app:instagram:token "<jeton>".
 */
#[ORM\Entity]
class InstagramConnection
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** Jeton "longue durée" d'Instagram : valable 60 jours, renouvelé automatiquement avant */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $accessToken = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $tokenExpiresAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $lastSyncAt = null;

    /** Message de la dernière erreur (null si la dernière synchronisation a réussi) */
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

    /** Jeton expiré ou qui expire dans moins de 7 jours (le renouvellement automatique a échoué) */
    public function isTokenExpiringSoon(): bool
    {
        return null !== $this->tokenExpiresAt && $this->tokenExpiresAt < new \DateTimeImmutable('+7 days');
    }
}

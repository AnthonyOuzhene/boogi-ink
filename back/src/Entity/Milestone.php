<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\ExactFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\QueryParameter;
use App\Repository\MilestoneRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Date marquante d'une frise de L'atelier : l'histoire du salon ou le parcours de l'artiste.
 * GET /api/milestones?timeline=salon (ou artist)
 */
#[ApiResource(
    operations: [
        new GetCollection(
            order: ['position' => 'ASC', 'id' => 'ASC'],
            parameters: ['timeline' => new QueryParameter(filter: new ExactFilter(), property: 'timeline')],
        ),
    ],
    normalizationContext: ['groups' => ['milestone:read']],
    paginationEnabled: false,
)]
#[ORM\Entity(repositoryClass: MilestoneRepository::class)]
class Milestone
{
    public const TIMELINE_SALON = 'salon';
    public const TIMELINE_ARTIST = 'artist';

    /** Libellés affichés dans le backoffice */
    public const TIMELINES = [
        'Histoire du salon' => self::TIMELINE_SALON,
        "Parcours de l'artiste" => self::TIMELINE_ARTIST,
    ];

    #[Groups(['milestone:read'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** Frise à laquelle appartient la date : "salon" ou "artist" */
    #[Groups(['milestone:read'])]
    #[ORM\Column(length: 20)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: [self::TIMELINE_SALON, self::TIMELINE_ARTIST])]
    private ?string $timeline = self::TIMELINE_SALON;

    /** Texte libre : "2012", "2015 – 2018", "Aujourd'hui"… */
    #[Groups(['milestone:read'])]
    #[ORM\Column(length: 30)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 30)]
    private ?string $year = null;

    #[Groups(['milestone:read'])]
    #[ORM\Column(length: 150)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 150)]
    private ?string $title = null;

    #[Groups(['milestone:read'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private int $position = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTimeline(): ?string
    {
        return $this->timeline;
    }

    public function setTimeline(?string $timeline): static
    {
        $this->timeline = $timeline;

        return $this;
    }

    public function getYear(): ?string
    {
        return $this->year;
    }

    public function setYear(?string $year): static
    {
        $this->year = $year;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

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
        return $this->year.' — '.$this->title;
    }
}

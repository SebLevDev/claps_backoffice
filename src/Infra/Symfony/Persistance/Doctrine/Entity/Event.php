<?php

declare(strict_types=1);

namespace Infra\Symfony\Persistance\Doctrine\Entity;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Domain\Event\Enum\EventTypeEnum;
use Infra\Symfony\Persistance\Doctrine\Repository\EventRepository;

#[ApiResource]
#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event implements \Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private $id;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $name;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $venue;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private $date;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $isHighlight = false;

    #[ORM\Column(type: Types::STRING, enumType: EventTypeEnum::class, nullable: true)]
    private ?EventTypeEnum $type = null;

    #[ORM\OneToMany(targetEntity: Video::class, mappedBy: 'event', cascade: ['persist'])]
    private $videos;

    public function __construct()
    {
        $this->videos = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getName() ?? '';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getIsHighlight(): ?bool
    {
        return $this->isHighlight;
    }

    public function setIsHighlight(bool $isHighlight): self
    {
        $this->isHighlight = $isHighlight;

        return $this;
    }

    public function getVenue(): ?string
    {
        return $this->venue;
    }

    public function setVenue(?string $venue): self
    {
        $this->venue = $venue;

        return $this;
    }

    public function getType(): ?EventTypeEnum
    {
        return $this->type;
    }

    public function setType(?EventTypeEnum $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return Collection|Video[]
     */
    public function getVideos(): Collection
    {
        return $this->videos;
    }

    public function addVideo(Video $video): self
    {
        if (!$this->videos->contains($video)) {
            $this->videos[] = $video;
            $video->setEvent($this);
        }

        return $this;
    }

    public function removeVideo(Video $video): self
    {
        if ($this->videos->contains($video)) {
            $this->videos->removeElement($video);
            // set the owning side to null (unless already changed)
            if ($video->getEvent() === $this) {
                $video->setEvent(null);
            }
        }

        return $this;
    }
}

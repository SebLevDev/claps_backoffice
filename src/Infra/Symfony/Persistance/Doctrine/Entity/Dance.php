<?php

declare(strict_types=1);

namespace Infra\Symfony\Persistance\Doctrine\Entity;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Domain\Video\Enum\VideoTagEnum;
use Infra\Symfony\Persistance\Doctrine\Repository\DanceRepository;
use Symfony\Component\String\Slugger\AsciiSlugger;

#[ApiResource]
#[ORM\Entity(repositoryClass: DanceRepository::class)]
class Dance implements \Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private $id;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $name;

    #[ORM\Column(type: Types::STRING, length: 5, nullable: true)]
    private ?string $country = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $region = null;

    #[ORM\Column(type: Types::TEXT, length: 4000, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToMany(targetEntity: Video::class, inversedBy: 'dances')]
    private $videos;

    public function __construct()
    {
        $this->videos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function __toString(): string
    {
        $string = '';

        if ($this->name) {
            $string = $this->name;
        }

        return $string;
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

    /**
     * Slug cosmétique pour les URLs, non persisté : l'identification se fait par l'id.
     */
    public function getSlug(): string
    {
        $slug = (new AsciiSlugger())->slug((string) $this->name)->lower()->toString();

        return $slug !== '' ? $slug : 'dance';
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(?string $region): self
    {
        $this->region = $region;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getVideos()
    {
        return $this->videos;
    }

    public function getVideosByType(VideoTagEnum $type)
    {
        return $this->videos->filter(function (Video $video) use ($type) {
            return $video->getTag() === $type;
        });
    }

    public function getVideosOfWorkShop()
    {
        return $this->getVideosByType(VideoTagEnum::Workshop);
    }

    public function getVideosOfPerformance()
    {
        return $this->getVideosByType(VideoTagEnum::Show);
    }

    public function addVideo(Video $video)
    {
        if (!$this->videos->contains($video)) {
            $this->videos[] = $video;
            $video->addDance($this);
        }
    }

    public function removeVideo(Video $video)
    {
        if ($this->videos->contains($video)) {
            $this->videos->removeElement($video);
            $video->removeDance($this);
        }
    }
}

<?php

declare(strict_types=1);

namespace Infra\Symfony\Persistance\Doctrine\Entity;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Domain\Video\Enum\VideoTagEnum;
use Infra\Symfony\Persistance\Doctrine\Repository\VideoRepository;
use Symfony\Component\String\Slugger\AsciiSlugger;

#[ApiResource]
#[ORM\Entity(repositoryClass: VideoRepository::class)]
class Video implements \Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private $id;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $name;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $url;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $recordDate = null;

    #[ORM\Column(type: Types::STRING, length: 5, nullable: true)]
    private ?string $country = null;

    #[ORM\ManyToMany(targetEntity: Section::class, inversedBy: 'videos')]
    private $sections;

    #[ORM\OneToMany(targetEntity: PlaylistVideo::class, mappedBy: 'video', orphanRemoval: true)]
    private $playlistVideos;

    #[ORM\ManyToOne(targetEntity: Event::class, inversedBy: 'video', cascade: ['persist'])]
    private $event;

    #[ORM\Column(type: Types::TEXT, length: 4000, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::STRING, enumType: VideoTagEnum::class, nullable: true)]
    private ?VideoTagEnum $tag;

    #[ORM\ManyToMany(targetEntity: Dance::class, mappedBy: 'videos')]
    private $dances;

    public function __construct()
    {
        $this->sections = new ArrayCollection();
        $this->dances = new ArrayCollection();
        $this->playlistVideos = new ArrayCollection();
        $this->tag = VideoTagEnum::Show;
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

    /**
     * Slug cosmétique pour les URLs, non persisté : l'identification se fait par l'id.
     */
    public function getSlug(): string
    {
        $slug = (new AsciiSlugger())->slug((string) $this->name)->lower()->toString();

        return $slug !== '' ? $slug : 'video';
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getRecordDate(): ?\DateTimeInterface
    {
        return $this->recordDate;
    }

    public function setRecordDate(?\DateTimeInterface $recordDate): self
    {
        $this->recordDate = $recordDate;

        return $this;
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

    /**
     * @return Collection|Section[]
     */
    public function getSections(): Collection
    {
        return $this->sections;
    }

    public function addSection(Section $section): self
    {
        if (!$this->sections->contains($section)) {
            $this->sections[] = $section;
        }

        return $this;
    }

    public function removeSection(Section $section): self
    {
        if ($this->sections->contains($section)) {
            $this->sections->removeElement($section);
        }

        return $this;
    }

    /**
     * @return Collection|PlaylistVideo[]
     */
    public function getPlaylistVideos(): Collection
    {
        return $this->playlistVideos;
    }

    public function addPlaylistVideo(PlaylistVideo $playlistVideo): self
    {
        if (!$this->playlistVideos->contains($playlistVideo)) {
            $this->playlistVideos[] = $playlistVideo;
            $playlistVideo->setVideo($this);
        }

        return $this;
    }

    public function removePlaylistVideo(PlaylistVideo $playlistVideo): self
    {
        if ($this->playlistVideos->contains($playlistVideo)) {
            $this->playlistVideos->removeElement($playlistVideo);
            // set the owning side to null (unless already changed)
            if ($playlistVideo->getVideo() === $this) {
                $playlistVideo->setVideo(null);
            }
        }

        return $this;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(Event $event): self
    {
        $this->event = $event;

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

    public function getTag(): ?VideoTagEnum
    {
        return $this->tag;
    }

    public function setTag(?VideoTagEnum $tag): self
    {
        $this->tag = $tag;

        return $this;
    }

    public function getDances()
    {
        return $this->dances;
    }

    public function addDance(Dance $dance)
    {
        if (!$this->dances->contains($dance)) {
            $this->dances[] = $dance;
            $dance->addVideo($this);
        }
    }

    public function removeDance(Dance $dance)
    {
        if ($this->dances->contains($dance)) {
            $this->dances->removeElement($dance);
            $dance->removeVideo($this);
        }
    }

    public function getYoutubeId()
    {
        return $this->getYoutubeViedeoId($this->getUrl());
    }

    private function getYoutubeViedeoId($youtube_video_url) {
        /**
         * Pattern matches
         * http://youtu.be/ID
         * http://www.youtube.com/embed/ID
         * http://www.youtube.com/watch?v=ID
         * http://www.youtube.com/?v=ID
         * http://www.youtube.com/v/ID
         * http://www.youtube.com/e/ID
         * http://www.youtube.com/user/username#p/u/11/ID
         * http://www.youtube.com/leogopal#p/c/playlistID/0/ID
         * http://www.youtube.com/watch?feature=player_embedded&v=ID
         * http://www.youtube.com/?feature=player_embedded&v=ID
         */
        $pattern = '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i';

        // Checks if it matches a pattern and returns the value
        if (preg_match($pattern, (string) $youtube_video_url, $match)) {
            return $match[1];
        }

        // if no match return false.
        return false;
    }
}

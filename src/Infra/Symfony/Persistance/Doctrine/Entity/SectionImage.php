<?php

declare(strict_types=1);

namespace Infra\Symfony\Persistance\Doctrine\Entity;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Infra\Symfony\Persistance\Doctrine\Repository\SectionImageRepository;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Attribute as Vich;

#[ApiResource]
#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: SectionImageRepository::class)]
class SectionImage implements \Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private $id;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $image = null;

    #[Vich\UploadableField(mapping: 'section_images', fileNameProperty: 'image')]
    private ?File $imageFile = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTime $updatedAt;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $position = 0;

    #[ORM\ManyToOne(targetEntity: Section::class, inversedBy: 'images')]
    private ?Section $section = null;

    public function __construct()
    {
        $this->updatedAt = new \DateTime('now');
    }

    public function __toString(): string
    {
        return (string) $this->image;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setImageFile(?File $imageFile = null): self
    {
        $this->imageFile = $imageFile;

        // VERY IMPORTANT:
        // It is required that at least one field changes if you are using Doctrine,
        // otherwise the event listeners won't be called and the file is lost
        if ($imageFile) {
            $this->updatedAt = new \DateTime('now');
        }

        return $this;
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getSection(): ?Section
    {
        return $this->section;
    }

    public function setSection(?Section $section): self
    {
        $this->section = $section;

        return $this;
    }
}

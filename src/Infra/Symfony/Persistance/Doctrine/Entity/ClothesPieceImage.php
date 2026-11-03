<?php

declare(strict_types=1);

namespace Infra\Symfony\Persistance\Doctrine\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Attribute as Vich;

#[Vich\Uploadable]
#[ORM\Entity]
class ClothesPieceImage implements \Stringable
{
    /** Doit correspondre au paramètre app.path.clothespiece_images (uri_prefix du mapping Vich). */
    public final const UPLOAD_PATH = '/uploads/images/clothespieces';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private $id;

    #[ORM\ManyToOne(targetEntity: ClothesPiece::class, inversedBy: 'images')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?ClothesPiece $piece = null;

    /** Nom du fichier uploadé, ou chemin complet pour les images reprises de WordPress (/uploads/clothes/pieces/...). */
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $image = null;

    #[Vich\UploadableField(mapping: 'clothespiece_images', fileNameProperty: 'image')]
    private ?File $imageFile = null;

    #[ORM\Column(type: Types::INTEGER)]
    private int $position = 0;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTime $updatedAt;

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

    public function getPiece(): ?ClothesPiece
    {
        return $this->piece;
    }

    public function setPiece(?ClothesPiece $piece): self
    {
        $this->piece = $piece;

        return $this;
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

    /** URL publique de l'image, quelle que soit son origine (upload ou reprise WordPress). */
    public function getPath(): ?string
    {
        if (null === $this->image || '' === $this->image) {
            return null;
        }

        return str_starts_with($this->image, '/') ? $this->image : self::UPLOAD_PATH.'/'.$this->image;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(?int $position): self
    {
        $this->position = (int) $position;

        return $this;
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
}

<?php

declare(strict_types=1);

namespace Infra\Symfony\Persistance\Doctrine\Entity;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Domain\Reference\Enum\ReferenceTypeEnum;
use Infra\Symfony\Persistance\Doctrine\Repository\ReferenceRepository;

#[ApiResource]
#[ORM\Entity(repositoryClass: ReferenceRepository::class)]
class Reference implements \Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private $id;

    #[ORM\Column(type: Types::STRING, length: 30, enumType: ReferenceTypeEnum::class, nullable: true)]
    private ?ReferenceTypeEnum $type = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $name;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $city = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $country = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $year = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $lat = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $lng = null;

    #[ORM\Column(type: Types::TEXT, length: 2000, nullable: true)]
    private ?string $description = null;

    public function __construct()
    {
        $this->name = '';
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?ReferenceTypeEnum
    {
        return $this->type;
    }

    public function setType(?ReferenceTypeEnum $type): self
    {
        $this->type = $type;

        return $this;
    }

    /** Icône dérivée du type — plus de champ dédié en base. */
    public function getIcon(): ?string
    {
        return $this->type?->getIcon();
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

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

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

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(?int $year): self
    {
        $this->year = $year;

        return $this;
    }

    public function getLat(): ?float
    {
        return $this->lat;
    }

    public function setLat(?float $lat): self
    {
        $this->lat = $lat;

        return $this;
    }

    public function getLng(): ?float
    {
        return $this->lng;
    }

    public function setLng(?float $lng): self
    {
        $this->lng = $lng;

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
}

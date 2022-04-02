<?php

declare(strict_types=1);

namespace Infra\Symfony\Persistance\Doctrine\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="Infra\Symfony\Persistance\Doctrine\Repository\ClothesTypeTypeRepository")
 */
class ClothesTypeType implements \Stringable
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="Infra\Symfony\Persistance\Doctrine\Entity\ClothesType", mappedBy="type")
     */
    private $clothesTypes;

    public function __construct()
    {
        $this->clothesTypes = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->name;
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
     * @return Collection|ClothesType[]
     */
    public function getClothesTypes(): Collection
    {
        return $this->clothesTypes;
    }

    public function addClothesType(ClothesType $clothesType): self
    {
        if (!$this->clothesTypes->contains($clothesType)) {
            $this->clothesTypes[] = $clothesType;
            $clothesType->setType($this);
        }

        return $this;
    }

    public function removeClothesType(ClothesType $clothesType): self
    {
        if ($this->clothesTypes->contains($clothesType)) {
            $this->clothesTypes->removeElement($clothesType);
            // set the owning side to null (unless already changed)
            if ($clothesType->getType() === $this) {
                $clothesType->setType(null);
            }
        }

        return $this;
    }
}

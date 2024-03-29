<?php

declare(strict_types=1);

namespace Infra\Symfony\Persistance\Doctrine\Entity;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource]
#[ORM\Entity]
class LoginHistory implements \Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private $id;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private $date;

    #[ORM\Column(type: Types::STRING, length: 64)]
    private ?string $clientIp = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'loginHistories', cascade: ['persist'])]
    private $user;

    public function __toString(): string
    {
        return $this->getUser() ." at ". date_format($this->getDate(), 'd/m/y');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): \DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getClientIp(): ?string
    {
        return $this->clientIp;
    }

    public function setClientIp(?string $clientIp = null): self
    {
        $this->clientIp = $clientIp;

        return $this;
    }

}

<?php

declare(strict_types=1);

namespace Domain\Contact;

use Symfony\Component\Validator\Constraints as Assert;

final class RegistrationRequest
{
    #[Assert\NotBlank]
    public ?string $firstName = null;

    #[Assert\NotBlank]
    public ?string $lastName = null;

    public ?\DateTimeInterface $birthDate = null;

    public ?string $section = null;

    #[Assert\NotBlank]
    #[Assert\Email]
    public ?string $email = null;

    public ?string $phone = null;

    public ?string $additionalInfo = null;
}

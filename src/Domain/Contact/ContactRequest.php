<?php

declare(strict_types=1);

namespace Domain\Contact;

use Symfony\Component\Validator\Constraints as Assert;

final class ContactRequest
{
    #[Assert\NotBlank]
    public ?string $fullName = null;

    #[Assert\NotBlank]
    #[Assert\Email]
    public ?string $email = null;

    #[Assert\NotBlank]
    public ?string $subject = null;

    #[Assert\NotBlank]
    public ?string $message = null;
}

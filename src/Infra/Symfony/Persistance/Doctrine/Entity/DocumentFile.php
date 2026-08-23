<?php

declare(strict_types=1);

namespace Infra\Symfony\Persistance\Doctrine\Entity;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Infra\Symfony\Persistance\Doctrine\Repository\DocumentFileRepository;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Attribute as Vich;

#[ApiResource]
#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: DocumentFileRepository::class)]
class DocumentFile implements \Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private $id;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $name;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTime $updatedAt;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $document = null;

    #[Vich\UploadableField(mapping: 'documents', fileNameProperty: 'document')]
    private ?File $documentFile = null;

    #[ORM\ManyToOne(targetEntity: DocumentCategory::class, inversedBy: 'documentFiles')]
    private $documentCategory;

    public function __construct()
    {
        $this->updatedAt = new \DateTime('now');
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

    public function getDocumentCategory(): ?DocumentCategory
    {
        return $this->documentCategory;
    }

    public function setDocumentCategory(?DocumentCategory $documentCategory): self
    {
        $this->documentCategory = $documentCategory;

        return $this;
    }

    public function setDocumentFile(File $documentFile = null)
    {
        $this->documentFile = $documentFile;

        // VERY IMPORTANT:
        // It is required that at least one field changes if you are using Doctrine,
        // otherwise the event listeners won't be called and the file is lost
        if ($documentFile) {
            // if 'updatedAt' is not defined in your entity, use another property
            $this->updatedAt = new \DateTime('now');
        }
    }

    public function getDocumentFile()
    {
        return $this->documentFile;
    }

    public function setDocument($document)
    {
        $this->document = $document;
    }

    public function getDocument()
    {
        return $this->document;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): DocumentFile
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

}

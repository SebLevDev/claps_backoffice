<?php

declare(strict_types=1);

namespace Infra\Symfony\Service;

use Infra\Symfony\Persistance\Doctrine\Entity\Reference;
use Infra\Symfony\Persistance\Doctrine\Repository\ReferenceRepository;

class ReferencesDataProvider
{
    public function __construct(private readonly ReferenceRepository $referenceRepository) {}

    /** @return array<int, array<string, mixed>> */
    public function getReferences(): array
    {
        return array_map($this->toArray(...), $this->referenceRepository->findAllOrdered());
    }

    /** @return array<string, mixed> */
    private function toArray(Reference $reference): array
    {
        return [
            'icon'    => $reference->getIcon()?->value,
            'name'    => $reference->getName(),
            'city'    => $reference->getCity(),
            'country' => $reference->getCountry(),
            'year'    => $reference->getYear(),
            'lat'     => $reference->getLat(),
            'lng'     => $reference->getLng(),
            'desc'    => $reference->getDescription(),
        ];
    }
}

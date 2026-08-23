<?php

declare(strict_types=1);

namespace Infra\Symfony\Service;

class AgendaDataProvider
{
    /** @return array<int, array<string, mixed>> */
    public function getEvents(): array
    {
        return [
            [
                'date'  => new \DateTimeImmutable('2025-02-08'),
                'title' => 'Gala annuel Look & Dance',
                'lieu'  => 'Centre Destelheide, Dworp',
                'type'  => 'spectacle',
            ],
            [
                'date'  => new \DateTimeImmutable('2025-03-15'),
                'title' => 'Stage de danses bulgares',
                'lieu'  => 'Salle des fêtes, Braine-l\'Alleud',
                'type'  => 'stage',
            ],
            [
                'date'  => new \DateTimeImmutable('2025-04-22'),
                'title' => 'Festival de folklore de Nivelles',
                'lieu'  => 'Grand-Place, Nivelles',
                'type'  => 'spectacle',
            ],
            [
                'date'  => new \DateTimeImmutable('2025-05-10'),
                'title' => 'Voyage en Bretagne',
                'lieu'  => 'Quimper, France',
                'type'  => 'voyage',
            ],
            [
                'date'  => new \DateTimeImmutable('2025-06-07'),
                'title' => 'Bal folk d\'été',
                'lieu'  => 'Parc communal, Braine-l\'Alleud',
                'type'  => 'cours',
            ],
            [
                'date'  => new \DateTimeImmutable('2025-10-19'),
                'title' => 'Stage de danses israéliennes',
                'lieu'  => 'Local du groupe',
                'type'  => 'stage',
            ],
        ];
    }

    public function getTypes(): array
    {
        return ['spectacle', 'stage', 'voyage', 'cours'];
    }
}
<?php

declare(strict_types=1);

namespace Infra\Symfony\Service;

class VoyagesDataProvider
{
    /** @return array<int, array<string, string>> */
    public function getVoyages(): array
    {
        return [
            [
                'img'   => '/images/clapsabots/festivals-hero.jpg',
                'pays'  => 'Hongrie',
                'annee' => '2010',
                'desc'  => 'Sárvár — 5e participation au Festival International de Folklore (IOV).',
            ],
            [
                'img'   => '/images/clapsabots/echanges-polynesie.jpg',
                'pays'  => 'Grèce',
                'annee' => '2007',
                'desc'  => 'Aigion — festival de folklore.',
            ],
            [
                'img'   => '/images/clapsabots/g-leaping.jpg',
                'pays'  => 'Tchéquie',
                'annee' => '2006',
                'desc'  => 'Šlapanice — participation au Festival International de Folklore (IOV).',
            ],
            [
                'img'   => '/images/clapsabots/ensemble-swirl.jpg',
                'pays'  => 'Bulgarie',
                'annee' => '1998',
                'desc'  => 'Pazardjik, Panagurichté, Vélingrad, Saint-Constantin, Calougérovo — tournée à l\'invitation de l\'Ensemble Chavdar de Pazardjik.',
            ],
            [
                'img'   => '/images/clapsabots/danse-energie.jpg',
                'pays'  => 'France',
                'annee' => '1995',
                'desc'  => 'Draguignan — échange culturel avec le groupe local.',
            ],
        ];
    }
}
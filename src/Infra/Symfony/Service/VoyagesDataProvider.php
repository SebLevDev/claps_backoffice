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
                'img'   => 'https://www.clapsabots.be/wp-content/uploads/2016/10/23_Clapsabots_14_000_Bulgarie-1024x621.jpg',
                'pays'  => 'Bulgarie',
                'annee' => '2024',
                'desc'  => 'Festival international de Plovdiv — 3e participation du groupe.',
            ],
            [
                'img'   => 'https://www.clapsabots.be/wp-content/uploads/2016/10/23_Clapsabots_12_002_Angleterre-400x400.jpg',
                'pays'  => 'Écosse',
                'annee' => '2023',
                'desc'  => 'Festival de Stirling — échange avec le groupe Thistle Dancers.',
            ],
            [
                'img'   => 'https://www.clapsabots.be/wp-content/uploads/2016/10/23_Clapsabots_14_010_Bulgarie-400x400.jpg',
                'pays'  => 'Allemagne',
                'annee' => '2022',
                'desc'  => 'Festival de Wangen im Allgäu — 120 groupes participants.',
            ],
            [
                'img'   => 'https://www.clapsabots.be/wp-content/uploads/2016/03/23_Clapsabots_03_006_Israel-400x400.jpg',
                'pays'  => 'Israël',
                'annee' => '2019',
                'desc'  => 'Voyage culturel et stage avec des groupes locaux à Jérusalem.',
            ],
        ];
    }
}
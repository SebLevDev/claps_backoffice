<?php

declare(strict_types=1);

namespace Infra\Symfony\Service;

class StagesDataProvider
{
    /** @return array<int, array<string, string>> */
    public function getStages(): array
    {
        return [
            ['flag' => '🇧🇬', 'pays' => 'Bulgarie',  'annee' => '2024', 'choreg' => 'Ivan Petrov',     'style' => 'Danses de Thrace et de Rhodopes'],
            ['flag' => '🇦🇲', 'pays' => 'Arménie',   'annee' => '2023', 'choreg' => 'Anahit Sargsyan', 'style' => 'Danses de fête arméniennes'],
            ['flag' => '🇮🇱', 'pays' => 'Israël',    'annee' => '2023', 'choreg' => 'Rachel Cohen',    'style' => 'Danses folkloriques israéliennes'],
            ['flag' => '🇳🇴', 'pays' => 'Norvège',   'annee' => '2022', 'choreg' => 'Erik Halvorsen',  'style' => 'Springleik et Halling'],
            ['flag' => '🇭🇺', 'pays' => 'Hongrie',   'annee' => '2022', 'choreg' => 'László Tóth',     'style' => 'Danses du Kalotaszeg'],
        ];
    }
}
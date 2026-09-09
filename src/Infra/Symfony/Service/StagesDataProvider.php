<?php

declare(strict_types=1);

namespace Infra\Symfony\Service;

class StagesDataProvider
{
    /** @return array<int, array<string, string>> */
    public function getStages(): array
    {
        return [
            ['flag' => '🇺🇦', 'pays' => 'Ukraine',  'annee' => '2024', 'choreg' => 'Riakhovskyi Mykhailo, Volkovych Olena & Maxime Luna Kirschbach (danseurs à l\'ensemble Mazowsze)', 'style' => 'Danses ukrainiennes — Hopak'],
            ['flag' => '🇮🇳', 'pays' => 'Inde',     'annee' => '2023', 'choreg' => 'Khushboo Agarwal',                                                                              'style' => 'Danses indiennes — Bollywood'],
            ['flag' => '🇵🇱', 'pays' => 'Pologne',  'annee' => '2013', 'choreg' => 'Maxime Luna Kirschbach (danseur à l\'ensemble Mazowsze)',                                       'style' => 'Danses polonaises de Lublin'],
            ['flag' => '🇷🇴', 'pays' => 'Roumanie', 'annee' => '2012', 'choreg' => 'Maria & Marius Ursu',                                                                            'style' => 'Danses roumaines de Făgăraș'],
        ];
    }
}
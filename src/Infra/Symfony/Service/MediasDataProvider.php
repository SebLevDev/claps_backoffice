<?php

declare(strict_types=1);

namespace Infra\Symfony\Service;

class MediasDataProvider
{
    /** @return list<string> */
    public function getPhotos(): array
    {
        return [
            'https://www.clapsabots.be/wp-content/uploads/2016/10/23_Clapsabots_14_000_Bulgarie-1024x621.jpg',
            'https://www.clapsabots.be/wp-content/uploads/2015/04/arton1878-300x200.jpg',
            'https://www.clapsabots.be/wp-content/uploads/2024/09/PXL_20240728_102556518-scaled-e1725203092284-1024x706.jpg',
            'https://www.clapsabots.be/wp-content/uploads/2016/10/23_Clapsabots_12_002_Angleterre-400x400.jpg',
            'https://www.clapsabots.be/wp-content/uploads/2016/10/23_Clapsabots_14_010_Bulgarie-400x400.jpg',
            'https://www.clapsabots.be/wp-content/uploads/2016/03/23_Clapsabots_03_006_Israel-400x400.jpg',
            'https://www.clapsabots.be/wp-content/uploads/2016/09/23_Clapsabots_02_012_Belgique-400x400.jpg',
            'https://www.clapsabots.be/wp-content/uploads/2015/05/23_Clapsabots_02_018_Belgique-400x400.jpg',
            'https://www.clapsabots.be/wp-content/uploads/2015/04/23_Clapsabots_11_021_Armenie-scaled-e1682179167681-400x400.jpg',
            'https://www.clapsabots.be/wp-content/uploads/2016/10/23_Clapsabots_14_000_Bulgarie-1024x621.jpg',
            'https://www.clapsabots.be/wp-content/uploads/2016/10/23_Clapsabots_12_002_Angleterre-400x400.jpg',
            'https://www.clapsabots.be/wp-content/uploads/2016/10/23_Clapsabots_14_010_Bulgarie-400x400.jpg',
        ];
    }

    /** @return array<int, array<string, string>> */
    public function getVideos(): array
    {
        return [
            ['titre' => 'Stage Bulgarie 2024 — Danses de Thrace',  'cat' => 'stage',      'duree' => '1h23',  'date' => 'Août 2024'],
            ['titre' => 'Répétition Adultes — Chorégraphie Grèce', 'cat' => 'répétition', 'duree' => '42min', 'date' => 'Oct. 2024'],
            ['titre' => 'Stage Hongrois avec László Tóth',         'cat' => 'stage',      'duree' => '2h05',  'date' => 'Jan. 2024'],
            ['titre' => 'Répétition Jeunes — Arménie',             'cat' => 'répétition', 'duree' => '38min', 'date' => 'Nov. 2024'],
            ['titre' => 'Stage Israélien — Niveau avancé',         'cat' => 'stage',      'duree' => '1h44',  'date' => 'Oct. 2023'],
            ['titre' => 'Gala 2024 — Filmage complet',             'cat' => 'gala',       'duree' => '2h31',  'date' => 'Fév. 2024'],
        ];
    }
}
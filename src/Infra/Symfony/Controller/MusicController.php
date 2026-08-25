<?php

declare(strict_types=1);

namespace Infra\Symfony\Controller;

use Infra\Symfony\Persistance\Doctrine\Entity\Video;
use Infra\Symfony\Form\Type\SearchVideoType;
use Infra\Symfony\Persistance\Doctrine\Repository\VideoRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/music')]
class MusicController extends BaseController
{
    #[Route('/', name:'app_music_index')]
    public function indexAction(): Response
    {
        return $this->render('member/music/index.html.twig', [
            'breadcrumb' => $this->getBreadcurmb()
        ]);
    }

    private function getBreadcurmb(): array
    {
        $breadcrumb = [];
        $breadcrumb['items'][] = ['title'=> 'Home', 'url' => '/'];
        $breadcrumb['items'][] = ['title'=> 'Media', 'url' => $this->generateUrl('app_media_index')];
        $breadcrumb['items'][] = ['title'=> 'Music'];

        return $breadcrumb;
    }

}

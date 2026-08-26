<?php

declare(strict_types=1);

namespace Infra\Symfony\Controller;

use Infra\Symfony\Persistance\Doctrine\Repository\PlaylistRepository;
use Infra\Symfony\Persistance\Doctrine\Repository\VideoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MediaController extends AbstractController
{
    #[Route('/media', name:'app_media_index')]
    public function indexAction(VideoRepository $videoRepository, PlaylistRepository $playlistRepository): Response
    {
        $videos = $videoRepository->findLastVideos();

        return $this->render('member/media/index.html.twig', [
            'videos' => $videos,
            'breadcrumb' => $this->getBreadcurmb()
        ]);
    }

    private function getBreadcurmb(): array
    {
        $breadcrumb = [];
        $breadcrumb['items'][] = ['title' => 'Home', 'url' => '/'];
        $breadcrumb['items'][] = ['title' => 'Media'];

        return $breadcrumb;
    }
}

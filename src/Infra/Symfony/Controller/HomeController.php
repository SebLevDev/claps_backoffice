<?php

declare(strict_types=1);

namespace Infra\Symfony\Controller;

use Infra\Symfony\Persistance\Doctrine\Repository\VideoRepository;
use Infra\Symfony\Service\AgendaDataProvider;
use Infra\Symfony\Service\BlogDataProvider;
use Infra\Symfony\Service\SectionDataProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private readonly SectionDataProvider $sectionProvider,
        private readonly AgendaDataProvider  $agendaProvider,
        private readonly BlogDataProvider    $blogProvider,
    ) {}

    #[Route('/', name: 'app_index')]
    public function indexAction(VideoRepository $videoRepository): Response
    {
        return $this->render('home/index.html.twig', [
            'videos'         => $videoRepository->findLastVideos(8),
            'sections'       => $this->sectionProvider->getSections(),
            'events_preview' => array_slice($this->agendaProvider->getEvents(), 0, 4),
            'blog_preview'   => array_slice($this->blogProvider->getArticles(), 0, 3),
        ]);
    }
}
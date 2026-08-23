<?php

declare(strict_types=1);

namespace Infra\Symfony\Controller;

use Infra\Symfony\Service\MediasDataProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MediasController extends AbstractController
{
    public function __construct(private readonly MediasDataProvider $provider) {}

    #[Route('/medias', name: 'app_medias')]
    public function indexAction(): Response
    {
        return $this->render('medias/index.html.twig', [
            'photos' => $this->provider->getPhotos(),
            'videos' => $this->provider->getVideos(),
        ]);
    }
}
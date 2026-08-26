<?php

declare(strict_types=1);

namespace Infra\Symfony\Controller;

use Infra\Symfony\Persistance\Doctrine\Entity\Dance;
use Infra\Symfony\Persistance\Doctrine\Repository\DanceRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/dance')]
class DanceController extends BaseController
{
    #[Route('/', name:'app_dance_index')]
    public function indexAction(DanceRepository $danceRepository): Response
    {
        $dances = $danceRepository->findBy([], [
            'country' => 'ASC',
            'name' => 'ASC'
        ]);

        return $this->render('member/dance/index.html.twig', [
            'dances' => $dances,
            'breadcrumb' => $this->getBreadcurmb()
        ]);
    }

    #[Route('/{id}', name:'app_dance_show', requirements: ['id' => '\d+'])]
    public function showAction(Dance $dance): Response
    {
        return $this->render('member/dance/show.html.twig', [
            'dance' => $dance,
            'breadcrumb' => $this->getBreadcurmb()
        ]);
    }

    private function getBreadcurmb(): array
    {
        $breadcrumb = [];
        $breadcrumb['items'][] = ['title'=> 'Home', 'url' => '/'];
        $breadcrumb['items'][] = ['title'=> 'Dance', 'url' => $this->generateUrl('app_dance_index')];
        $breadcrumb['items'][] = ['title'=> 'Dance'];

        return $breadcrumb;
    }
}

<?php

declare(strict_types=1);

namespace Infra\Symfony\Controller;

use Infra\Symfony\Form\Type\SearchDanceType;
use Infra\Symfony\Persistance\Doctrine\Entity\Dance;
use Infra\Symfony\Persistance\Doctrine\Repository\DanceRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/dance')]
class DanceController extends BaseController
{
    private const int DEFAULT_LIMIT = 25;

    #[Route('/', name:'app_dance_index')]
    public function indexAction(DanceRepository $danceRepository): Response
    {
        $countries = $danceRepository->getCountryList();
        $form = $this->createForm(SearchDanceType::class, null, [
            'countries' => $countries
        ]);

        $params = $this->getSqlParameterBag();
        if (!$params->getLimit()) {
            $params->setLimit(self::DEFAULT_LIMIT);
        }

        $dances = $danceRepository->filterAll($params);
        $total = $danceRepository->countAll($params);

        return $this->render('member/dance/index.html.twig', [
            'dances' => $dances,
            'searchDanceForm' => $form->createView(),
            'currentPage' => $params->getPage(),
            'totalPages' => $params->getPageCount($total),
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

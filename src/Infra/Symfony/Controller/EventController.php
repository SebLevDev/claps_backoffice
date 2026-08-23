<?php

declare(strict_types=1);

namespace Infra\Symfony\Controller;

use Infra\Symfony\Form\Type\SearchVideoType;
use Infra\Symfony\Persistance\Doctrine\Repository\EventRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/events')]
class EventController extends BaseController
{
    #[Route('/', name:'app_event_index')]
    public function indexAction(EventRepository $eventRepository): Response
    {
        $events = $eventRepository->findBy([], ['date' => 'DESC']);

        return $this->render('event/index.html.twig', [
            'events' => $events,
            'breadcrumb' => $this->getBreadcurmb()
        ]);
    }


    private function getBreadcurmb(): array
    {
        $breadcrumb = [];
        $breadcrumb['items'][] = ['title'=> 'Home', 'url' => '/'];
        $breadcrumb['items'][] = ['title'=> 'Events', 'url' => $this->generateUrl('app_event_index')];
        $breadcrumb['items'][] = ['title'=> 'Events'];

        return $breadcrumb;
    }

}

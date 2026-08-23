<?php

declare(strict_types=1);

namespace Infra\Symfony\Controller;

use Infra\Symfony\Service\AgendaDataProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AgendaController extends AbstractController
{
    public function __construct(private readonly AgendaDataProvider $provider) {}

    #[Route('/agenda', name: 'app_agenda_list')]
    public function listAction(Request $request): Response
    {
        $currentType = $request->query->get('type', 'tous');
        $all = $this->provider->getEvents();

        $events = $currentType !== 'tous'
            ? array_values(array_filter($all, fn($e) => $e['type'] === $currentType))
            : $all;

        return $this->render('agenda/list.html.twig', [
            'events'      => $events,
            'currentType' => $currentType,
            'types'       => $this->provider->getTypes(),
        ]);
    }
}
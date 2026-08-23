<?php

declare(strict_types=1);

namespace Infra\Symfony\Controller;

use Infra\Symfony\Service\SectionDataProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SectionController extends AbstractController
{
    public function __construct(private readonly SectionDataProvider $provider) {}

    #[Route('/sections', name: 'app_sections_list')]
    public function listAction(): Response
    {
        return $this->render('section/list.html.twig', [
            'sections' => $this->provider->getSections(),
        ]);
    }

    #[Route('/sections/{slug}', name: 'app_sections_show')]
    public function showAction(string $slug): Response
    {
        $section = $this->provider->getSectionBySlug($slug);

        if ($section === null) {
            throw $this->createNotFoundException('Section non trouvée.');
        }

        return $this->render('section/show.html.twig', [
            'section' => $section,
        ]);
    }
}
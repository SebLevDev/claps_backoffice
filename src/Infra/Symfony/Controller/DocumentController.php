<?php

declare(strict_types=1);

namespace Infra\Symfony\Controller;

use Infra\Symfony\Persistance\Doctrine\Repository\DocumentCategoryRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/documents')]
class DocumentController extends BaseController
{
    #[Route('/', name:'app_document_index')]
    public function indexAction(DocumentCategoryRepository $documentCategoryRepository): Response
    {
        $categories = $documentCategoryRepository->findAll();

        return $this->render('document/index.html.twig', [
            'categories' => $categories,
            'breadcrumb' => $this->getBreadcurmb()
        ]);
    }

    private function getBreadcurmb(): array
    {
        $breadcrumb = [];
        $breadcrumb['items'][] = ['title'=> 'Home', 'url' => '/'];
        $breadcrumb['items'][] = ['title'=> 'Media', 'url' => $this->generateUrl('app_media_index')];
        $breadcrumb['items'][] = ['title'=> 'Video'];

        return $breadcrumb;
    }

}

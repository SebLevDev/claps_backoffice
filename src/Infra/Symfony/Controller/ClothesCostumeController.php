<?php

declare(strict_types=1);

namespace Infra\Symfony\Controller;

use Infra\Symfony\Persistance\Doctrine\Repository\ClothesCostumeRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/clothes/costumes')]
class ClothesCostumeController extends BaseController
{

    #[Route('/', name:'app_clothes_costume_index')]
    public function indexAction(ClothesCostumeRepository $clothesCostumeRepository): Response
    {
        $costumes = $clothesCostumeRepository->findAll();

        return $this->render('clothescostume/index.html.twig', [
            'costumes' => $costumes,
            'breadcrumb' => null
        ]);
    }

}

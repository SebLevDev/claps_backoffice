<?php

declare(strict_types=1);

namespace Infra\EasyAdmin\Controller;

use Infra\Symfony\Persistance\Doctrine\Entity\ClothesCostume;
use Infra\Symfony\Persistance\Doctrine\Repository\ClubYearRepository;
use Infra\Symfony\Persistance\Doctrine\Repository\MemberShipRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @Security("is_granted('ROLE_ADMIN')")
 */
class ClothesCostumeDashboardController extends AbstractController
{

    /**
     * @Route("/admin/clothes/costumes/dasboard/{id}", name="admin_clothes_costume_dashboard")
     */
    public function index(ClothesCostume $costume, MemberShipRepository $memberShipRepository, ClubYearRepository $clubYearRepository)
    {
        $clubYear = $clubYearRepository->findCurrentYear();
        $memberShips = $memberShipRepository->findForCostumeAssignation($clubYear, $costume);

        return $this->render('admin/clothes_costume/dashboard.html.twig', [
            'costume' => $costume,
            'memberShips' => $memberShips,
        ]);
    }
}

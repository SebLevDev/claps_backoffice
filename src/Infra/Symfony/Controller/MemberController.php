<?php

declare(strict_types=1);

namespace Infra\Symfony\Controller;

use Infra\Symfony\Form\Type\MemberEditType;
use Infra\Symfony\Persistance\Doctrine\Entity\Member;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/member')]
#[IsGranted('ROLE_USER')]
class MemberController extends BaseController
{
    #[Route('/edit/{id}', name:'app_member_edit', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function edit(Request $request, Member $member): Response
    {
        $form = $this->createForm(MemberEditType::class, $member);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getEntityManager()->flush();

            $this->addFlash('success', 'user.updated_successfully');

            return $this->redirectToRoute('app_user_show');
        }

        return $this->render('member/edit.html.twig', [
            'member' => $member,
            'form' => $form->createView(),
        ]);
    }

}

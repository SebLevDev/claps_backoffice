<?php

declare(strict_types=1);

namespace Infra\Symfony\Controller;

use Infra\Symfony\Form\Type\ChangePasswordType;
use Infra\Symfony\Form\Type\UserEditType;
use Infra\Symfony\Persistance\Doctrine\Entity\Member;
use Infra\Symfony\Persistance\Doctrine\Entity\User;
use Infra\Symfony\Persistance\Doctrine\Repository\MemberFamilyRepository;
use Infra\Symfony\Persistance\Doctrine\Repository\MemberRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[Route('/profile')]
#[IsGranted('ROLE_USER')]
class UserController extends BaseController
{
    #[Route('/', name:'app_user_show', methods: [Request::METHOD_GET])]
    public function show(MemberRepository $memberRepository, MemberFamilyRepository $familyRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $families = [];
        $familyMembers = [];
        $member = $memberRepository->findOneByEmail($user->getEmail());
        if ($member) {
            $families = $member->getFamilies();
            $familyMembers[] = $memberRepository->findOneByEmail($member->getEmail());
        } else {
            $families[] = $familyRepository->findOneByEmail($user->getEmail());
        }

        foreach ($families as $family) {
            $members = $memberRepository->findByFamily($family);
            $familyMembers = array_merge($familyMembers, $members);
        }
        $familyMembers = array_unique($familyMembers);

        return $this->render('user/show.html.twig', [
            'user' => $user,
            'familyMembers' => $familyMembers,
            'families' => $families,
        ]);
    }

    private function getMember(): Member
    {

    }

    #[Route('/edit', name:'user_edit', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function edit(Request $request): Response
    {
        $user = $this->getUser();

        $form = $this->createForm(UserEditType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getEntityManager()->flush();

            $this->addFlash('success', 'user.updated_successfully');

            return $this->redirectToRoute('user_edit');
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/change-password', name:'user_change_password', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function changePassword(Request $request, UserPasswordHasherInterface $hasher): Response
    {
        /** @var PasswordAuthenticatedUserInterface $user */
        $user = $this->getUser();

        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($hasher->hashPassword($user, $form->get('newPassword')->getData()));

            $this->getEntityManager()->flush();

            return $this->redirectToRoute('security_logout');
        }

        return $this->render('user/change_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}

<?php

declare(strict_types=1);

namespace Infra\Symfony\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Domain\Account\User\UseCase\Register\RegisterUserRequest;
use Infra\Symfony\Form\Type\UserRegisterType;
use Infra\Symfony\Persistance\Doctrine\Entity\Member;
use Infra\Symfony\Persistance\Doctrine\Entity\MemberFamily;
use Infra\Symfony\Persistance\Doctrine\Entity\User;
use Infra\Symfony\Persistance\Doctrine\Repository\MemberFamilyRepository;
use Infra\Symfony\Persistance\Doctrine\Repository\MemberRepository;
use Infra\Symfony\Persistance\Doctrine\Repository\UserRepository;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\FormLoginAuthenticator;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class SecurityController extends BaseController
{
    use TargetPathTrait;

    public function __construct(
        RequestStack $requestStack,
        EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $hasher
    ) {
        parent::__construct($requestStack, $entityManager);
    }

    #[Route('/register', name: 'security_register')]
    public function register(
        Request $request,
        UserRepository $userRepository,
        MemberRepository $membreRepository,
        MemberFamilyRepository $familyRepository,
        UserAuthenticatorInterface $userAuthenticator,
        FormLoginAuthenticator $formLoginAuthenticator
    ): Response {
        $registerRequest = new RegisterUserRequest();

        $form = $this->createForm(UserRegisterType::class, $registerRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $userRepository->findOneByEmail($registerRequest->email);
            if ($user) {
                return $this->redirectToRoute('security_login');
            }

            $member = $membreRepository->findOneByEmail($registerRequest->email);
            if ($member) {
                $user = $this->createFromMember($member, $registerRequest);
                $userAuthenticator->authenticateUser($user, $formLoginAuthenticator, $request);

                return $this->redirectToRoute('app_index');
            }
            $family = $familyRepository->findOneByEmail($registerRequest->email);
            if ($family) {
                $user = $this->createFromFamily($family, $registerRequest);
                $userAuthenticator->authenticateUser($user, $formLoginAuthenticator, $request);

                return $this->redirectToRoute('app_index');
            }

            $form->addError(new FormError("Nous ne trouvons pas cette adresse email dans notre liste de membre."));
        }

        return $this->render('security/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function createFromMember(Member $member, RegisterUserRequest $registerRequest): User
    {
        $user = new User();
        $user->setEmail($member->getEmail());
        $user->setFirstName($member->getFirstname());
        $user->setLastName($member->getLastname());
        $user->setPassword($this->hasher->hashPassword($user, $registerRequest->password));
        $user->setRoles(['ROLE_USER']);

        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();

        return $user;
    }

    private function createFromFamily(MemberFamily $family, RegisterUserRequest $registerRequest): User
    {
        $user = new User();
        $user->setEmail($registerRequest->email);
        $user->setLastName($family->getLastname());
        $user->setPassword($this->hasher->hashPassword($user, $registerRequest->password));
        $user->setRoles(['ROLE_USER']);

        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();

        return $user;
    }

    #[Route('/login', name: 'security_login')]
    public function login(
        Request $request,
        #[CurrentUser] ?User $user,
        AuthenticationUtils $helper
    ): Response {
        // if user is already logged in, don't display the login page again
        if ($user) {
            return $this->redirectToRoute('admin_dashboard');
        }

        return $this->render('security/login.html.twig', [
            'error' => $helper->getLastAuthenticationError(),
            'last_username' => $helper->getLastUsername(),
            'page_title' => 'Clap\'Sabots',
            'csrf_token_intention' => 'authenticate',
        ]);
    }

    /**
     * This is the route the user can use to logout.
     *
     * But, this will never be executed. Symfony will intercept this first
     * and handle the logout automatically. See logout in config/packages/security.yaml
     */
    #[Route('/logout', name: 'security_logout')]
    public function logout(): never
    {
        throw new \Exception('This should never be reached!');
    }
}

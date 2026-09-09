<?php

declare(strict_types=1);

namespace Infra\Symfony\Controller;

use Domain\Contact\ContactRequest;
use Domain\Contact\RegistrationRequest;
use Infra\Symfony\Form\Type\ContactRequestType;
use Infra\Symfony\Form\Type\RegistrationRequestType;
use Infra\Symfony\Service\ReferencesDataProvider;
use Infra\Symfony\Service\StagesDataProvider;
use Infra\Symfony\Service\VoyagesDataProvider;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;

class GroupController extends AbstractController
{
    public function __construct(
        private readonly StagesDataProvider     $workshopsProvider,
        private readonly VoyagesDataProvider    $tripsProvider,
        private readonly ReferencesDataProvider $referencesProvider,
    ) {}

    #[Route('/history', name: 'app_history')]
    public function historyAction(): Response
    {
        return $this->render('group/history.html.twig');
    }

    #[Route('/giants', name: 'app_giants')]
    public function giantsAction(): Response
    {
        return $this->render('group/giants.html.twig');
    }

    #[Route('/references', name: 'app_references')]
    public function referencesAction(): Response
    {
        return $this->render('group/references.html.twig', [
            'references' => $this->referencesProvider->getReferences(),
        ]);
    }

    #[Route('/friends', name: 'app_friends')]
    public function friendsAction(): Response
    {
        return $this->render('group/friends.html.twig');
    }

    #[Route('/shows', name: 'app_shows')]
    public function showsAction(): Response
    {
        return $this->render('group/shows.html.twig');
    }

    #[Route('/shows/gala', name: 'app_shows_gala')]
    public function showsGalaAction(): Response
    {
        return $this->render('group/shows/gala.html.twig');
    }

    #[Route('/shows/prestations', name: 'app_shows_prestations')]
    public function showsPrestationsAction(): Response
    {
        return $this->render('group/shows/prestations.html.twig');
    }

    #[Route('/shows/ecoles', name: 'app_shows_ecoles')]
    public function showsEcolesAction(): Response
    {
        return $this->render('group/shows/ecoles.html.twig');
    }

    #[Route('/shows/festivals', name: 'app_shows_festivals')]
    public function showsFestivalsAction(): Response
    {
        return $this->render('group/shows/festivals.html.twig', [
            'latestFestivals' => $this->referencesProvider->getLatestFestivals(),
        ]);
    }

    #[Route('/workshops', name: 'app_workshops')]
    public function workshopsAction(): Response
    {
        return $this->render('group/workshops.html.twig', [
            'workshops' => $this->workshopsProvider->getStages(),
        ]);
    }

    #[Route('/trips', name: 'app_trips')]
    public function tripsAction(): Response
    {
        return $this->render('group/trips.html.twig', [
            'trips' => $this->tripsProvider->getVoyages(),
        ]);
    }

    #[Route('/contact', name: 'app_contact')]
    public function contactAction(Request $request, MailerInterface $mailer): Response
    {
        $contactForm = $this->createForm(ContactRequestType::class, new ContactRequest());
        $contactForm->handleRequest($request);

        if ($contactForm->isSubmitted() && $contactForm->isValid()) {
            $this->sendContactEmail($contactForm->getData(), $mailer);

            $this->addFlash('success', 'Votre message a bien été envoyé, merci ! Nous vous répondrons rapidement.');

            return $this->redirectToRoute('app_contact', ['sent' => 'contact']);
        }

        $registrationForm = $this->createForm(RegistrationRequestType::class, new RegistrationRequest());
        $registrationForm->handleRequest($request);

        if ($registrationForm->isSubmitted() && $registrationForm->isValid()) {
            $this->sendRegistrationEmail($registrationForm->getData(), $mailer);

            $this->addFlash('success', 'Votre demande d\'inscription a bien été envoyée, merci ! Un responsable vous recontactera pour la finaliser.');

            return $this->redirectToRoute('app_contact', ['sent' => 'inscription']);
        }

        return $this->render('group/contact.html.twig', [
            'contactForm' => $contactForm->createView(),
            'registrationForm' => $registrationForm->createView(),
            'activeTab' => $request->query->get('sent', 'contact-form') === 'inscription' ? 'inscription-form' : 'contact-form',
        ]);
    }

    private function sendContactEmail(ContactRequest $contactRequest, MailerInterface $mailer): void
    {
        $senderAddress = $this->getParameter('app.sender_address');
        $senderName = $this->getParameter('app.sender_name');

        $email = (new TemplatedEmail())
            ->from(new Address($senderAddress, $senderName))
            ->to($senderAddress)
            ->replyTo(new Address($contactRequest->email, $contactRequest->fullName))
            ->subject('[Contact] ' . $contactRequest->subject)
            ->htmlTemplate('emails/contact_request.html.twig')
            ->context(['contactRequest' => $contactRequest]);

        $mailer->send($email);
    }

    private function sendRegistrationEmail(RegistrationRequest $registrationRequest, MailerInterface $mailer): void
    {
        $senderAddress = $this->getParameter('app.sender_address');
        $senderName = $this->getParameter('app.sender_name');

        $email = (new TemplatedEmail())
            ->from(new Address($senderAddress, $senderName))
            ->to($senderAddress)
            ->replyTo(new Address($registrationRequest->email, $registrationRequest->firstName . ' ' . $registrationRequest->lastName))
            ->subject('[Inscription] ' . $registrationRequest->firstName . ' ' . $registrationRequest->lastName)
            ->htmlTemplate('emails/registration_request.html.twig')
            ->context(['registrationRequest' => $registrationRequest]);

        $mailer->send($email);
    }
}

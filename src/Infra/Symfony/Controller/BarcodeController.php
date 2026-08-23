<?php

declare(strict_types=1);

namespace Infra\Symfony\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Domain\Barcode\Service\PdfPrintTicket;
use Infra\Symfony\Persistance\Doctrine\Entity\Barcode;
use Infra\Symfony\Persistance\Doctrine\Repository\BarcodeRepository;
use Infra\Symfony\Persistance\Doctrine\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

class BarcodeController extends AbstractController
{
    #[Route('/barcode', name:'app_barcode_index')]
    public function indexAction(): Response
    {
        return $this->render('barcode/index.html.twig', []);
    }

    #[Route('/barcode/scan', name:'app_barcode_scan')]
    public function scanAction(
        Request $request,
        BarcodeRepository $barcodeRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $barcodeValue = $request->request->get('barcodeValue');
        $barcode = $barcodeRepository->findOneByValue($barcodeValue);
        if ($barcode) {
            if ($barcode->isScanned()) {
                return new JsonResponse([
                    'value' => $barcodeValue,
                    'barcode' => $barcode,
                    'name' => $barcode->getAttendeeDisplayName(),
                    'scanned' => true
                ]);
            }

            $barcode->setScanned(true);
            $entityManager->persist($barcode);
            $entityManager->flush();
        }

        return new JsonResponse([
            'value' => $barcodeValue,
            'barcode' => $barcode,
            'name' => $barcode?->getAttendeeDisplayName(),]);
    }

    #[Route('/barcode/add', name:'app_barcode_add')]
    public function addAction(
        Request $request,
        BarcodeRepository $barcodeRepository,
        EventRepository $eventRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse|BinaryFileResponse {
        $eventId = $request->query->get('eventId');
        $seat = $request->query->get('seat');
        $firstname = $request->query->get('firstname');
        $lastname = $request->query->get('lastname');
        $email = $request->query->get('email');
        $category = $request->query->get('category');
        $price = $request->query->get('price');
        $k = $request->query->get('k');

        if ($k !== 'ca4e6893-fdaf-4e1f-b844-f0f') {
            return new JsonResponse([
                'error' => 'Forbidden',
            ], Response::HTTP_FORBIDDEN);
        }

        if ($eventId === null || $seat === null || $firstname === null || $lastname === null || $category === null || $price === null) {
            return new JsonResponse([
                'error' => 'Mising parameter',
            ], Response::HTTP_NOT_ACCEPTABLE);
        }

        $event = $eventRepository->find($eventId);
        if (!$event) {
            return new JsonResponse([
                'error' => 'Event not found',
            ], Response::HTTP_NOT_FOUND);
        }

        $barcode = $barcodeRepository->findOneBy([
            'event' => $eventId,
            'seat' => $seat,
        ]);
        if (!$barcode) {
            $barcode = new Barcode();
            $barcode->setEvent($event);
            $barcode->setSeat($seat);
        }
        $barcode->setFirstname($firstname);
        $barcode->setLastname($lastname);
        $barcode->setEmail($email);
        $barcode->setCategory($category);
        $barcode->setPrice((int)$price);

        $entityManager->persist($barcode);
        $entityManager->flush();

        return $this->createPdf($barcode);
    }

    private function createPdf(Barcode $barcode): BinaryFileResponse
    {
        $filename = "public/barcodes/" . $barcode->getPdfFileName();
        $pdf = PdfPrintTicket::createDocument($barcode);

        $test = __DIR__ . '/../../../../'.$filename;
        $content = $pdf->Output(__DIR__ . '/../../../../'.$filename, "F");
        $pdf->freeResources();
        $fullName = realpath($test);

        $fileResponse = new BinaryFileResponse($fullName);
        $fileResponse->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $barcode->getPdfFileName());

        return $fileResponse;
    }
}

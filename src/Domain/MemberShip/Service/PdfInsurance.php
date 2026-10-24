<?php

namespace Domain\MemberShip\Service;

use Domain\Barcode\Service\PdfPrint;
use Infra\Symfony\Persistance\Doctrine\Entity\Barcode;
use Infra\Symfony\Persistance\Doctrine\Entity\MemberShip;
use IntlDateFormatter;

class PdfInsurance extends PdfPrint
{
    public static function createDocument(MemberShip $memberShip): self
    {
        $pdf = new self('P', 'mm', 'A4');
        $pdf->setMargins(0, 0, 0);
        $pdf->setAutoPageBreak(false);
        $pdf->setAuthor("Clap'Sabots");
        $pdf->setCreator("Clap'Sabots");
        $pdf->setTitle("Clap'Sabots - ". $memberShip->getPdfFileName("Insurance"));

        $pdf->AddPage();
        $member = $memberShip->getMember();
        $clubYear = $memberShip->getClubYear();
        $club = $clubYear->getClub();
        $federation = "Wallonne des Groupements de Danses et/ou de Musiques Populaires";
        $sport = "Danse folklorique";
        $responsable = "LÉVÊQUE Sébastien";
        $date = new \DateTimeImmutable();

        $pageWidth = $pdf->getPageWidth();
        $pageHeight = $pdf->getPageHeight();

        if ($member->getInsurer() === 'PARTENA') {
            $pdf->Image(
                dirname(__DIR__, 4) . "/public/images/insurance/insurance_partenamut.jpg",
                0, 0, $pageWidth, $pageHeight, '', '',  '',  2
            );

            $pdf->setFont('FreeSans', '', 13);
            $pdf->setXY(45,60);
            $pdf->Cell(200, 4, $member->getLastname(), 0, 1, 'L');
            $pdf->setX(45);
            $pdf->Cell(200, 4, $member->getFirstname(), 0, 1, 'L');
            $pdf->setX(45);
            $pdf->Cell(200, 4, $member->getAddress()->getFirstLine(), 0, 1, 'L');
            $pdf->setX(45);
            $pdf->Cell(200, 4, $member->getAddress()->getZipCode() .' '. $member->getAddress()->getCity(), 0, 1, 'L');

            $pdf->setXY(67,103);
            $pdf->Cell(200, 5, $club->getName(), 0, 1, 'L');

            $pdf->setFont('FreeSans', '', 11);
            $pdf->setXY(67,112);
            $pdf->Cell(200, 5, $federation, 0, 1, 'L');

            $pdf->setFont('FreeSans', '', 17);
            $pdf->setXY(116,129);
            $pdf->Cell(200, 5, $memberShip->getStartDate()->format('d'), 0, 1, 'L');
            $pdf->setXY(129,129);
            $pdf->Cell(200, 5, $memberShip->getStartDate()->format('m'), 0, 1, 'L');
            $pdf->setXY(142,129);
            $pdf->Cell(200, 5, $memberShip->getStartDate()->format('Y'), 0, 1, 'L');

            $pdf->setFont('FreeSans', '', 13);
            $pdf->setXY(45,137);
            $pdf->Cell(200, 5, $sport, 0, 1, 'L');

            $pdf->setFont('FreeSans', '', 17);
            $pdf->setXY(60,156);
            $pdf->Cell(200, 5, $memberShip->getSubscriptionAmount(), 0, 1, 'L');
        } elseif ($member->getInsurer() === 'MC') {
            $pdf->Image(
                dirname(__DIR__, 4) . "/public/images/insurance/insurance_mc.jpg",
                0, 0, $pageWidth, $pageHeight, '', '',  '',  2
            );

            $pdf->setFont('FreeSans', '', 13);
            $pdf->setXY(42,79);
            $pdf->Cell(200, 4, $member->getLastname() .' '. $member->getFirstname(), 0, 0, 'L');
            $pdf->setXY(42,87);
            $pdf->Cell(200, 4, $member->getBirthdate()->format('d/m/Y'), 0, 0, 'L');
            $pdf->setXY(42,103);
            $pdf->Cell(200, 4, $member->getAddress()->getFirstLine(), 0, 0, 'L');
            $pdf->setXY(42,111);
            $pdf->Cell(200, 4, $member->getAddress()->getZipCode() .' '. $member->getAddress()->getCity(), 0, 1, 'L');
            $pdf->setXY(130,79);
            $pdf->Cell(200, 4, $member->getMobilePhone(), 0, 1, 'L');
            $pdf->setXY(130,88);
            $pdf->Cell(200, 4, $member->getEmail(), 0, 1, 'L');

            $pdf->setXY(90,156);
            $pdf->Cell(200, 5, $club->getName(), 0, 1, 'L');
            $pdf->setXY(60,165);
            $pdf->Cell(200, 4, $club->getHeadOfficeAddress()->__toString(), 0, 1, 'L');
            $pdf->setXY(60,173);
            $pdf->Cell(200, 4, $sport, 0, 1, 'L');
            $pdf->setXY(60,181);
            $pdf->Cell(200, 4, $responsable, 0, 1, 'L');
            $pdf->setXY(60,189);
            $pdf->Cell(200, 4, $member->getLastname() .' '. $member->getFirstname(), 0, 0, 'L');
            $pdf->setXY(180,189);
            $pdf->Cell(200, 5, $memberShip->getSubscriptionAmount(), 0, 1, 'L');
            $pdf->setXY(117,198);
            $pdf->Cell(215, 5, $memberShip->getStartDate()->format('d/m/Y'), 0, 1, 'L');
            $pdf->setXY(157,198);
            $pdf->Cell(240, 5, $memberShip->getEndDate()->format('d/m/Y'), 0, 1, 'L');
            $pdf->setXY(70,207);
            $pdf->Cell(240, 5, $memberShip->getSubscriptionPaidAt()->format('d/m/Y'), 0, 1, 'L');
            $pdf->setXY(20,215);
            $pdf->Cell(240, 5, $date->format('d/m/Y'), 0, 1, 'L');


        } elseif ($member->getInsurer() === 'SOLIDARIS') {
            $pdf->Image(
                dirname(__DIR__, 4) . "/public/images/insurance/insurance_solidaris.jpg",
                0, 0, $pageWidth, $pageHeight, '', '',  '',  2
            );
            $pdf->setFont('FreeSans', '', 12);
            $pdf->setXY(55,96);
            $pdf->Cell(200, 4, $member->getLastname() .' '. $member->getFirstname(), 0, 0, 'L');

            $pdf->setXY(65,167);
            $pdf->Cell(200, 4, $responsable, 0, 1, 'L');
            $pdf->setXY(65,180);
            $pdf->Cell(200, 5, $club->getName(), 0, 1, 'L');
            $pdf->setXY(65,187);
            $pdf->Cell(200, 4, $club->getHeadOfficeAddress()->getFirstLine(), 0, 1, 'L');
            $pdf->setXY(65,194);
            $pdf->Cell(200, 4, $club->getHeadOfficeAddress()->getZipCode() .' '. $club->getHeadOfficeAddress()->getCity(), 0, 1, 'L');
            $pdf->setXY(105,208);
            $pdf->Cell(200, 4, $member->getLastname() .' '. $member->getFirstname(), 0, 0, 'L');
            $pdf->setXY(86,222);
            $pdf->Cell(200, 5, $memberShip->getSubscriptionAmount(), 0, 1, 'L');
            $pdf->setXY(60,230);
            $pdf->Cell(215, 5, $memberShip->getStartDate()->format('d/m/Y'), 0, 1, 'L');
            $pdf->setXY(125,230);
            $pdf->Cell(240, 5, $memberShip->getEndDate()->format('d/m/Y'), 0, 1, 'L');
            $pdf->setXY(86,237);
            $pdf->Cell(200, 4, $sport, 0, 1, 'L');
            $pdf->setXY(40,258);
            $pdf->Cell(240, 5, $date->format('d/m/Y'), 0, 1, 'L');


        } elseif ($member->getInsurer() === 'MN') {
            $pdf->Image(
                dirname(__DIR__, 4) . "/public/images/insurance/insurance_mn.jpg",
                0, 0, $pageWidth, $pageHeight, '', '',  '',  2
            );

            $pdf->setFont('FreeSans', '', 12);
            $pdf->setXY(50,56);
            $pdf->Cell(200, 4, $member->getLastname(), 0, 0, 'L');
            $pdf->setXY(50,63);
            $pdf->Cell(200, 4, $member->getFirstname(), 0, 0, 'L');
            $pdf->setXY(50,78);
            $pdf->Cell(200, 4, $member->getAddress()->getFirstLine(), 0, 0, 'L');
            $pdf->setXY(65,86);
            $pdf->Cell(200, 4, $member->getAddress()->getZipCode() .' '. $member->getAddress()->getCity(), 0, 1, 'L');
            $pdf->setXY(50,94);
            $pdf->Cell(200, 4, $member->getMobilePhone(), 0, 1, 'L');
            $pdf->setXY(100,94);
            $pdf->Cell(200, 4, $member->getEmail(), 0, 1, 'L');

            $pdf->setXY(110,111);
            $pdf->Cell(200, 4, $responsable, 0, 1, 'L');
            $pdf->setXY(80,118);
            $pdf->Cell(200, 5, $club->getName(), 0, 1, 'L');
            $pdf->setXY(45,125);
            $pdf->Cell(200, 4, $club->getHeadOfficeAddress()->__toString(), 0, 1, 'L');
            $pdf->setXY(75,132);
            $pdf->Cell(200, 4, $member->getLastname() .' '. $member->getFirstname(), 0, 0, 'L');
            $pdf->setXY(23,141);
            $pdf->Cell(200, 4, "X", 0, 1, 'L');
            $pdf->setXY(60,148);
            $pdf->Cell(200, 4, $sport, 0, 1, 'L');
            $pdf->setXY(165,148);
            $pdf->Cell(200, 5, $memberShip->getSubscriptionAmount(), 0, 1, 'L');
            $pdf->setXY(43,155);
            $pdf->Cell(240, 5, $memberShip->getSubscriptionPaidAt()?->format('d/m/Y'), 0, 1, 'L');
            $pdf->setXY(71,161);
            $pdf->Cell(200, 4, "X", 0, 1, 'L');
            $pdf->setXY(82,167);
            $pdf->Cell(215, 5, $memberShip->getStartDate()->format('d/m/Y'), 0, 1, 'L');
            $pdf->setXY(115,167);
            $pdf->Cell(240, 5, $memberShip->getEndDate()->format('d/m/Y'), 0, 1, 'L');
            $pdf->setXY(24,180);
            $pdf->Cell(240, 5, $date->format('d/m/Y'), 0, 1, 'L');

        }

        return $pdf;
    }
}

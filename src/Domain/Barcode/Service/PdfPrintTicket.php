<?php

namespace Domain\Barcode\Service;

use Infra\Symfony\Persistance\Doctrine\Entity\Barcode;
use IntlDateFormatter;

class PdfPrintTicket extends PdfPrint
{
    protected const UTF8_BULLET = "\u{2022}";

    public static function createDocument(Barcode $barcode): self
    {
        /*
         * Required fonts:
         * apt-get install fonts-freefont-ttf fonts-dejavu
         */
        $pdf = new self('P', 'mm', 'A4');
        $pdf->setMargins(0, 0, 0);
        $pdf->setAutoPageBreak(false);
        $pdf->setAuthor("Clap'Sabots");
        $pdf->setCreator("Clap'Sabots");
        $pdf->setTitle("Clap'Sabots - Gala 46ème anniversaire - 15 Avril 2023");

        $pdf->AddPage();
        //$pdf->RoundedRect(5, 5, 255, 255, 3.50, '1111', 'DF', null, array(255, 255, 255));
        $pdf->RoundedRect(10, 5, 190, 90, 3.50, '1111', 'DF', null, array(255, 255, 255));

        $logoTitreImage = dirname(__DIR__, 4) . "/public/images/logo-titre.png";
        $logoImage = dirname(__DIR__, 4) . "/public/images/LogoClapTitre.jpg";
        $pdf->Image($logoTitreImage, 20, 8, 75, 0, '', '', '', 2, 300, '', false, false, 0, "CB");
        $pdf->Image($logoImage, 150, 270, 35, 0, '', '', '', 2, 300, '', false, false, 0, "CB");


        $pdf->write2DBarcode($barcode->getValue(), "QRCODE,Q", 140, 25+15, 50, 50, [
            'padding' => 3,
            'bgcolor' => [255, 255, 255]
        ]);
        $pdf->setXY(144, 73+15);
        $pdf->setFont('DejaVu Sans Mono', '', 7);
        $pdf->Cell(50, 3, $barcode->getValue(), 0, 0, 'L');



        $pdf->setFont('FreeSans', 'B', 17);
        $pdf->setXY(20, 29+15);
        $pdf->Cell(181, 7, "Gala 46ème anniversaire - 15 Avril 2023", 0, 0, 'L');


        $pdf->setFont('FreeSans', 'B', 13);
        $pdf->setXY(20,53);
        $pdf->Cell(60, 4, "Billet ". $barcode->getCategory(), 0, 1, 'L');

        $pdf->setFont('FreeSans', null, 11);
        $pdf->setXY(20,60);
        $pdf->Cell(60, 4, "CERIA - Auditorium Jacques Brel", 0, 1, 'L');
        $pdf->setXY(20,64);
        $pdf->Cell(60, 4, "Av. Emile Gryson 1/, 1070 Anderlecht", 0, 1, 'L');


        $pdf->setFont('FreeSans', 'B', 11);
        $pdf->setXY(20,60+15);
        $pdf->Cell(60, 4, "Prix", 0, 1, 'L');
        $pdf->setXY(40,60+15);
        $pdf->Cell(75, 4, "Place", 0, 1, 'L');
        $pdf->setXY(85,60+15);
        $pdf->Cell(60, 4, "Nom", 0, 1, 'L');

        $pdf->setFont('FreeSans', null, 11);
        $pdf->setXY(20,67+15);
        $pdf->Cell(60, 4, $barcode->getPrice() .",00 €", 0, 1, 'L');
        $pdf->setXY(40,67+15);
        $pdf->Cell(75, 4, $barcode->getSeat(), 0, 1, 'L');
        $pdf->setXY(85,67+15);
        $pdf->Cell(60, 4, $barcode->getAttendeeDisplayName(), 0, 1, 'L');


        $mapImage = dirname(__DIR__, 4) . "/public/images/map-ceria-detail.png";
        $pdf->Image($mapImage, 10, 100, 190, 0, '', '', '', 2, 300, '', false, false, 0, "CB");


        $pdf->setFont('FreeSans', null, 10);
        $pdf->setXY(10,230);
        $pdf->Cell(60, 4,"Av. Emile Gryson 1/, 1070 Anderlecht", 0, 1, 'L');
        $pdf->setXY(10,235);
        $pdf->Cell(60, 4,"Sortie R0 16 - Metro CERIA", 0, 1, 'L');



        $pdf->setFont('FreeSans', '', 7);
        $pdf->setXY(20, 250);
        $conditions = "Cet e-ticket est soumis aux conditions générales de vente de l'ASBL Ensemble Clap'Sabots, ainsi qu’à celles de l'organisation, que vous avez acceptées lors de votre commande.  Cet e-ticket est non remboursable. Sauf accord contraire de l’organisation, cet e-ticket est non transférable et non échangeable. L'accès au site est soumis au contrôle de validité de cet e-ticket.  Cet e-ticket est valable uniquement pour le lieu, la date et l'heure exacts définis par ledit e-ticket.   Après l''heure de début, l'accès au site n'est plus garanti et en cas d'accès refusé,  cet e-ticket ne donne pas droit à un remboursement.  Il est interdit de reproduire, de copier ou de contrefaire cet e-ticket de quelque manière que ce soit,  sous peine de poursuites judiciaires.  De même, toute commande utilisant une méthode de paiement illicite pour obtenir un e-ticket entraînera des poursuites pénales et rendra invalide cet e-ticket.";
        $pdf->setCellHeightRatio(0.9);
        $pdf->MultiCell(170, 100, $conditions, 0, 'J', false, 1, '', '', true, 0, false, true, 25);
        $pdf->setCellHeightRatio(1);


        /*
                $pdf->setFont('FreeSans', '', 6);
                $pdf->setXY($projectHeaderCol, $marginTopAdjust + 21);
                $pdf->Cell(181, 4, "Chau. de Mont-Saint-Jean 83, 1420 Braine-l'Alleud", 0, 0, 'L');

                $pdf->setY($marginTopAdjust + 24);

        /*
                $pdf->setFont('FreeSans', '', 5);
                $pdf->StartTransform();
                $pdf->Rotate(90, 153, $marginTopAdjust + 86);
                $pdf->setXY(153, $marginTopAdjust + 86);
                $conditions = "Cet e-ticket est soumis aux conditions générales de vente d’Emisys, ainsi qu’à celles de l''organisation, que vous avez acceptées lors de votre commande. Cet e-ticket est non remboursable. Sauf accord contraire de l’organisation, cet e-ticket est personnel, non transférable et non échangeable. L''accès au site est soumis au contrôle de validité de cet e-ticket. Cet e-ticket est valable uniquement pour le lieu, la date et l''heure exacts définis par ledit e-ticket.  Après l''heure de début, l''accès au site n''est plus garanti et en cas d''accès refusé, cet e-ticket ne donne pas droit à un remboursement.  Il est interdit de reproduire, de copier ou de contrefaire cet e-ticket de quelque manière que ce soit, sous peine de poursuites judiciaires. De même, toute commande utilisant une méthode de paiement illicite pour obtenir un e-ticket entraînera des poursuites pénales et rendra invalide cet e-ticket.";
                $pdf->setCellHeightRatio(0.9);
                $pdf->MultiCell(72, 25, $conditions, 0, 'J', false, 1, '', '', true, 0, false, true, 25);
                $pdf->setCellHeightRatio(1);
                $pdf->StopTransform();
        /*
                $pdf->StartTransform();
                $pdf->Rotate(270, 199, $marginTopAdjust + 14);
                $pdf->write1DBarcode($barcode->getValue(), "C128", 199, $marginTopAdjust + 14, 72, 20, '', [
                    'padding' => 1,
                    'text' => true,
                    'align' => "C",
                    'stretch' => true,
                    'bgcolor' => [255, 255, 255],
                    'font' => "DejaVu Sans Mono",
                    'fontsize' => 10,
                    'stretchtext' => 0
                ]);
                $pdf->StopTransform();
        */
        return $pdf;
    }

}

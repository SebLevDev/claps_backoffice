<?php

declare(strict_types=1);

namespace Infra\EasyAdmin\Controller;

use Domain\Barcode\Service\PdfPrintTicket;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use Infra\Symfony\Persistance\Doctrine\Entity\Barcode;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

#[AdminRoute(path: '/barcodes', name: 'barcode')]
class BarcodeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Barcode::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $renderPdf = Action::new('renderPdf', 'action.renderPdf')
            ->setIcon('fa fa-file-invoice')
            ->linkToCrudAction('renderPdf')
            ->setCssClass('btn');

        return $actions
            ->add(Crud::PAGE_EDIT, $renderPdf)
            ->add(Crud::PAGE_DETAIL, $renderPdf);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('barcode.barcode.title.singular')
            ->setEntityLabelInPlural('barcode.barcode.title.plural')
            ->setSearchFields(['id', 'value', 'firstname', 'lastname', 'email'])
            ->setPaginatorPageSize(100)
            ->overrideTemplate('label/null', 'easy_admin/label_null.html.twig');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IDField::new('id', 'ID')->onlyOnDetail(),
            TextField::new('value', 'word.value')->hideOnIndex(),
            EmailField::new('email', 'barcode.properties.email')->hideOnIndex(),
            TextField::new('firstname', 'barcode.properties.firstname'),
            TextField::new('lastname', 'barcode.properties.lastname'),
            TextField::new('category', 'barcode.properties.category')->hideOnIndex(),
            TextField::new('seat', 'barcode.properties.seat'),
            IntegerField::new('price', 'barcode.properties.price')->hideOnIndex(),
            AssociationField::new('event', 'event.crud.title.singular'),
            BooleanField::new('scanned', 'barcode.properties.scanned'),
        ];
    }

    public function renderPdf(AdminContext $context)
    {
        $barcode = $context->getEntity()->getInstance();

        $filename = "public/barcodes/" . $barcode->getPdfFileName();
        $pdf = PdfPrintTicket::createDocument($barcode);

        $test = __DIR__ . '/../../../../'.$filename;
        $content = $pdf->Output(__DIR__ . '/../../../../'.$filename, "F");
        $pdf->freeResources();
        $fullName = realpath($test);
/*
        return new Response($content, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '.pdf"'
            ]
        );*/
        $fileResponse = new BinaryFileResponse($fullName);
        $fileResponse->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $barcode->getPdfFileName());

        return $fileResponse;
    }

}

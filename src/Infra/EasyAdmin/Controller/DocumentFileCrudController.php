<?php

declare(strict_types=1);

namespace Infra\EasyAdmin\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use Infra\Symfony\Persistance\Doctrine\Entity\DocumentFile;
use Vich\UploaderBundle\Form\Type\VichFileType;

#[AdminRoute(path: '/documents/files', name: 'documentFile')]
class DocumentFileCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return DocumentFile::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('document_file.crud.title.singular')
            ->setEntityLabelInPlural('document_file.crud.title.plural')
            ->setSearchFields(['id', 'name', 'document'])
            ->setPaginatorPageSize(100)
            ->overrideTemplate('label/null', 'easy_admin/label_null.html.twig');
    }

    public function configureFields(string $pageName): iterable
    {
        $name = TextField::new('name');
        $documentCategory = AssociationField::new('documentCategory');
        $documentFile = Field::new('documentFile')->setFormType(VichFileType::class);
        $document = UrlField::new('document')->setTemplatePath('admin/field/property_documentfile.html.twig');;

        if (Crud::PAGE_INDEX === $pageName) {
            return [$name, $documentCategory, $document];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$name, $documentCategory, $document];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$name, $documentCategory, $documentFile];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$name, $documentCategory, $documentFile];
        }
    }
}

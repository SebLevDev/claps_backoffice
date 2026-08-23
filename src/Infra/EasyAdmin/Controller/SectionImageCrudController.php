<?php

declare(strict_types=1);

namespace Infra\EasyAdmin\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use Infra\Symfony\Persistance\Doctrine\Entity\SectionImage;
use Vich\UploaderBundle\Form\Type\VichImageType;

#[AdminRoute(path: '/section-images', name: 'sectionImage')]
class SectionImageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return SectionImage::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('section_image.crud.title.singular')
            ->setEntityLabelInPlural('section_image.crud.title.plural')
            ->setSearchFields(['id', 'image'])
            ->setPaginatorPageSize(100)
            ->overrideTemplate('label/null', 'easy_admin/label_null.html.twig');
    }

    public function configureFields(string $pageName): iterable
    {
        $section = AssociationField::new('section', 'section.crud.title.singular');
        $position = IntegerField::new('position', 'section_image.properties.position');
        $imageFile = Field::new('imageFile', 'word.image')->setFormType(VichImageType::class);
        $image = ImageField::new('image', 'word.image')->setBasePath('/uploads/section_images');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$section, $position, $image];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$section, $position, $image];
        }

        return [$section, $position, $imageFile];
    }
}

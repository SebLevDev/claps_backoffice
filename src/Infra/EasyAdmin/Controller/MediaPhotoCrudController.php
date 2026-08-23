<?php

declare(strict_types=1);

namespace Infra\EasyAdmin\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Infra\Symfony\Persistance\Doctrine\Entity\MediaPhoto;
use Vich\UploaderBundle\Form\Type\VichImageType;

#[AdminRoute(path: '/medias/photos', name: 'mediaPhoto')]
class MediaPhotoCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return MediaPhoto::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('media_photo.crud.title.singular')
            ->setEntityLabelInPlural('media_photo.crud.title.plural')
            ->setSearchFields(['id', 'caption'])
            ->setPaginatorPageSize(100)
            ->overrideTemplate('label/null', 'easy_admin/label_null.html.twig');
    }

    public function configureFields(string $pageName): iterable
    {
        $caption = TextField::new('caption', 'media_photo.properties.caption');
        $position = IntegerField::new('position', 'section_image.properties.position');
        $imageFile = Field::new('imageFile', 'word.image')->setFormType(VichImageType::class);
        $image = ImageField::new('image', 'word.image')->setBasePath('/uploads/media_photos');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$position, $caption, $image];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$position, $caption, $image];
        }

        return [$position, $caption, $imageFile];
    }
}

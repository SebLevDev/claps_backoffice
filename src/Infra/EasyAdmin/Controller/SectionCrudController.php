<?php

declare(strict_types=1);

namespace Infra\EasyAdmin\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Infra\Symfony\Persistance\Doctrine\Entity\Section;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

#[AdminRoute(path: '/sections', name: 'section')]
class SectionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Section::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Section')
            ->setEntityLabelInPlural('Section')
            ->setSearchFields(['id', 'name', 'code', 'slug'])
            ->setDefaultSort(['position' => 'ASC'])
            ->setPaginatorPageSize(100)
            ->overrideTemplate('label/null', 'easy_admin/label_null.html.twig');
    }

    public function configureFields(string $pageName): iterable
    {
        $id = IdField::new('id', 'ID')->onlyOnDetail();
        $name = TextField::new('name', 'word.name');
        $code = TextField::new('code', 'word.code');
        $slug = TextField::new('slug', 'section.properties.slug');
        $position = IntegerField::new('position', 'section.properties.position');
        $ageRange = TextField::new('ageRange', 'section.properties.age_range');
        $schedule = TextField::new('schedule', 'section.properties.schedule');
        $instructorName = TextField::new('instructorName', 'section.properties.instructor_name');
        $description = TextareaField::new('description', 'word.description');
        // Les photos se gèrent depuis leur propre écran (SectionImageCrudController), pas ici,
        // comme pour DocumentFile/DocumentCategory. On les affiche juste en lecture sur le détail.
        $images = AssociationField::new('images', 'section.properties.images')->onlyOnDetail();

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $position, $name, $code, $slug, $ageRange, $schedule, $instructorName];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$position, $name, $code, $slug, $ageRange, $schedule, $instructorName, $description, $images];
        }

        return [$position, $name, $code, $slug, $ageRange, $schedule, $instructorName, $description];
    }
}

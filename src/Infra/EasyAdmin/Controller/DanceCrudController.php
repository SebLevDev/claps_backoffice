<?php

declare(strict_types=1);

namespace Infra\EasyAdmin\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Field\CountryField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use Infra\EasyAdmin\Filter\CountryFilter;
use Infra\Symfony\Persistance\Doctrine\Entity\Dance;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

#[AdminRoute(path: '/dances', name: 'dance')]
class DanceCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Dance::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('dance.crud.title.singular')
            ->setEntityLabelInPlural('dance.crud.title.plural')
            ->setSearchFields(['id', 'name', 'url', 'country', 'region'])
            ->setPaginatorPageSize(100)
            ->overrideTemplate('label/null', 'easy_admin/label_null.html.twig');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('name')
            ->add(CountryFilter::new('country'));
    }

    public function configureFields(string $pageName): iterable
    {
        $name = TextField::new('name', 'word.name');
        $country = CountryField::new('country', 'address.properties.country');
        $id = IntegerField::new('id', 'ID');
        $description = TextEditorField::new('description');
        $region = TextField::new('region', 'dance.region');
        $videos = AssociationField::new('videos', 'dance.crud.title.video');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $name, $country, $region];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $name, $country, $region, $description, $videos];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$name, $country, $region, $description, $videos];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$name, $country, $region, $description, $videos];
        }
    }
}

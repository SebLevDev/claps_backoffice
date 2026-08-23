<?php

declare(strict_types=1);

namespace Infra\EasyAdmin\Controller;

use Domain\Reference\Enum\ReferenceIconEnum;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Infra\Symfony\Persistance\Doctrine\Entity\Reference;

#[AdminRoute(path: '/references', name: 'reference')]
class ReferenceCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Reference::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('reference.crud.title.singular')
            ->setEntityLabelInPlural('reference.crud.title.plural')
            ->setSearchFields(['id', 'name', 'city', 'country'])
            ->setDefaultSort(['year' => 'DESC'])
            ->setPaginatorPageSize(100)
            ->overrideTemplate('label/null', 'easy_admin/label_null.html.twig');
    }

    public function configureFields(string $pageName): iterable
    {
        $icon = ChoiceField::new('icon', 'reference.properties.icon')->setChoices(ReferenceIconEnum::cases());
        $name = TextField::new('name', 'word.name');
        $city = TextField::new('city', 'reference.properties.city');
        $country = TextField::new('country', 'address.properties.country');
        $year = IntegerField::new('year', 'reference.properties.year');
        $lat = NumberField::new('lat', 'reference.properties.lat');
        $lng = NumberField::new('lng', 'reference.properties.lng');
        $description = TextareaField::new('description', 'word.description');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$year, $icon, $name, $city, $country];
        }

        return [$icon, $name, $city, $country, $year, $lat, $lng, $description];
    }
}

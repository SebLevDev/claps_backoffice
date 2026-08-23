<?php

declare(strict_types=1);

namespace Infra\EasyAdmin\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Infra\EasyAdmin\Filter\CountryFilter;
use Infra\Symfony\Persistance\Doctrine\Entity\ClothesPiece;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CountryField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;

#[AdminRoute(path: '/clothes/pieces', name: 'clothesPiece')]
class ClothesPieceCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ClothesPiece::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Clothes piece')
            ->setEntityLabelInPlural('Clothes pieces')
            ->setSearchFields(['id', 'name', 'code', 'description', 'country', 'area', 'city', 'gender', 'image'])
            ->setPaginatorPageSize(100)
            ->setDefaultSort(['code' => 'ASC'])
            ->overrideTemplate('label/null', 'easy_admin/label_null.html.twig');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('code')
            ->add('name')
            ->add('gender')
            ->add(EntityFilter::new('clotheType'))
            ->add(CountryFilter::new('country'))
            ->add(EntityFilter::new('sections'));
    }

    public function configureFields(string $pageName): iterable
    {
        $name = TextField::new('name');
        $code = TextField::new('code');
        $gender = ChoiceField::new('gender', 'Gender')
            ->allowMultipleChoices()
            ->autocomplete()
            ->setChoices([ 'Male' => 'M', 'Female' => 'F'])
            ->setTemplatePath('admin/field/property_gender.html.twig');
        $personal = BooleanField::new('personal');
        $sections = AssociationField::new('sections');
        $country = CountryField::new('country');
        $area = TextField::new('area');
        $city = TextField::new('city');
        $description = TextareaField::new('description');
        $clotheType = AssociationField::new('clotheType');
        $imageFile = Field::new('imageFile');
        $clothesPieceStocks = AssociationField::new('clothesPieceStocks', 'Stocks');
        $id = IdField::new('id', 'ID');
        $image = ImageField::new('image');
        $updatedAt = DateTimeField::new('updatedAt');
        $season = AssociationField::new('season');
        $clothesCostumePieces = AssociationField::new('clothesCostumePieces');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$image, $code, $gender, $name, $country, $clothesPieceStocks];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $name, $code, $description, $country, $area, $city, $personal, $gender, $image, $updatedAt, $season, $clotheType, $sections, $clothesCostumePieces, $clothesPieceStocks];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$name, $code, $gender, $personal, $sections, $country, $area, $city, $description, $clotheType, $imageFile, $clothesPieceStocks];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$name, $code, $gender, $personal, $sections, $country, $area, $city, $description, $clotheType, $imageFile, $clothesPieceStocks];
        }
    }
}

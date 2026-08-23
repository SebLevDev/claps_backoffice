<?php

declare(strict_types=1);

namespace Infra\EasyAdmin\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use Infra\EasyAdmin\Filter\CountryFilter;
use Infra\Symfony\Form\Type\CostumePieceType;
use Infra\Symfony\Persistance\Doctrine\Entity\ClothesCostume;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CountryField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;

#[AdminRoute(path: '/clothes/costumes', name: 'clothesCostume')]
class ClothesCostumeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ClothesCostume::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('clothe_costume.crud.title.singular')
            ->setEntityLabelInPlural('clothe_costume.crud.title.plural')
            ->setSearchFields(['id', 'name', 'code', 'description', 'country', 'area', 'city', 'gender'])
            ->setPaginatorPageSize(100)
            ->setDefaultSort(['country' => 'ASC'])
            ->overrideTemplate('label/null', 'easy_admin/label_null.html.twig');
    }

    public function configureActions(Actions $actions): Actions
    {
        $sendInvoice = Action::new('pieceDashboard', 'pieceDashboard', 'fa fa-cubes')
            ->linkToRoute('admin_clothes_costume_dashboard', function (ClothesCostume $costume): array {
                return [
                    'id' => $costume->getId(),
                ];
            });

        return $actions->add(Crud::PAGE_DETAIL, $sendInvoice);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('code')
            ->add('name')
            ->add('gender')
            ->add(CountryFilter::new('country'))
            ->add(EntityFilter::new('sections'));
    }

    public function configureFields(string $pageName): iterable
    {
        $name = TextField::new('name', 'word.name');
        $code = TextField::new('code', 'word.code');
        $gender = ChoiceField::new('gender', 'clothe_costume.properties.gender')
            ->allowMultipleChoices()
            ->autocomplete()
            ->setChoices([
                'word.gender.male' => ClothesCostume::GENDER_MALE,
                'word.gender.female' => ClothesCostume::GENDER_FEMALE])
            ->setTemplatePath('admin/field/property_gender.html.twig');
        $sections = AssociationField::new('sections', 'section.crud.title.plural');
        $country = CountryField::new('country', 'address.properties.country');
        $area = TextField::new('area', 'address.properties.area');
        $city = TextField::new('city', 'address.properties.city');
        $description = TextareaField::new('description', 'word.description');
       // $season = AssociationField::new('season', 'clothe_season.crud.title.singular');
       // $clotheOpportunity = AssociationField::new('clotheOpportunity', 'clothe_opportunity.crud.title.singular');
        $clothesCostumePieces = CollectionField::new('clothesCostumePieces', 'clothe_piece.crud.title.plural')
            ->setFormTypeOption('entry_type', CostumePieceType::class)
            ->setTemplatePath('admin/field/clothes_costume_pieces.html.twig');
        $id = IdField::new('id', 'ID');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$code, $gender, $name, $country];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $name, $code, $description, $country, $area, $city, $gender, $sections, $clothesCostumePieces];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$name, $code, $gender, $sections, $country, $area, $city, $description, $clothesCostumePieces];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$name, $code, $gender, $sections, $country, $area, $city, $description, $clothesCostumePieces];
        }
    }
}

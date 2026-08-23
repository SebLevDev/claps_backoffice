<?php

declare(strict_types=1);

namespace Infra\EasyAdmin\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Infra\Symfony\Persistance\Doctrine\Entity\ClothesPieceStock;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

#[AdminRoute(path: '/clothes/stock', name: 'clothesStock')]
class ClothesPieceStockCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ClothesPieceStock::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Clothes Stock')
            ->setEntityLabelInPlural('Clothes Stock')
            ->setSearchFields(['id', 'status'])
            ->setPaginatorPageSize(100)
            ->overrideTemplate('label/null', 'easy_admin/label_null.html.twig');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id', 'ID')->onlyOnDetail(),
            TextField::new('status'),
            ChoiceField::new('status')
                ->hideOnIndex()
                ->setChoices([
                    'Used' => 'USED',
                    'Stock' => 'STOCK',
                ]),

            AssociationField::new('colors')->setTemplatePath('admin/field/property_color.html.twig'),
            AssociationField::new('clothesPiece'),
            AssociationField::new('dressKeeper'),
            BooleanField::new('personal'),
            AssociationField::new('dressMaker'),
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('status'))
            ->add(EntityFilter::new('clothesPiece'))
            ->add(EntityFilter::new('dressKeeper'))
            ->add(EntityFilter::new('dressMaker'));
    }
}

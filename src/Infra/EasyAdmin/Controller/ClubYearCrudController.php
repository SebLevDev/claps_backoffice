<?php

declare(strict_types=1);

namespace Infra\EasyAdmin\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Infra\Symfony\Persistance\Doctrine\Entity\ClubYear;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;

#[AdminRoute(path: '/club-years', name: 'clubYear')]
class ClubYearCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ClubYear::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('club_year.crud.title.singular')
            ->setEntityLabelInPlural('club_year.crud.title.plural')
            ->setSearchFields(['id'])
            ->setPaginatorPageSize(100)
            ->overrideTemplate('label/null', 'easy_admin/label_null.html.twig');
    }

    public function configureFields(string $pageName): iterable
    {
        $dateStart = DateField::new('dateStart', 'club_year.properties.date_start');
        $dateStop = DateField::new('dateStop', 'club_year.properties.date_stop');
        $isActive = Field::new('isActive', 'club_year.properties.is_active');
        $club = AssociationField::new('club', 'club.crud.title.singular');
        $memberShips = AssociationField::new('memberShips', 'memberShips.crud.title.plural');
        $id = IntegerField::new('id', 'ID');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $dateStart, $dateStop, $isActive, $club, $memberShips];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $dateStart, $dateStop, $isActive, $club, $memberShips];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$dateStart, $dateStop, $isActive, $club, $memberShips];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$dateStart, $dateStop, $isActive, $club, $memberShips];
        }
    }
}

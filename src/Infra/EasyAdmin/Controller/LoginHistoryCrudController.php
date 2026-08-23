<?php

declare(strict_types=1);

namespace Infra\EasyAdmin\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use Infra\Symfony\Persistance\Doctrine\Entity\LoginHistory;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

#[AdminRoute(path: '/security/logs', name: 'loginHistory')]
class LoginHistoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return LoginHistory::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('loginHistory.crud.title.singular')
            ->setEntityLabelInPlural('loginHistory.crud.title.plural')
            ->setSearchFields(['id', 'user', 'date', 'clientIp'])
            ->setPaginatorPageSize(100)
            ->overrideTemplate('label/null', 'easy_admin/label_null.html.twig');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id', 'ID')->onlyOnDetail(),
            AssociationField::new('user', 'loginHistory.properties.user'),
            DateTimeField::new('date', 'loginHistory.properties.date'),
            TextField::new('clientIp', 'loginHistory.properties.clientIp'),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->setPermission(Action::DETAIL, 'ROLE_SUPER_ADMIN')
            ;
    }
}

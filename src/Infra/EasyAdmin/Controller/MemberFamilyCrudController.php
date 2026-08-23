<?php

declare(strict_types=1);

namespace Infra\EasyAdmin\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Infra\Symfony\Persistance\Doctrine\Entity\MemberFamily;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

#[AdminRoute(path: '/member-families', name: 'memberfamily')]
class MemberFamilyCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return MemberFamily::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Family')
            ->setEntityLabelInPlural('Families')
            ->setSearchFields(['id', 'lastname', 'motherEmail', 'motherMobilePhone', 'fatherEmail', 'fatherMobilePhone'])
            ->setPaginatorPageSize(100)
            ->overrideTemplate('label/null', 'easy_admin/label_null.html.twig');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnDetail(),
            TextField::new('lastname'),
            FormField::addRow(),
            EmailField::new('motherEmail')->setColumns('col-sm-6 col-lg-5 col-xxl-3'),
            EmailField::new('fatherEmail')->setColumns('col-sm-6 col-lg-5 col-xxl-3'),
            FormField::addRow(),
            TelephoneField::new('motherMobilePhone')->setColumns('col-sm-6 col-lg-5 col-xxl-3'),
            TelephoneField::new('fatherMobilePhone')->setColumns('col-sm-6 col-lg-5 col-xxl-3'),
        ];
    }
}

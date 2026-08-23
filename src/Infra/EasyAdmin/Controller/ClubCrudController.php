<?php

declare(strict_types=1);

namespace Infra\EasyAdmin\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use Infra\Symfony\Persistance\Doctrine\Entity\Club;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\CountryField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

#[AdminRoute(path: '/clubs', name: 'club')]
class ClubCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Club::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('club.crud.title.singular')
            ->setEntityLabelInPlural('club.crud.title.plural')
            ->setSearchFields(['id', 'name', 'vatNumber'])
            ->setPaginatorPageSize(100)
            ->overrideTemplate('label/null', 'easy_admin/label_null.html.twig');
    }

    public function configureFields(string $pageName): iterable
    {

        return [
            FormField::addFieldset('club.crud.form.general'),
            IDField::new('id', 'ID')->onlyOnDetail(),
            TextField::new('name', 'word.name'),
            EmailField::new('email', 'club.properties.email'),
            UrlField::new('website', 'club.properties.website'),
            TextField::new('bankNumber', 'club.properties.bank_number'),
            TextField::new('vatNumber', 'club.properties.vat_number'),

            FormField::addFieldset('club.crud.form.headoffice_address')->collapsible(),
            TextField::new('headOfficeAddress.street','address.properties.street')->hideOnIndex(),
            TextField::new('headOfficeAddress.streetNumber','address.properties.streetNumber')->hideOnIndex(),
            TextField::new('headOfficeAddress.streetBox','address.properties.streetBox')->hideOnIndex(),
            TextField::new('headOfficeAddress.zipCode','address.properties.zipCode')->hideOnIndex(),
            TextField::new('headOfficeAddress.city','address.properties.city')->hideOnIndex(),
            CountryField::new('headOfficeAddress.country','address.properties.country')->hideOnIndex(),
        ];
    }
}

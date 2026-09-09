<?php

declare(strict_types=1);

namespace Infra\EasyAdmin\Controller;

use Domain\Event\Enum\EventTypeEnum;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Infra\Symfony\Persistance\Doctrine\Entity\Event;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;


#[AdminRoute(path: '/events', name: 'event')]
class EventCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Event::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('event.crud.title.singular')
            ->setEntityLabelInPlural('event.crud.title.plural')
            ->setSearchFields(['id', 'name'])
            ->setPaginatorPageSize(100)
            ->overrideTemplate('label/null', 'easy_admin/label_null.html.twig');
    }

    public function configureFields(string $pageName): iterable
    {
        $name = TextField::new('name', 'word.name');
        $date = DateTimeField::new('date', 'word.date');
        $endDate = DateTimeField::new('endDate', 'event.properties.end_date')->setRequired(false);
        $vanue = TextField::new('venue', 'vent.properties.venue');
        $isHighlight = Field::new('isHighlight', 'event.properties.is_highlight');
        $type = ChoiceField::new('type', 'event.properties.type')->setChoices(EventTypeEnum::cases());
        $videos = AssociationField::new('videos', 'event.crud.title.plural');
        $id = IntegerField::new('id', 'ID');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $name, $date, $vanue, $type, $isHighlight, $videos];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $name, $date, $endDate, $vanue, $type, $isHighlight, $videos];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$name, $date, $endDate, $vanue, $type, $isHighlight, $videos];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$name, $date, $endDate, $vanue, $type, $isHighlight, $videos];
        }
    }
}

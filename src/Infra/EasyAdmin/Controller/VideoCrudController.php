<?php

declare(strict_types=1);

namespace Infra\EasyAdmin\Controller;

use Domain\Video\Enum\VideoTagEnum;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CountryField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use Infra\EasyAdmin\Filter\CountryFilter;
use Infra\Symfony\Persistance\Doctrine\Entity\Video;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;

#[AdminRoute(path: '/videos', name: 'video')]
class VideoCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Video::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('video.crud.title.singular')
            ->setEntityLabelInPlural('video.crud.title.plural')
            ->setSearchFields(['id', 'name', 'url', 'country'])
            ->setPaginatorPageSize(100)
            ->overrideTemplate('label/null', 'easy_admin/label_null.html.twig');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('name')
            ->add(CountryFilter::new('country'))
            ->add(EntityFilter::new('sections'));
    }

    public function configureFields(string $pageName): iterable
    {
        $name = TextField::new('name', 'word.name');
        $url = UrlField::new('url', 'word.url');
        $event = AssociationField::new('event', 'event.crud.title.singular');
        $sections = AssociationField::new('sections', 'section.crud.title.plural');
        $recordDate = DateTimeField::new('recordDate', 'video.properties.record_date');
        $country = CountryField::new('country', 'address.properties.country');
        $id = IntegerField::new('id', 'ID');
        $description = TextEditorField::new('description');
        $tag = ChoiceField::new('tag')->setChoices(VideoTagEnum::cases());
        $playlistVideos = AssociationField::new('playlistVideos', 'playlist.crud.title.plural');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $name, $event, $sections, $country, $url];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $name, $url, $recordDate, $country, $sections, $playlistVideos, $event, $tag, $description];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$name, $url, $event, $sections, $recordDate, $country, $tag, $description];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$name, $url, $event, $sections, $recordDate, $country, $tag, $description];
        }
    }
}

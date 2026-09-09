<?php

declare(strict_types=1);

namespace Infra\EasyAdmin\Controller;

use Domain\BlogArticle\Enum\BlogArticleTagEnum;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Infra\Symfony\Persistance\Doctrine\Entity\BlogArticle;
use Vich\UploaderBundle\Form\Type\VichImageType;

#[AdminRoute(path: '/blog/articles', name: 'blogArticle')]
class BlogArticleCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return BlogArticle::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('blog_article.crud.title.singular')
            ->setEntityLabelInPlural('blog_article.crud.title.plural')
            ->setSearchFields(['id', 'title', 'slug', 'tag'])
            ->setDefaultSort(['date' => 'DESC'])
            ->setPaginatorPageSize(100)
            ->overrideTemplate('label/null', 'easy_admin/label_null.html.twig');
    }

    public function configureFields(string $pageName): iterable
    {
        $title = TextField::new('title', 'word.name');
        $slug = TextField::new('slug', 'blog_article.properties.slug')
            ->setRequired(false)
            ->setHelp('blog_article.properties.slug_help');
        $tag = ChoiceField::new('tag', 'blog_article.properties.tag')->setChoices(BlogArticleTagEnum::cases());
        $date = DateTimeField::new('date', 'word.date');
        $isPublished = BooleanField::new('isPublished', 'blog_article.properties.is_published');
        $resume = TextareaField::new('resume', 'blog_article.properties.resume');
        $content = TextEditorField::new('content', 'blog_article.properties.content');
        $imageFile = Field::new('imageFile', 'word.image')->setFormType(VichImageType::class);
        $image = ImageField::new('image', 'word.image')->setBasePath('/uploads/blog_articles');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$title, $tag, $date, $isPublished];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$title, $slug, $tag, $date, $isPublished, $resume, $image, $content];
        }

        return [$title, $slug, $tag, $date, $isPublished, $resume, $imageFile, $content];
    }
}

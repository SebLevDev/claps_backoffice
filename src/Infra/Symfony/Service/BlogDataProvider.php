<?php

declare(strict_types=1);

namespace Infra\Symfony\Service;

use Infra\Symfony\Persistance\Doctrine\Entity\BlogArticle;
use Infra\Symfony\Persistance\Doctrine\Repository\BlogArticleRepository;

class BlogDataProvider
{
    public function __construct(private readonly BlogArticleRepository $blogArticleRepository) {}

    /** @return array<int, array<string, mixed>> */
    public function getArticles(): array
    {
        return array_map($this->toArray(...), $this->blogArticleRepository->findPublished());
    }

    /** @return array<string, mixed>|null */
    public function getArticleBySlug(string $slug): ?array
    {
        $article = $this->blogArticleRepository->findOnePublishedBySlug($slug);

        return $article === null ? null : $this->toArray($article);
    }

    /** @return array<string, mixed> */
    private function toArray(BlogArticle $article): array
    {
        return [
            'slug'    => $article->getSlug(),
            'tag'     => $article->getTag(),
            'title'   => $article->getTitle(),
            'date'    => $article->getDate(),
            'resume'  => $article->getResume(),
            'img'     => $this->resolveImageUrl($article->getImage()),
            'content' => $article->getContent(),
        ];
    }

    /**
     * Old imported articles store a full external URL (old WordPress media).
     * Images uploaded via the admin store just the filename (VichUploader), which
     * needs the mapping's uri_prefix prepended to be a valid <img src>.
     */
    private function resolveImageUrl(?string $image): ?string
    {
        if ($image === null || $image === '') {
            return null;
        }

        if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://') || str_starts_with($image, '/')) {
            return $image;
        }

        return '/uploads/blog_articles/' . $image;
    }
}

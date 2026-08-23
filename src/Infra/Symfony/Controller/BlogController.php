<?php

declare(strict_types=1);

namespace Infra\Symfony\Controller;

use Infra\Symfony\Service\BlogDataProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

class BlogController extends AbstractController
{
    private const PER_PAGE = 10;

    public function __construct(private readonly BlogDataProvider $provider) {}

    #[Route('/blog', name: 'app_blog_list')]
    public function listAction(Request $request): Response
    {
        $articles = $this->provider->getArticles();
        $total = count($articles);
        $totalPages = max(1, (int) ceil($total / self::PER_PAGE));
        $page = max(1, min($request->query->getInt('page', 1), $totalPages));

        return $this->render('blog/list.html.twig', [
            'articles'    => array_slice($articles, ($page - 1) * self::PER_PAGE, self::PER_PAGE),
            'currentPage' => $page,
            'totalPages'  => $totalPages,
            'total'       => $total,
        ]);
    }

    #[Route('/blog/{slug}', name: 'app_blog_show')]
    public function showAction(string $slug): Response
    {
        $article = $this->provider->getArticleBySlug($slug);

        if ($article === null) {
            throw new NotFoundHttpException();
        }

        return $this->render('blog/show.html.twig', [
            'article' => $article,
        ]);
    }
}
<?php

declare(strict_types=1);

namespace Infra\Symfony\Controller;

use Infra\Symfony\Persistance\Doctrine\Repository\BlogArticleRepository;
use Infra\Symfony\Persistance\Doctrine\Repository\SectionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SitemapController extends AbstractController
{
    public function __construct(
        private readonly SectionRepository $sectionRepository,
        private readonly BlogArticleRepository $blogArticleRepository,
    ) {}

    /**
     * Pages statiques du frontoffice public. /medias est volontairement exclu (page masquée,
     * contenu non terminé) — voir _header.html.twig/_footer.html.twig.
     *
     * @var array<int, array{route: string, changefreq: string, priority: string}>
     */
    private const STATIC_PAGES = [
        ['route' => 'app_index',              'changefreq' => 'weekly',  'priority' => '1.0'],
        ['route' => 'app_sections_list',       'changefreq' => 'weekly',  'priority' => '0.9'],
        ['route' => 'app_agenda_list',         'changefreq' => 'daily',   'priority' => '0.8'],
        ['route' => 'app_blog_list',           'changefreq' => 'weekly',  'priority' => '0.7'],
        ['route' => 'app_history',             'changefreq' => 'monthly', 'priority' => '0.6'],
        ['route' => 'app_team',                'changefreq' => 'monthly', 'priority' => '0.5'],
        ['route' => 'app_giants',              'changefreq' => 'yearly',  'priority' => '0.4'],
        ['route' => 'app_references',          'changefreq' => 'monthly', 'priority' => '0.6'],
        ['route' => 'app_friends',             'changefreq' => 'monthly', 'priority' => '0.5'],
        ['route' => 'app_shows',               'changefreq' => 'monthly', 'priority' => '0.7'],
        ['route' => 'app_shows_gala',          'changefreq' => 'monthly', 'priority' => '0.6'],
        ['route' => 'app_shows_prestations',   'changefreq' => 'monthly', 'priority' => '0.6'],
        ['route' => 'app_shows_ecoles',        'changefreq' => 'monthly', 'priority' => '0.6'],
        ['route' => 'app_shows_festivals',     'changefreq' => 'monthly', 'priority' => '0.6'],
        ['route' => 'app_workshops',           'changefreq' => 'monthly', 'priority' => '0.6'],
        ['route' => 'app_trips',               'changefreq' => 'monthly', 'priority' => '0.6'],
        ['route' => 'app_contact',             'changefreq' => 'yearly',  'priority' => '0.5'],
    ];

    #[Route('/sitemap.xml', name: 'app_sitemap')]
    public function sitemapAction(): Response
    {
        $urls = [];

        foreach (self::STATIC_PAGES as $page) {
            $urls[] = [
                'loc'        => $this->generateUrl($page['route'], [], UrlGeneratorInterface::ABSOLUTE_URL),
                'changefreq' => $page['changefreq'],
                'priority'   => $page['priority'],
            ];
        }

        foreach ($this->sectionRepository->findBy([]) as $section) {
            if ($section->getSlug() === null) {
                continue;
            }

            $urls[] = [
                'loc'        => $this->generateUrl('app_sections_show', ['slug' => $section->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL),
                'changefreq' => 'monthly',
                'priority'   => '0.7',
            ];
        }

        foreach ($this->blogArticleRepository->findPublished() as $article) {
            $urls[] = [
                'loc'        => $this->generateUrl('app_blog_show', ['slug' => $article->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL),
                'lastmod'    => $article->getDate()?->format('Y-m-d'),
                'changefreq' => 'yearly',
                'priority'   => '0.5',
            ];
        }

        $response = $this->render('sitemap.xml.twig', ['urls' => $urls]);
        $response->headers->set('Content-Type', 'text/xml; charset=UTF-8');

        return $response;
    }

    /**
     * Généré dynamiquement (plutôt qu'un fichier statique dans public/) pour que la directive
     * Sitemap: pointe toujours vers l'hôte réel, sans dépendre du domaine de déploiement.
     */
    #[Route('/robots.txt', name: 'app_robots')]
    public function robotsAction(): Response
    {
        $disallow = [
            '/medias', // page masquée, contenu non terminé
            '/admin',
            '/login',
            '/register',
            '/reset-password',
            '/profile',
            '/video',
            '/clothes',
            '/documents',
            '/events',
            '/media',
            '/music',
            '/playlist',
            '/dance',
            '/barcode',
        ];

        $lines = ['User-agent: *', 'Allow: /'];
        foreach ($disallow as $path) {
            $lines[] = 'Disallow: ' . $path;
        }
        $lines[] = '';
        $lines[] = 'Sitemap: ' . $this->generateUrl('app_sitemap', [], UrlGeneratorInterface::ABSOLUTE_URL);

        return new Response(implode("\n", $lines), 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}

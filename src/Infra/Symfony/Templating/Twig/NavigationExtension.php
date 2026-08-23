<?php

declare(strict_types=1);

namespace Infra\Symfony\Templating\Twig;

use Infra\Symfony\Persistance\Doctrine\Entity\Section;
use Infra\Symfony\Persistance\Doctrine\Repository\SectionRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Exposes data needed by templates shared across every page (e.g. _header.html.twig),
 * so it doesn't have to be passed down explicitly from every single controller.
 */
class NavigationExtension extends AbstractExtension
{
    public function __construct(private readonly SectionRepository $sectionRepository) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('nav_sections', [$this, 'getNavSections']),
        ];
    }

    /** @return Section[] */
    public function getNavSections(): array
    {
        return $this->sectionRepository->findBy([], ['position' => 'ASC', 'id' => 'ASC']);
    }
}

<?php

declare(strict_types=1);

namespace Infra\Symfony\EventSubscriber;

use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use Infra\Symfony\Persistance\Doctrine\Entity\BlogArticle;
use Infra\Symfony\Persistance\Doctrine\Repository\BlogArticleRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class BlogArticleSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly SluggerInterface $slugger,
        private readonly BlogArticleRepository $blogArticleRepository,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeEntityPersistedEvent::class => ['generateSlug'],
        ];
    }

    public function generateSlug(BeforeEntityPersistedEvent $event): void
    {
        $entity = $event->getEntityInstance();

        if (!$entity instanceof BlogArticle || trim((string) $entity->getSlug()) !== '') {
            return;
        }

        $entity->setSlug($this->buildUniqueSlug($entity->getTitle()));
    }

    private function buildUniqueSlug(string $title): string
    {
        $base = strtolower((string) $this->slugger->slug($title, '-', 'fr'));
        $slug = $base;

        for ($i = 2; $this->blogArticleRepository->findOneBy(['slug' => $slug]) !== null; ++$i) {
            $slug = $base.'-'.$i;
        }

        return $slug;
    }
}

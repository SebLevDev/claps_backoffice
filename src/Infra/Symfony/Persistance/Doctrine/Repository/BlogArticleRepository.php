<?php

declare(strict_types=1);

namespace Infra\Symfony\Persistance\Doctrine\Repository;

use Infra\Symfony\Persistance\Doctrine\Entity\BlogArticle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BlogArticle|null find($id, $lockMode = null, $lockVersion = null)
 * @method BlogArticle|null findOneBy(array $criteria, array $orderBy = null)
 * @method BlogArticle[]    findAll()
 * @method BlogArticle[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BlogArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BlogArticle::class);
    }

    /** @return BlogArticle[] */
    public function findPublished(): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.isPublished = true')
            ->orderBy('a.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOnePublishedBySlug(string $slug): ?BlogArticle
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.isPublished = true')
            ->andWhere('a.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }
}

<?php

declare(strict_types=1);

namespace Infra\Symfony\Persistance\Doctrine\Repository;

use Infra\Symfony\Persistance\Doctrine\Entity\MediaPhoto;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MediaPhoto|null find($id, $lockMode = null, $lockVersion = null)
 * @method MediaPhoto|null findOneBy(array $criteria, array $orderBy = null)
 * @method MediaPhoto[]    findAll()
 * @method MediaPhoto[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MediaPhotoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MediaPhoto::class);
    }

    /** @return MediaPhoto[] */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.position', 'ASC')
            ->addOrderBy('p.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
}

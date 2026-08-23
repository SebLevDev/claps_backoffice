<?php

declare(strict_types=1);

namespace Infra\Symfony\Persistance\Doctrine\Repository;

use Infra\Symfony\Persistance\Doctrine\Entity\Reference;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Reference|null find($id, $lockMode = null, $lockVersion = null)
 * @method Reference|null findOneBy(array $criteria, array $orderBy = null)
 * @method Reference[]    findAll()
 * @method Reference[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReferenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reference::class);
    }

    /** @return Reference[] */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('r')
            ->orderBy('r.year', 'ASC')
            ->getQuery()
            ->getResult();
    }
}

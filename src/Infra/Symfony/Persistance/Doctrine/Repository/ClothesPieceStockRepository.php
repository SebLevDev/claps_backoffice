<?php

declare(strict_types=1);

namespace Infra\Symfony\Persistance\Doctrine\Repository;

use Infra\Symfony\Persistance\Doctrine\Entity\ClothesPieceStock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ClothesPieceStock|null find($id, $lockMode = null, $lockVersion = null)
 * @method ClothesPieceStock|null findOneBy(array $criteria, array $orderBy = null)
 * @method ClothesPieceStock[]    findAll()
 * @method ClothesPieceStock[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClothesPieceStockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ClothesPieceStock::class);
    }

    // /**
    //  * @return ClothesPieceStock[] Returns an array of ClothesPieceStock objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ClothesPieceStock
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

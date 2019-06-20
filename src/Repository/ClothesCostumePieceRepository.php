<?php

namespace App\Repository;

use App\Entity\ClothesCostumePiece;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ClothesCostumePiece|null find($id, $lockMode = null, $lockVersion = null)
 * @method ClothesCostumePiece|null findOneBy(array $criteria, array $orderBy = null)
 * @method ClothesCostumePiece[]    findAll()
 * @method ClothesCostumePiece[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClothesCostumePieceRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ClothesCostumePiece::class);
    }

    // /**
    //  * @return ClothesCostumePiece[] Returns an array of ClothesCostumePiece objects
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
    public function findOneBySomeField($value): ?ClothesCostumePiece
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
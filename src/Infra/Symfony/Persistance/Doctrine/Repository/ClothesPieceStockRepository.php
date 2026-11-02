<?php

declare(strict_types=1);

namespace Infra\Symfony\Persistance\Doctrine\Repository;

use Infra\Symfony\Persistance\Doctrine\Entity\ClothesPieceStock;
use Infra\Symfony\Persistance\Doctrine\Entity\Member;
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

    /**
     * @param int[] $pieceIds
     *
     * @return ClothesPieceStock[]
     */
    public function findForPieces(array $pieceIds): array
    {
        return $this->createQueryBuilder('s')
            ->addSelect('maker', 'keeper', 'color')
            ->leftJoin('s.dressMaker', 'maker')
            ->leftJoin('s.dressKeeper', 'keeper')
            ->leftJoin('s.colors', 'color')
            ->andWhere('s.clothesPiece IN (:pieces)')
            ->setParameter('pieces', $pieceIds)
            ->orderBy('s.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Toutes les pièces uniques actuellement chez un membre, avec leurs costumes.
     *
     * @return ClothesPieceStock[]
     */
    public function findKeptBy(Member $keeper): array
    {
        return $this->createQueryBuilder('s')
            ->addSelect('piece', 'maker', 'color', 'costumePiece', 'costume')
            ->join('s.clothesPiece', 'piece')
            ->leftJoin('s.dressMaker', 'maker')
            ->leftJoin('s.colors', 'color')
            ->leftJoin('piece.clothesCostumePieces', 'costumePiece')
            ->leftJoin('costumePiece.costume', 'costume')
            ->andWhere('s.dressKeeper = :keeper')
            ->setParameter('keeper', $keeper)
            ->orderBy('piece.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Pièces uniques d'un costume actuellement chez un membre.
     *
     * @param int[] $pieceIds
     *
     * @return ClothesPieceStock[]
     */
    public function findForPiecesKeptBy(array $pieceIds, Member $keeper): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.clothesPiece IN (:pieces)')
            ->andWhere('s.dressKeeper = :keeper')
            ->setParameter('pieces', $pieceIds)
            ->setParameter('keeper', $keeper)
            ->getQuery()
            ->getResult();
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

<?php

declare(strict_types=1);

namespace Infra\Symfony\Persistance\Doctrine\Repository;

use Infra\Symfony\Persistance\Doctrine\Entity\Member;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Member|null find($id, $lockMode = null, $lockVersion = null)
 * @method Member|null findOneBy(array $criteria, array $orderBy = null)
 * @method Member[]    findAll()
 * @method Member[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MemberRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Member::class);
    }

    // /**
    //  * @return Member[] Returns an array of Member objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Member
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /**
     * @param \Symfony\Component\Security\Core\User\User $user
     * @return QueryBuilder
     */
    public function filterByParentUser(\Symfony\Component\Security\Core\User\User $user): QueryBuilder
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.firstname LIKE :firstname')
            ->setParameter('firstname', '%'.$user->getUsername() .'%')
            ->setMaxResults(10)
            ;
    }
}
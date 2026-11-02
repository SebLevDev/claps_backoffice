<?php

declare(strict_types=1);

namespace Infra\Symfony\Persistance\Doctrine\Repository;

use Infra\Symfony\Persistance\Doctrine\Entity\ClubYear;
use Infra\Symfony\Persistance\Doctrine\Entity\MemberShip;
use Infra\Symfony\Persistance\Doctrine\Entity\Section;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MemberShip|null find($id, $lockMode = null, $lockVersion = null)
 * @method MemberShip|null findOneBy(array $criteria, array $orderBy = null)
 * @method MemberShip[]    findAll()
 * @method MemberShip[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MemberShipRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MemberShip::class);
    }

    /**
     * Inscriptions de l'année aux sections données, pour l'attribution des costumes.
     *
     * @param iterable<Section> $sections
     * @param string[]|null     $genders  null = tous les sexes
     *
     * @return MemberShip[]
     */
    public function findForCostumeAssignation(ClubYear $year, iterable $sections, ?array $genders = null): array
    {
        $query = $this->createQueryBuilder('m')
            ->addSelect('mem')
            ->join('m.member', 'mem')
            ->join('m.sections', 'section')
            ->andWhere('m.clubYear = :year')
            ->andWhere('section IN (:sections)')
            ->setParameter('year', $year)
            ->setParameter('sections', $sections)
            ->addOrderBy('mem.firstname', 'ASC')
            ->addOrderBy('mem.lastname', 'ASC')
        ;

        if (null !== $genders) {
            $query
                ->andWhere('mem.sex IN (:genders)')
                ->setParameter('genders', $genders);
        }

        return $query->getQuery()->getResult();
    }

    /** @return int[] */
    public function findMemberIdsForYear(ClubYear $year): array
    {
        return $this->createQueryBuilder('m')
            ->select('IDENTITY(m.member)')
            ->andWhere('m.clubYear = :year')
            ->setParameter('year', $year)
            ->getQuery()
            ->getSingleColumnResult();
    }
}

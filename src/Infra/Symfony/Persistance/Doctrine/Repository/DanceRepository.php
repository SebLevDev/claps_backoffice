<?php

declare(strict_types=1);

namespace Infra\Symfony\Persistance\Doctrine\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Domain\Video\Enum\VideoTagEnum;
use Infra\Symfony\Persistance\Doctrine\Entity\Dance;
use Infra\Symfony\Persistance\Doctrine\Entity\Video;
use Infra\Symfony\Utils\SqlParameterBag;

/**
 * @method Dance|null find($id, $lockMode = null, $lockVersion = null)
 * @method Dance|null findOneBy(array $criteria, array $orderBy = null)
 * @method Dance[]    findAll()
 * @method Dance[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DanceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Dance::class);
    }

    public function getCountryList(): array
    {
        $result = $this->createQueryBuilder('d')
            ->select('d.country')
            ->distinct()
            ->getQuery()
            ->getArrayResult();

        $countries = [];
        foreach ($result as $i) {
            $countries[] = $i['country'];
        }

        return $countries;
    }

    private function filterAllQueryBuilder(SqlParameterBag $params): QueryBuilder
    {
        /** @var QueryBuilder $query */
        $query = $this->createQueryBuilder('dance');

        if ($params->has('search')) {
            $searchs = explode(' ', (string) $params->get('search'));
            foreach ($searchs as $key => $search) {
                $search = iconv('UTF-8', 'UTF-8//IGNORE', $search);
                $query
                    ->andWhere('dance.name LIKE :search' . $key)
                    ->setParameter('search' . $key, '%' . $search . '%');
            }
        }

        if ($params->has('country') && strlen((string) $params->get('country')) > 0) {
            $query
                ->andWhere('dance.country = :country')
                ->setParameter('country', $params->get('country'));
        }

        if ($params->has('hasWorkshopVideo') && filter_var($params->get('hasWorkshopVideo'), FILTER_VALIDATE_BOOLEAN)) {
            $subQuery = $this->getEntityManager()->createQueryBuilder()
                ->select('1')
                ->from(Video::class, 'workshopVideo')
                ->innerJoin('workshopVideo.dances', 'workshopVideoDance')
                ->andWhere('workshopVideoDance = dance')
                ->andWhere('workshopVideo.tag = :workshopTag');

            $query
                ->andWhere($query->expr()->exists($subQuery->getDQL()))
                ->setParameter('workshopTag', VideoTagEnum::Workshop->value);
        }

        if ($params->hasOrderBy()) {
            foreach ($params->getOrderBy() as $key => $value) {
                $query->addOrderBy('dance.' . $key, $value);
            }
        } else {
            // Par défaut : les danses les plus récemment ajoutées en premier ("les 25 dernières")
            $query->addOrderBy('dance.id', 'DESC');
        }

        return $query;
    }

    /**
     * @return Dance[]
     */
    public function filterAll(SqlParameterBag $params): array
    {
        return $this
            ->filterAllQueryBuilder($params)
            ->setFirstResult($params->getOffset())
            ->setMaxResults($params->getLimit())
            ->getQuery()
            ->getResult();
    }

    public function countAll(SqlParameterBag $params): int
    {
        return (int) $this
            ->filterAllQueryBuilder($params)
            ->resetDQLPart('orderBy')
            ->select('COUNT(DISTINCT dance.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}

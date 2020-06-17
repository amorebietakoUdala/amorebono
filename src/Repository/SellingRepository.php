<?php

namespace App\Repository;

use App\Entity\Selling;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Selling|null find($id, $lockMode = null, $lockVersion = null)
 * @method Selling|null findOneBy(array $criteria, array $orderBy = null)
 * @method Selling[]    findAll()
 * @method Selling[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SellingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Selling::class);
    }

    private function __removeBlankFilters($criteria)
    {
        $new_criteria = [];
        foreach ($criteria as $key => $value) {
            if (!empty($value)) {
                $new_criteria[$key] = $value;
            }
        }

        return $new_criteria;
    }

    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $criteriaLikeKeys = ['NAN' => 'NAN'];
        $criteriaLike = $criteriaAnd = null;
        if (null !== $criteria) {
            $criteriaLike = array_intersect_key($criteria, $criteriaLikeKeys);
            $criteriaAnd = array_diff_key($criteria, $criteriaLikeKeys);
        }
        $qb = $this->findByQB($criteriaAnd, $criteriaLike, $orderBy = null, $limit = null, $offset = null);
        $query = $qb->getQuery();
        $result = $query->getResult();

        return $result;
    }

    public function findByQB(array $criteriaAnd, $criteriaLike = null, array $orderBy = null, $limit = null, $offset = null)
    {
        $qb = $this->createQueryBuilder('s');
        $qb->innerJoin(\App\Entity\Person::class, 'p', 'WITH', 's.person = p.id');
        $from = (array_key_exists('fromDate', $criteriaAnd)) ? $criteriaAnd['fromDate'] : null;
        $criteriaAnd['fromDate'] = null;
        $to = (array_key_exists('toDate', $criteriaAnd)) ? $criteriaAnd['toDate'] : null;
        $criteriaAnd['toDate'] = null;
        $criteriaAnd = $this->__removeBlankFilters($criteriaAnd);
        if (null !== $from) {
            $qb->andWhere('s.sellingDate >= :from')
        ->setParameter('from', $from);
        }
        if (null !== $to) {
            $qb->andWhere('s.sellingDate <= :to')
        ->setParameter('to', $to);
        }
        if ($criteriaAnd) {
            foreach ($criteriaAnd as $field => $value) {
                $qb->andWhere('s.'.$field.' = :'.$field)
                    ->setParameter($field, $value);
            }
        }
//        dd($criteriaLike);
        if ($criteriaLike) {
            foreach ($criteriaLike as $field => $value) {
                $qb->andWhere('p.'.$field.' LIKE :'.$field)
                    ->setParameter($field, '%'.$value.'%');
            }
        }

        return $qb;
    }
}

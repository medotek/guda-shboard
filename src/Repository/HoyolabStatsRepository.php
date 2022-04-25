<?php

namespace App\Repository;

use App\Entity\HoyolabStats;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method HoyolabStats|null find($id, $lockMode = null, $lockVersion = null)
 * @method HoyolabStats|null findOneBy(array $criteria, array $orderBy = null)
 * @method HoyolabStats[]    findAll()
 * @method HoyolabStats[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HoyolabStatsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HoyolabStats::class);
    }

    // /**
    //  * @return HoyolabStats[] Returns an array of HoyolabStats objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('h.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?HoyolabStats
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

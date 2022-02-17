<?php

namespace App\Repository;

use App\Entity\HoyolabPostStats;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method HoyolabPostStats|null find($id, $lockMode = null, $lockVersion = null)
 * @method HoyolabPostStats|null findOneBy(array $criteria, array $orderBy = null)
 * @method HoyolabPostStats[]    findAll()
 * @method HoyolabPostStats[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HoyolabPostStatsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HoyolabPostStats::class);
    }

    // /**
    //  * @return HoyolabPostStats[] Returns an array of HoyolabPostStats objects
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
    public function findOneBySomeField($value): ?HoyolabPostStats
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

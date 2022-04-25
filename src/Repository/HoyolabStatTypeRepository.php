<?php

namespace App\Repository;

use App\Entity\HoyolabStatType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method HoyolabStatType|null find($id, $lockMode = null, $lockVersion = null)
 * @method HoyolabStatType|null findOneBy(array $criteria, array $orderBy = null)
 * @method HoyolabStatType[]    findAll()
 * @method HoyolabStatType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HoyolabStatTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HoyolabStatType::class);
    }

    // /**
    //  * @return HoyolabStatType[] Returns an array of HoyolabStatType objects
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
    public function findOneBySomeField($value): ?HoyolabStatType
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

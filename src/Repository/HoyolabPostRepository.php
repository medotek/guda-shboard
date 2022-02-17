<?php

namespace App\Repository;

use App\Entity\HoyolabPost;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method HoyolabPost|null find($id, $lockMode = null, $lockVersion = null)
 * @method HoyolabPost|null findOneBy(array $criteria, array $orderBy = null)
 * @method HoyolabPost[]    findAll()
 * @method HoyolabPost[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HoyolabPostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HoyolabPost::class);
    }

    // /**
    //  * @return HoyolabPost[] Returns an array of HoyolabPost objects
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
    public function findOneBySomeField($value): ?HoyolabPost
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

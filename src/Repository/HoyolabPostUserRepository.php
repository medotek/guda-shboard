<?php

namespace App\Repository;

use App\Entity\HoyolabPostUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method HoyolabPostUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method HoyolabPostUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method HoyolabPostUser[]    findAll()
 * @method HoyolabPostUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HoyolabPostUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HoyolabPostUser::class);
    }

    // /**
    //  * @return HoyolabPostUser[] Returns an array of HoyolabPostUser objects
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
    public function findOneBySomeField($value): ?HoyolabPostUser
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

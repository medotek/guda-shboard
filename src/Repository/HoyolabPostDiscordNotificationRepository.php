<?php

namespace App\Repository;

use App\Entity\HoyolabPostDiscordNotification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method HoyolabPostDiscordNotification|null find($id, $lockMode = null, $lockVersion = null)
 * @method HoyolabPostDiscordNotification|null findOneBy(array $criteria, array $orderBy = null)
 * @method HoyolabPostDiscordNotification[]    findAll()
 * @method HoyolabPostDiscordNotification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HoyolabPostDiscordNotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HoyolabPostDiscordNotification::class);
    }

    // /**
    //  * @return HoyolabPostDiscordNotification[] Returns an array of HoyolabPostDiscordNotification objects
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
    public function findOneBySomeField($value): ?HoyolabPostDiscordNotification
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

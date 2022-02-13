<?php

namespace App\Repository;

use App\Entity\DiscordWebhook;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DiscordWebhook|null find($id, $lockMode = null, $lockVersion = null)
 * @method DiscordWebhook|null findOneBy(array $criteria, array $orderBy = null)
 * @method DiscordWebhook[]    findAll()
 * @method DiscordWebhook[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DiscordWebhookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DiscordWebhook::class);
    }

    // /**
    //  * @return DiscordWebhook[] Returns an array of DiscordWebhook objects
    //  */
    //    public function findByOwner($value)
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.owner = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('d.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    /*
    public function findOneBySomeField($value): ?DiscordWebhook
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

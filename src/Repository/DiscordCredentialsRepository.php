<?php

namespace App\Repository;

use App\Entity\DiscordCredentials;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DiscordCredentials|null find($id, $lockMode = null, $lockVersion = null)
 * @method DiscordCredentials|null findOneBy(array $criteria, array $orderBy = null)
 * @method DiscordCredentials[]    findAll()
 * @method DiscordCredentials[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DiscordCredentialsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DiscordCredentials::class);
    }

    // /**
    //  * @return DiscordCredentials[] Returns an array of DiscordCredentials objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DiscordCredentials
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

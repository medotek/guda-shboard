<?php

namespace App\Repository;

use App\Entity\DiscordEmbedMessage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DiscordEmbedMessage|null find($id, $lockMode = null, $lockVersion = null)
 * @method DiscordEmbedMessage|null findOneBy(array $criteria, array $orderBy = null)
 * @method DiscordEmbedMessage[]    findAll()
 * @method DiscordEmbedMessage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DiscordEmbedMessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DiscordEmbedMessage::class);
    }


    /**
     * @return DiscordEmbedMessage[] Returns an array of DiscordEmbedMessage objects
     */
    public function findAllEmbedMessagesByDateDESC(): array
    {
        return $this->createQueryBuilder('d')
            ->orderBy('d.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    // /**
    //  * @return DiscordEmbedMessage[] Returns an array of DiscordEmbedMessage objects
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
    public function findOneBySomeField($value): ?DiscordEmbedMessage
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

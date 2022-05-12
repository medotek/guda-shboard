<?php

namespace App\Repository;

use App\Entity\HoyolabUserStats;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<HoyolabUserStats>
 *
 * @method HoyolabUserStats|null find($id, $lockMode = null, $lockVersion = null)
 * @method HoyolabUserStats|null findOneBy(array $criteria, array $orderBy = null)
 * @method HoyolabUserStats[]    findAll()
 * @method HoyolabUserStats[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HoyolabUserStatsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HoyolabUserStats::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(HoyolabUserStats $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(HoyolabUserStats $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return HoyolabUserStats[] Returns an array of HoyolabUserStats objects
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
    public function findOneBySomeField($value): ?HoyolabUserStats
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

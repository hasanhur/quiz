<?php

namespace App\Repository;

use App\Entity\Test;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Test|null find($id, $lockMode = null, $lockVersion = null)
 * @method Test|null findOneBy(array $criteria, array $orderBy = null)
 * @method Test[]    findAll()
 * @method Test[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Test::class);
    }

    // /**
    //  * @return Test[] Returns an array of Test objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Test
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /**
     * @param string slug
     * @return int The number of slugs that contain the given slug
     */

    public function countSlug($slug)
    {
        return $this->createQueryBuilder('t')
            ->select("count(t.slug)")
            ->andWhere('t.slug LIKE :slug')
            ->orderBy('t.slug', 'DESC')
            ->setParameter('slug', $slug.'%')
            ->getQuery()
            ->setMaxResults(1)
            ->getSingleScalarResult();
    }

    /**
     * @return Test[] Returns an array of Test objects
     */

    public function findAllPublishedTests()
    {
        $now = new \DateTime();
        return $this->createQueryBuilder('t')
            ->andWhere('t.active_from <= :date')
            ->andWhere('t.isPublished = 1')
            ->setParameter('date', $now)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Test[] Returns an array of Test objects
     */

    public function findPublishedTestsBySubject($subject)
    {
        $now = new \DateTime();
        return $this->createQueryBuilder('t')
            ->andWhere('t.active_from <= :date')
            ->andWhere('t.subject = :subject')
            ->setParameter('date', $now)
            ->setParameter('subject', $subject)
            ->getQuery()
            ->getResult()
        ;
    }
}

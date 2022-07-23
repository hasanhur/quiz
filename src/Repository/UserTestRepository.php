<?php

namespace App\Repository;

use App\Entity\UserTest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Tests;
use App\Repository\TestsRepository;

/**
 * @method UserTest|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserTest|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserTest[]    findAll()
 * @method UserTest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserTestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserTest::class);
    }

    // /**
    //  * @return UserTest[] Returns an array of UserTest objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?UserTest
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /**
     * @param int test id
     * @return int|null test id or null if the time is up
     */

    public function isInProgress($userTest)
    {
        return $this->createQueryBuilder('u')
            ->select('u.created_at')
            ->join('u.test', 't')
            ->where('u.test = :test')
            ->andWhere('u.user = :user')
            ->andWhere("DATE_SUB(CURRENT_TIMESTAMP(), t.max_time, 'second') <= u.created_at OR t.max_time = 0")
            ->andWhere("u.isSubmitted = 0")
            ->setParameter('test', $userTest->getTest())
            ->setParameter('user', $userTest->getUser())
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}

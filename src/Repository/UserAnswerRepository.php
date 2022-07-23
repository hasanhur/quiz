<?php

namespace App\Repository;

use App\Entity\UserAnswer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;
/**
 * @method UserAnswer|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserAnswer|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserAnswer[]    findAll()
 * @method UserAnswer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserAnswerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserAnswer::class);
    }

    // /**
    //  * @return UserAnswer[] Returns an array of UserAnswer objects
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
    public function findOneBySomeField($value): ?UserAnswer
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findAllUserAnswers($questions, $user): array
    {
        // return $this->createQuery("SELECT u.option FROM userAnswer u WHERE question IN ($questions->)")->getResult();
        $foundOptions = $this->createQueryBuilder('u')
            ->select('o.id')
            ->join('u.option', 'o')
            ->andWhere('u.question IN (:questions)')
            ->andWhere('u.user = :user')
            ->setParameter('questions', $questions)
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult()
        ;
        $options = [];
        foreach ($foundOptions as $option) {
            $options[] = $option["id"];
        }
        return $options;
    }
}

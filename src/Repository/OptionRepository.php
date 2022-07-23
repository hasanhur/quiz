<?php

namespace App\Repository;

use App\Entity\Option;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Option|null find($id, $lockMode = null, $lockVersion = null)
 * @method Option|null findOneBy(array $criteria, array $orderBy = null)
 * @method Option[]    findAll()
 * @method Option[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Option::class);
    }

    // /**
    //  * @return Option[] Returns an array of Option objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Option
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /**
     * @param Questions[] An array of Questions objects
     * @return array Returns an array of option id's (correct answers of given questions)
     */
    public function findCorrectAnswers($questions): array
    {
        $foundOptions = $this->createQueryBuilder('o')
            ->select('o.id')
            ->andWhere('o.isCorrect = 1')
            ->andWhere('o.question IN (:questions)')
            ->setParameter('questions', $questions)
            ->getQuery()
            ->getResult()
        ;
        $options = [];
        foreach ($foundOptions as $option) {
            $options[] = $option["id"];
        }

        return $options;
    }

    /**
     * @param Option[] An array of Option objects
     * @return array Returns an array of question id's
     */
    public function findQuestions($options): array
    {
        $foundQuestions = $this->createQueryBuilder('o')
            ->select('q.id')
            ->join('o.question', 'q')
            ->andWhere('o.id IN (:option)')
            ->setParameter('option', $options)
            ->getQuery()
            ->getResult()
        ;
        $questions = [];
        foreach ($foundQuestions as $question) {
            $questions[] = $question["id"];
        }

        return array_unique($questions);
    }
}

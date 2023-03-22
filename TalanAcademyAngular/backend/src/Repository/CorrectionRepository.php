<?php

namespace App\Repository;

use App\Entity\Correction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Correction|null find($id, $lockMode = null, $lockVersion = null)
 * @method Correction|null findOneBy(array $criteria, array $orderBy = null)
 * @method Correction[]    findAll()
 * @method Correction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CorrectionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Correction::class);
    }


    /**
     * @param $day
     * @param $user
     * @return mixed
     */
    public function findByDayUser($day, $user)
    {
        return $this->createQueryBuilder('c')
            ->join('c.correctionResults', 'correctionResults')
            ->join('correctionResults.orderCourse', 'orderCourse')
            ->andWhere('c.day = :day')
            ->setParameter('day', $day)
            ->andWhere('c.corrected = :corrected')
            ->setParameter('corrected', $user)
            ->select('correctionResults.result ', 'orderCourse.scale')
            ->getQuery()
            ->getResult();
    }
    public function findCorrectionsByUser($user, $day)
    {
        return $this->createQueryBuilder('c')
            ->join('c.corrector','corrector')
            ->join('c.corrected','corrected')
            ->join('c.day','day')
            ->where('corrector.id = :corrector')
            ->andWhere('day.id = :day')
            ->setParameters(['corrector'=>$user,'day'=>$day])
            ->select('c.id as correctionId', 'corrected.id as correctedId','corrected.firstName','corrected.lastName','day.id as dayId')
            ->getQuery()
            ->getResult();
    }
}

<?php

namespace App\Repository;

use App\Entity\DayCourse;
use App\Entity\DayInterface;
use App\Entity\OrderCourse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method OrderCourse|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrderCourse|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrderCourse[]    findAll()
 * @method OrderCourse[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderCourseRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, OrderCourse::class);
    }


    public function countOrderByDay(DayCourse $dayCourse)
    {
        return $this->createQueryBuilder('oc')
            ->join('oc.dayCourse','ocd')
            ->where('ocd.id = :id')
            ->andWhere('oc.deleted IS NULL')
            ->setParameter('id',$dayCourse->getId())
            ->select('count(oc.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
    public function checkDayValidateWithInstruction(DayInterface $day)
    {

        return $this->createQueryBuilder('oc')
            ->join('oc.dayCourse','ocd')
            ->where('ocd.id = :id')
            ->andWhere('oc.deleted IS NULL')
            ->setParameter('id',$day)
            ->select('count(oc.id)')
            ->getQuery()
            ->getSingleScalarResult();

    }
}

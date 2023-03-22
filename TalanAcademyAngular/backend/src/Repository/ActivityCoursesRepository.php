<?php

namespace App\Repository;

use App\Entity\ActivityCourses;
use App\Entity\DayCourse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ActivityCourses|null find($id, $lockMode = null, $lockVersion = null)
 * @method ActivityCourses|null findOneBy(array $criteria, array $orderBy = null)
 * @method ActivityCourses[]    findAll()
 * @method ActivityCourses[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ActivityCoursesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ActivityCourses::class);
    }

    public function countAll()
    {
        return $this->createQueryBuilder('ac')
            ->select('count(ac.id)')
            ->where('ac.deleted IS NULL')
            ->getQuery()
            ->getSingleScalarResult();
    }


    public function countActivitiesByDay(DayCourse $dayCourse)
    {
        return $this->createQueryBuilder('ac')
            ->select('count(ac.id)')
            ->join('ac.day', 'acd')
            ->where('acd.id = :id')
            ->andWhere('ac.deleted IS NULL')
            ->setParameter('id', $dayCourse->getId())
            ->getQuery()
            ->getSingleScalarResult();
    }
}

<?php

namespace App\Repository;

use App\Entity\SessionActivityCourses;
use App\Entity\SessionDayCourse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SessionActivityCourses|null find($id, $lockMode = null, $lockVersion = null)
 * @method SessionActivityCourses|null findOneBy(array $criteria, array $orderBy = null)
 * @method SessionActivityCourses[]    findAll()
 * @method SessionActivityCourses[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SessionActivityCoursesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SessionActivityCourses::class);
    }
    public function countAll()
    {
        return $this->createQueryBuilder('ac')
            ->select('count(ac.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }


    public function countActivitiesByDay(SessionDayCourse $dayCourse)
    {
        return $this->createQueryBuilder('ac')
            ->select('count(ac.id)')
            ->join('ac.day', 'acd')
            ->where('acd.id = :id')
            ->setParameter('id',$dayCourse->getId())
            ->getQuery()
            ->getSingleScalarResult();
    }
    public function getActivitiesByDay(SessionDayCourse $sessionDayCourse)
    {
        return $this->createQueryBuilder('sac')
            ->join('sac.day','day')
            ->where('day.id = :day')
            ->setParameter('day',$sessionDayCourse)
            ->select('sac.id', 'sac.title', 'sac.content', 'day.id as dayId')
            ->getQuery()
            ->getResult();
    }
}

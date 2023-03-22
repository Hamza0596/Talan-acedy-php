<?php

namespace App\Repository;

use App\Entity\Session;
use App\Entity\SessionDayCourse;
use App\Entity\Student;
use App\Entity\StudentReview;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method StudentReview|null find($id, $lockMode = null, $lockVersion = null)
 * @method StudentReview|null findOneBy(array $criteria, array $orderBy = null)
 * @method StudentReview[]    findAll()
 * @method StudentReview[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StudentReviewRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, StudentReview::class);
    }


    public function findRatingAverage(SessionDayCourse $dayCourse)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.course = :val')
            ->setParameter('val', $dayCourse)
            ->andWhere('s.rating is not NULL')
            ->select("distinct count(s.rating) as rating_count, s.rating as rating_value")
            ->groupBy('s.rating')
            ->getQuery()
            ->getResult();
    }

    public function findMinRating(SessionDayCourse $dayCourse)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.course = :val')
            ->setParameter('val', $dayCourse)
            ->select('s.rating')
            ->andWhere('s.rating is not NULL')
            ->select("min(s.rating) as rating_min")
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findMaxRating(SessionDayCourse $dayCourse)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.course = :val')
            ->setParameter('val', $dayCourse)
            ->select('s.rating')
            ->andWhere('s.rating is not NULL')
            ->select("max(s.rating) as rating_max")
            ->getQuery()
            ->getSingleScalarResult();
    }


    public function findRatingAverageBySession(Session $session)
    {
        return $this->createQueryBuilder('s')
            ->join('s.course', 'course')
            ->join('course.module', 'module')
            ->join('module.session', 'session')
            ->andWhere('session = :val')
            ->setParameter('val', $session)
            ->select('s.rating')
            ->andWhere('s.rating is not NULL')
            ->select("avg(s.rating) as rating_avg")
            ->getQuery()
            ->getSingleScalarResult();
    }


    public function countNbrEvaluatingDayForUser(Session $session, Student $student)
    {
        return $this->createQueryBuilder('s')
            ->join('s.course', 'course')
            ->join('course.module', 'session_module')
            ->join('session_module.session', 'session')
            ->andWhere('session.id = :session')
            ->setParameter('session', $session)
            ->andWhere('s.student = :student')
            ->andWhere('s.rating is not NULL')
            ->setParameter('student', $student)
            ->select('COUNT(s.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }


    public function getAverageEvaluatingDay($user, $session)
    {

        return $this->createQueryBuilder('sr')
            ->join('sr.student', 'student')
            ->leftJoin('sr.course', 'course')
            ->leftJoin('course.module', 'module')
            ->leftJoin('module.session', 'session')
            ->where('student.id = :userId')
            ->setParameter('userId', $user->getId())
            ->andWhere('session.id = :sessionId')
            ->setParameter('sessionId', $session)
            ->select('AVG(sr.rating)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countEvaluatingDay($user, $session)
    {
        return $this->createQueryBuilder('sw')
            ->join('sw.student', 'student')
            ->leftJoin('sw.course', 'course')
            ->leftJoin('course.module', 'module')
            ->leftJoin('module.session', 'session')
            ->where('student.id = :userId')
            ->setParameter('userId', $user->getId())
            ->andWhere('session.id = :sessionId')
            ->setParameter('sessionId', $session)
            ->select('count(sw.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findCommentNotNullByDay(SessionDayCourse $dayCourse)
    {
        return $this->createQueryBuilder('s')
            ->orderBy('s.id', 'DESC')
            ->andWhere('s.course = :val')
            ->setParameter('val', $dayCourse)
            ->andWhere('s.comment is not NULL')
            ->select('s.comment')
            ->getQuery()
            ->getResult();
    }
    public function findCommentNotNullByDayAndCandidate(SessionDayCourse $dayCourse, $candidate)
    {
        return $this->createQueryBuilder('s')
            ->orderBy('s.id', 'DESC')
            ->andWhere('s.course = :val')
            ->andWhere('s.comment is not NULL')
            ->andWhere('s.student = :candidate')
            ->setParameters(['val'=> $dayCourse,'candidate'=>$candidate])
            ->getQuery()
            ->getResult();
    }

}

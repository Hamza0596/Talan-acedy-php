<?php

namespace App\Repository;

use App\Entity\Affectation;
use App\Entity\SessionProjectSubject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Affectation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Affectation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Affectation[]    findAll()
 * @method Affectation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AffectationRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Affectation::class);
    }

    public function getSubjectsForStudent($student)
    {
        return $this->createQueryBuilder('a')
            ->innerJoin('a.subject','subject')
            ->select('subject.id')
            ->andWhere('a.student = :student')
            ->setParameter('student', $student)
            ->getQuery()
            ->getResult()
        ;
    }
    public function getAffectedStudentOnProject($project)
    {
        return $this->createQueryBuilder('a')
            ->join('a.subject','subject')
            ->join('a.student','student')
            ->select('student.id')
            ->where('subject.SessionProject = :project')
            ->andWhere('subject.status LIKE :status')
            ->setParameters(['project'=>$project,'status'=>SessionProjectSubject::ACTIVATED])
            ->getQuery()
            ->getResult();

    }

    public function getAffectedApprenticeOnSubject($subject)
    {
        return $this->createQueryBuilder('a')
            ->where('a.subject = :subject')
            ->setParameter('subject',$subject)
            ->getQuery()
            ->getResult();

    }

    public function checkAssignementApprenticeOnProject($student,$project)
    {
        return $this->createQueryBuilder('a')
            ->join('a.subject','subject')
            ->join('subject.SessionProject','session_project')
            ->where('session_project.id = :id')
            ->andWhere('a.student = :student')
            ->setParameters(['student'=>$student,'id'=>$project])
            ->getQuery()
            ->getResult();

    }

    public function checkAssignementApprenticeOnSubject($student,$subject)
    {
        return $this->createQueryBuilder('a')
            ->where('a.subject = :subject')
            ->andWhere('a.student = :student')
            ->setParameters(['student'=>$student,'subject'=>$subject])
            ->getQuery()
            ->getResult();

    }







}

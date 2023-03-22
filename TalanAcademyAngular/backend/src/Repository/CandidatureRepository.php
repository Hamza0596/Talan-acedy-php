<?php

namespace App\Repository;

use App\Entity\Candidature;
use App\Entity\Cursus;
use App\Entity\Staff;
use App\Entity\Student;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\TextType;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Candidature|null find($id, $lockMode = null, $lockVersion = null)
 * @method Candidature|null findOneBy(array $criteria, array $orderBy = null)
 * @method Candidature[]    findAll()
 * @method Candidature[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CandidatureRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Candidature::class);
    }


    public function getCandidateByCursusAndRole(Cursus $cursus, $role = null)
    {
        return $this->createQueryBuilder('candidature')
            ->join('candidature.cursus', 'cursus')
            ->join('candidature.candidat', 'candidat')
            ->where('cursus.id = :id')
            ->setParameter('id', $cursus->getId())
            ->andWhere('candidat.roles LIKE :roles')
            ->setParameter('roles', '%"' . $role . '"%')
            ->select('count(candidature.id)')
            ->getQuery()
            ->getSingleScalarResult();

    }

    public function countCandidature()
    {
        return $this
            ->createQueryBuilder('c')
            ->select("count(c.id)")
            ->getQuery()
            ->getSingleScalarResult();
    }

   public function getCandidatureData()
    {
        return $this->createQueryBuilder('c')
            ->join('c.candidat', 'candidat')
            ->leftJoin('c.cursus', 'cursus')
            ->leftjoin('candidat.sessionUserDatas', 'sessionUsers')
            ->leftJoin('sessionUsers.session', 'session')
            ->leftJoin('session.cursus', 'cursusSession')
            ->andWhere('c.status != :draft')
            ->setParameter('draft', Candidature::DRAFT)
            ->getQuery()
            ->getResult();
    }

    public function getCandidateForMentor(Staff $mentor)
    {
        return $this->createQueryBuilder('c')
            ->select('count(c.id)')
            ->join('c.cursus', 'cursus')
            ->where('cursus.id LIKE :cursus')
            ->andWhere('c.status LIKE :accepted')
            ->setParameter('cursus', $mentor->getCursus()->getId())
            ->setParameter('accepted', Candidature::ACCEPTE)
            ->getQuery()
            ->getSingleScalarResult();

    }

    public function getCandidatureByArrayOfStatus($user, array $arrayStatus)
    {
        return $this
            ->createQueryBuilder('c')
            ->andWhere('c.status IN (:arrayStatus)')
            ->andWhere('c.candidat = :user')
            ->setParameter('arrayStatus', $arrayStatus)
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    public function getCandidatureAcceptedWithStatusApprenti($user)
    {
        return $this
            ->createQueryBuilder('c')
            ->join('c.candidat', 'candidat')
            ->where('candidat.id LIKE :candidat')
            ->setParameter('candidat', $user->getId())
            ->andWhere('c.status LIKE :accepted')
            ->setParameter('accepted', Candidature::ACCEPTE)
            ->join('c.sessionUserData', 'sessionUserData')
            ->andWhere('sessionUserData.status LIKE :apprenti')
            ->setParameter('apprenti', Student::APPRENTI)
            ->getQuery()
            ->getResult();
    }

    public function getCursusApplicationsCount(Cursus $cursus)
    {
        return $this->createQueryBuilder('candidature')
            ->select('count(candidature.id)')
            ->join('candidature.cursus', 'cursus')
            ->where('cursus.id = :id')
            ->setParameter('id', $cursus->getId())
            ->getQuery()
            ->getSingleScalarResult();

    }

    public function countAcceptedCandidature()
    {
        return $this->createQueryBuilder('c')
            ->select('count(c.id)')
            ->where('c.status = :status')
            ->setParameter('status', Candidature::ACCEPTE)
            ->getQuery()
            ->getSingleScalarResult();

    }

    public function getCandidatureByApprentice(Student $student)
    {
        return $this->createQueryBuilder('c')
            ->join('c.cursus', 'cursus')
            ->where('c.candidat = :student')
            ->setParameter('student', $student)
            ->orderBy('c.datePostule', 'DESC')
            ->select('c.id', 'c.status', 'c.datePostule', 'c.currentSituation' ,'c.grades' , 'c.degree' , 'c.cv', 'c.linkLinkedin', 'c.itExperience', 'cursus.name as cursusName', 'cursus.id as cursusId')
            ->getQuery()
            ->getResult();
    }
}

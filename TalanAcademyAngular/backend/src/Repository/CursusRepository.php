<?php

namespace App\Repository;

use App\Entity\Cursus;
use App\Entity\DayCourse;
use App\Entity\Staff;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Cursus|null find($id, $lockMode = null, $lockVersion = null)
 * @method Cursus|null findOneBy(array $criteria, array $orderBy = null)
 * @method Cursus[]    findAll()
 * @method Cursus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CursusRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Cursus::class);
    }




    /**
     * @param $value
     * @return Cursus|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneById($value): ?Cursus
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.id = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function getCursusPendingValidationOfCandidate($candidate)
    {
        return $this
            ->createQueryBuilder('c')
            ->leftJoin('c.candidatures ', 'candidatures  ')
            ->leftJoin('candidatures.candidat', 'candidat')
            ->andWhere('candidat.id LIKE :candidat')
            ->setParameter('candidat', $candidate)
            ->getQuery()
            ->getOneOrNullResult();
    }


    public function CountSessionFromCursus()
    {
        return $this->createQueryBuilder('c')
            ->join('c.sessions', 'cs')
            ->select('c.name as cursusName,count(cs.id) as number')
            ->groupBy('c.id')
            ->getQuery()
            ->getResult();
    }

    public function countAll()
    {
        return $this->createQueryBuilder('c')
            ->select('count(c.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countForStaff(Staff $staff)
    {
        return $this->createQueryBuilder('c')
            ->join('c.staff', 'staff')
            ->select('count(c.id)')
            ->where('staff.id = :id')
            ->setParameter('id', $staff->getId())
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getOnlyCursusWithSessions()
    {
        return $this->createQueryBuilder('c')
            ->select('c')
            ->join('c.sessions', 's')
            ->where('c.visibility LIKE ')
            ->getQuery()
            ->getResult();
    }

    public function countDays(Cursus $cursus)
    {
        return $this->createQueryBuilder('c')
            ->select('c')
            ->join('c.modules', 'cm')
            ->join('cm.dayCourses', 'cmd')
            ->select('count(cmd.id)')
            ->where('c.id = :id')
            ->andWhere('cm.deleted IS NULL ')
            ->andWhere('cmd.deleted IS NULL ')
            ->setParameter('id', $cursus->getId())
            ->getQuery()
            ->getSingleScalarResult();
    }
    



    public function countDaysValidate(Cursus $cursus)
    {
        return $this->createQueryBuilder('c')
            ->select('c')
            ->join('c.modules', 'cm')
            ->join('cm.dayCourses', 'cmd')
            ->select('count(cmd.id)')
            ->where('c.id = :id')
            ->andWhere('cmd.status = :status')
            ->andWhere('cm.deleted IS NULL ')
            ->andWhere('cmd.deleted IS NULL ')
            ->setParameter('status', DayCourse::VALIDATING_DAY)
            ->setParameter('id', $cursus->getId())
            ->getQuery()
            ->getSingleScalarResult();
    }


    public function findCursusSessionFinished()
    {
        $query = $this->createQueryBuilder('c')
            ->join('c.sessions', 's')
            ->where(':today > s.endDate')
            ->setParameter('today', new \DateTime());

        return $query->getQuery()->getResult();
    }

    public function findCursusVisibleExceptCursus(Cursus $cursus)
    {
        return $this->createQueryBuilder('c')
            ->select('c')
            ->andWhere('c.id != :cursusId')
            ->andWhere('c.visibility = :visible')
            ->setParameter('cursusId', $cursus->getId())
            ->setParameter('visible', 'visible')
            ->getQuery()
            ->getResult();
    }

    public function findVisibleCursus()
    {
        return $this->createQueryBuilder('c')
            ->where('c.visibility = :visible')
            ->setParameter('visible',Cursus::VISIBLE)
            ->select('c.name','c.description','c.id', 'c.tags', 'c.daysNumber')
            ->getQuery()
            ->getResult();
    }


}

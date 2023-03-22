<?php

namespace App\Repository;

use App\Entity\DayCourse;
use App\Entity\Resources;
use App\Entity\Student;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Resources|null find($id, $lockMode = null, $lockVersion = null)
 * @method Resources|null findOneBy(array $criteria, array $orderBy = null)
 * @method Resources[]    findAll()
 * @method Resources[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResourcesRepository extends ServiceEntityRepository
{
  
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Resources::class);
    }

    public function countAll()
    {
        return $this->createQueryBuilder('r')
            ->select('count(r.id)')
            ->where('r.deleted IS NULL')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countResourceByDay(DayCourse $dayCourse)
    {
        return $this->createQueryBuilder('r')
            ->select('count(r.id)')
            ->join('r.day', 'rd')
            ->where('rd.id = :id')
            ->andWhere('r.status = :status')
            ->andWhere('r.deleted IS NULL')
            ->setParameters(['id' => $dayCourse->getId(), 'status' => Resources::APPROVED])
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countSuggestionByDay(DayCourse $dayCourse)
    {
        return $this->createQueryBuilder('r')
            ->select('count(r.id)')
            ->join('r.day', 'rd')
            ->andWhere('rd.id = :id')
            ->andWhere('r.deleted IS NULL')
            ->andWhere('r.status = :status')
            ->setParameters(['id' => $dayCourse->getId(), 'status' => Resources::TOAPPROVE])
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countSuggestionApprovedByDay(DayCourse $dayCourse)
    {
        return $this->createQueryBuilder('r')
            ->select('count(r.id)')
            ->join('r.day', 'rd')
            ->andWhere('rd.id = :id')
            ->andWhere('r.deleted IS NULL')
            ->andWhere('r.status = :status')
            ->join('r.resourceOwner', 'resourceOwner')
            ->andWhere('resourceOwner.roles LIKE :resourceOwner')
            ->setParameters(['id' => $dayCourse->getId(), 'status' => Resources::APPROVED, 'resourceOwner' => '%ROLE_APPRENTI%'])
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findResourcesProposed(Student $user, DayCourse $day)
    {
        return $this->createQueryBuilder('r')
            ->select('r')
            ->join('r.day', 'rd')
            ->where('rd.id LIKE :day')
            ->join('r.resourceOwner', 'resourceOwner')
            ->andWhere('resourceOwner.id LIKE :resourceOwner')
            ->andWhere('r.deleted IS NULL')
            ->setParameters(['day' => $day->getId(), 'resourceOwner' => $user->getId()])
            ->getQuery()
            ->getResult();
    }

    public function countTotalResourcesProposedByDay(DayCourse $day)
    {
        return $this->createQueryBuilder('r')
            ->select('count(r.id)')
            ->join('r.day', 'rd')
            ->where('rd.id LIKE :day')
            ->andWhere('r.deleted IS NULL')
            ->join('r.resourceOwner', 'resourceOwner')
            ->andWhere('resourceOwner.roles LIKE :resourceOwner')
            ->setParameters(['day' => $day->getId(), 'resourceOwner' => '%ROLE_APPRENTI%'])
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findResourcesOrdred(DayCourse $dayCourse)
    {
        return $this->createQueryBuilder('r')
            ->select('r')
            ->join('r.day', 'rd')
            ->where('rd.id LIKE :day')
            ->andWhere('r.deleted IS NULL')
            ->setParameter('day', $dayCourse->getId())
            ->orderBy('r.status', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function countAllSuggestionDay(DayCourse $dayCourse)
    {
        return $this->createQueryBuilder('r')
            ->select('count(r.id)')
            ->join('r.resourceOwner', 'resourceOwner')
            ->andWhere('resourceOwner.roles LIKE :role')
            ->join('r.day', 'rd')
            ->andWhere('rd.id = :id')
            ->andWhere('r.deleted IS NULL')
            ->setParameters(['id' => $dayCourse->getId(), 'role' => '%ROLE_APPRENTI%'])
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countSuggestionApproved(DayCourse $dayCourse)
    {
        return $this->createQueryBuilder('r')
            ->select('count(r.id)')
            ->andWhere('r.status LIKE :status')
            ->join('r.resourceOwner', 'resourceOwner')
            ->andWhere('resourceOwner.roles LIKE :role')
            ->join('r.day', 'rd')
            ->andWhere('rd.id = :id')
            ->andWhere('r.deleted IS NULL')
            ->setParameters(['id' => $dayCourse->getId(), 'role' => '%ROLE_APPRENTI%', 'status' => Resources::APPROVED])
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getProposedResources()
    {
        return $this->createQueryBuilder('r')
            ->join('r.day', 'rd')
            ->join('rd.module', 'module')
            ->join('module.courses', 'courses')
            ->join('r.resourceOwner', 'resourceOwner')
            ->andWhere('resourceOwner.roles LIKE :resourceOwner')
            ->setParameters(['resourceOwner' => '%ROLE_APPRENTI%'])
            ->select('courses.name as cursus', 'rd.id as day', 'r.title as titre', 'r.url as url', 'resourceOwner.firstName as owner', 'resourceOwner.lastName')
            ->orderBy('courses.id')
            ->getQuery()
            ->getResult();
    }

    public function getProposedResourcesByApprentice(User $user)
    {
        return $this->createQueryBuilder('r')
            ->select('r')
            ->join('r.day', 'rd')
            ->join('rd.module', 'm')
            ->join('r.resourceOwner', 'resourceOwner')
            ->where('resourceOwner.id LIKE :resourceOwner')
            ->andWhere('r.deleted IS NULL')
            ->setParameter('resourceOwner', $user->getId())
            ->select('r.id', 'r.url', 'r.title as title ', 'r.comment',  'r.status', 'm.title as module', 'rd.description as lesson', 'rd.id as dayId')
            ->getQuery()
            ->getResult();
    }

    public function getCountProposedResourcesByApprentice(User $user)
    {
        return $this->createQueryBuilder('r')
            ->select('count(r.id)')
            ->join('r.day', 'rd')
            ->join('r.resourceOwner', 'resourceOwner')
            ->where('resourceOwner.id LIKE :resourceOwner')
            ->andWhere('r.deleted IS NULL')
            ->setParameter('resourceOwner', $user->getId())
            ->getQuery()
            ->getSingleScalarResult();
    }

}

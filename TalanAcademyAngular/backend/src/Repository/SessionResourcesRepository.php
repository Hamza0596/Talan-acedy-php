<?php

namespace App\Repository;

use App\Entity\SessionDayCourse;
use App\Entity\SessionResources;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SessionResources|null find($id, $lockMode = null, $lockVersion = null)
 * @method SessionResources|null findOneBy(array $criteria, array $orderBy = null)
 * @method SessionResources[]    findAll()
 * @method SessionResources[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SessionResourcesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SessionResources::class);
    }
    public function countAll()
    {
        return $this->createQueryBuilder('r')
            ->select('count(r.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countResourceByDay(SessionDayCourse $dayCourse)
    {
        return $this->createQueryBuilder('r')
            ->select('count(r.id)')
            ->join('r.day', 'rd')
            ->where('rd.id = :id')
            ->setParameter('id',$dayCourse->getId())
            ->getQuery()
            ->getSingleScalarResult();
    }
}

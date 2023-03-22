<?php

namespace App\Repository;

use App\Entity\DayInterface;
use App\Entity\SessionDayCourse;
use App\Entity\SessionOrder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SessionOrder|null find($id, $lockMode = null, $lockVersion = null)
 * @method SessionOrder|null findOneBy(array $criteria, array $orderBy = null)
 * @method SessionOrder[]    findAll()
 * @method SessionOrder[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SessionOrderRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SessionOrder::class);
    }


    public function countOrderByDay(SessionDayCourse $dayCourse)
    {
        return $this->createQueryBuilder('oc')
            ->join('oc.dayCourse','ocd')
            ->where('ocd.id = :id')
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
            ->setParameter('id',$day)
            ->select('count(oc.id)')
            ->getQuery()
            ->getSingleScalarResult();

    }
}

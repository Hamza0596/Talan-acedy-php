<?php

namespace App\Repository;

use App\Entity\DayCourse;
use App\Entity\Module;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method DayCourse|null find($id, $lockMode = null, $lockVersion = null)
 * @method DayCourse|null findOneBy(array $criteria, array $orderBy = null)
 * @method DayCourse[]    findAll()
 * @method DayCourse[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DayCourseRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, DayCourse::class);
    }

    /**
     * @param $id
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function nbDay($id)
    {
        return $this->createQueryBuilder('dc')

            ->select('count(dc.id)')
            ->where('dc.module = :idModule')
            ->andWhere('dc.deleted IS NULL')
            ->setParameter('idModule', $id)
            ->getQuery()->getSingleScalarResult();
    }

    public function countAll()
    {
        return $this->createQueryBuilder('dc')
            ->select('count(dc.id)')
            ->where('dc.deleted IS NULL')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findDaysOrdred(Module $module)
    {
        return $this->createQueryBuilder('dc')
            ->select('dc')
            ->join('dc.module', 'module')
            ->where('module.id LIKE :module')
            ->setParameter('module', $module->getId())
            ->andWhere('dc.deleted IS NULL')
            ->orderBy('dc.ordre')
            ->getQuery()
            ->getResult();
    }

    public function findDaysWithModuleBetweenTwoOrders($module, $first, $end)
    {
        return $this->createQueryBuilder('dc')
            ->select('dc')
            ->join('dc.module', 'module')
            ->where('module.id LIKE :module')
            ->andWhere('dc.ordre BETWEEN :first AND :end')
            ->andWhere('dc.deleted IS NULL')
            ->setParameter('module', $module)
            ->setParameter('first', $first)
            ->setParameter('end', $end)
            ->orderBy('dc.ordre')
            ->getQuery()
            ->getResult();
    }

    public function getOrdredDayCoursesByModule(Module $module){
        return $this->createQueryBuilder('dc')
            ->select('dc')
            ->join('dc.module', 'module')
            ->where('module = :module')
            ->andWhere('dc.deleted IS NULL')
            ->setParameter('module',$module)
            ->orderBy('dc.ordre', 'ASC')
            ->getQuery()
            ->getResult();
    }


}

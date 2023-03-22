<?php

namespace App\Repository;

use App\Entity\Cursus;
use App\Entity\DayCourse;
use App\Entity\Module;
use App\Entity\ModuleInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Module|null find($id, $lockMode = null, $lockVersion = null)
 * @method Module|null findOneBy(array $criteria, array $orderBy = null)
 * @method Module[]    findAll()
 * @method Module[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ModuleRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Module::class);
    }

    public function countAll()
    {
        return $this->createQueryBuilder('m')
            ->select('count(m.id)')
            ->where('m.deleted IS NULL')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countDays(ModuleInterface $module)
    {
        return $this->createQueryBuilder('m')
            ->join('m.dayCourses', 'md')
            ->select('count(md.id)')
            ->where('m.id=:id')
            ->andWhere('md.deleted IS NULL')
            ->setParameter('id', $module->getId())
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countDaysValidate(ModuleInterface $module)
    {
        return $this->createQueryBuilder('m')
            ->join('m.dayCourses', 'md')
            ->select('count(md.id)')
            ->where('m.id=:id')
            ->setParameter('id', $module->getId())
            ->andWhere('md.status = :status')
            ->setParameter('status', DayCourse::VALIDATING_DAY)
            ->andWhere('md.deleted IS NULL')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countDaysValidateWithoutInstruction(ModuleInterface $module)
    {
        $validDaysNumber = $this->createQueryBuilder('m')
            ->join('m.dayCourses', 'md')
            ->select('count(md.id)')
            ->where('m.id=:id')
            ->setParameter('id', $module->getId())
            ->andWhere('md.status = :status')
            ->setParameter('status', DayCourse::VALIDATING_DAY)
            ->andWhere('md.deleted IS NULL')
            ->getQuery()
            ->getSingleScalarResult();
        $countDaysValidateWithInstruction = $this->createQueryBuilder('m')
            ->join('m.dayCourses', 'md')
            ->leftJoin('md.orders', 'mdo')
            ->select('count(DISTINCT mdo.dayCourse)')
            ->where('m.id=:id')
            ->setParameter('id', $module->getId())
            ->andWhere('md.status = :status')
            ->setParameter('status', DayCourse::VALIDATING_DAY)
            ->andWhere('md.deleted IS NULL')
            ->getQuery()
            ->getSingleScalarResult();
        return $validDaysNumber - $countDaysValidateWithInstruction;
    }

    public function countByCursus(Cursus $courses)
    {
        return $this->createQueryBuilder('m')
            ->join('m.courses', 'mc')
            ->select('count(mc.id)')
            ->where('mc.id=:id')
            ->andWhere('m.deleted IS NULL')
            ->setParameter('id', $courses->getId())
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findModulesWithCursusBetweenTwoOrders($cursus, $first, $end)
    {
        return $this->createQueryBuilder('module')
            ->select('module')
            ->join('module.courses', 'cursus')
            ->where('cursus.id LIKE :cursus')
            ->andWhere('module.orderModule BETWEEN :first AND :end')
            ->andWhere('module.deleted IS NULL')
            ->setParameter('cursus', $cursus)
            ->setParameter('first', $first)
            ->setParameter('end', $end)
            ->orderBy('module.orderModule')
            ->getQuery()
            ->getResult();
    }
    public function findNonDeletedModule($cursus){
        return $this->createQueryBuilder('module')
            ->join('module.courses','cursus')
            ->where('cursus.id LIKE :cursus')
            ->setParameter('cursus', $cursus->getId())
            ->andWhere('module.deleted IS NULL')
            ->getQuery()
            ->getResult();
    }
}

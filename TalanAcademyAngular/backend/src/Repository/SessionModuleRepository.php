<?php

namespace App\Repository;

use App\Entity\Module;
use App\Entity\ModuleInterface;
use App\Entity\Session;
use App\Entity\SessionDayCourse;
use App\Entity\SessionModule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SessionModule|null find($id, $lockMode = null, $lockVersion = null)
 * @method SessionModule|null findOneBy(array $criteria, array $orderBy = null)
 * @method SessionModule[]    findAll()
 * @method SessionModule[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SessionModuleRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SessionModule::class);
    }

    public function countAll()
    {
        return $this->createQueryBuilder('m')
            ->select('count(m.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countDays(ModuleInterface $module)
    {
        return $this->createQueryBuilder('m')
            ->join('m.DayCourses', 'md')
            ->select('count(md.id)')
            ->where('m.id=:id')
            ->setParameter('id', $module->getId())
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countDaysValidate(ModuleInterface $module)
    {
        return $this->createQueryBuilder('m')
            ->join('m.DayCourses', 'md')
            ->select('count(md.id)')
            ->where('m.id=:id')
            ->setParameter('id', $module->getId())
            ->andWhere('md.status = :status')
            ->setParameter('status', SessionDayCourse::VALIDATING_DAY)
            ->getQuery()
            ->getSingleScalarResult();
    }
    public function countDaysValidateWithoutInstruction(ModuleInterface $module)
    {
        $validDaysNumber = $this->createQueryBuilder('m')
            ->join('m.DayCourses','md')
            ->select('count(md.id)')
            ->where('m.id=:id')
            ->setParameter('id',$module->getId())
            ->andWhere('md.status = :status')
            ->setParameter('status',SessionDayCourse::VALIDATING_DAY)
            ->getQuery()
            ->getSingleScalarResult();
        $countDaysValidateWithInstruction = $this->createQueryBuilder('m')
            ->join('m.DayCourses', 'md')
            ->leftJoin('md.orders', 'mdo')
            ->select('count(DISTINCT mdo.dayCourse)')
            ->where('m.id=:id')
            ->setParameter('id', $module->getId())
            ->andWhere('md.status = :status')
            ->setParameter('status', SessionDayCourse::VALIDATING_DAY)
            ->getQuery()
            ->getSingleScalarResult();
        return $validDaysNumber - $countDaysValidateWithInstruction;
    }

    public function countBySession(Session $session)
    {
        return $this->createQueryBuilder('m')
            ->join('m.session', 's')
            ->select('count(m.id)')
            ->where('s.id=:id')
            ->setParameter('id', $session->getId())
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findModulesSession(Session $session)
    {
        return $this->createQueryBuilder('sm')
            ->select('sm')
            ->join('sm.session', 'session')
            ->where('session.id LIKE :session')
            ->andWhere('sm.type = :type')
            ->setParameters(['session'=> $session->getId(), 'type'=>Module::MODULE])
            ->orderBy('sm.orderModule')
            ->getQuery()
            ->getResult();
    }
}

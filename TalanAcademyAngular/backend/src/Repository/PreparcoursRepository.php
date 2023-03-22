<?php

namespace App\Repository;

use App\Entity\Preparcours;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Preparcours|null find($id, $lockMode = null, $lockVersion = null)
 * @method Preparcours|null findOneBy(array $criteria, array $orderBy = null)
 * @method Preparcours[]    findAll()
 * @method Preparcours[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PreparcoursRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Preparcours::class);
    }

}

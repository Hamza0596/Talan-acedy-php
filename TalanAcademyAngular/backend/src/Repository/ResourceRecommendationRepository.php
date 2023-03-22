<?php

namespace App\Repository;

use App\Entity\ResourceRecommendation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method ResourceRecommendation|null find($id, $lockMode = null, $lockVersion = null)
 * @method ResourceRecommendation|null findOneBy(array $criteria, array $orderBy = null)
 * @method ResourceRecommendation[]    findAll()
 * @method ResourceRecommendation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResourceRecommendationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ResourceRecommendation::class);
    }


    public function sumResourceScore($value)
    {
        return $this->createQueryBuilder('r')
            ->join('r.resource','resource')
            ->select('sum(r.score)')
            ->andWhere('resource.ref = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

}

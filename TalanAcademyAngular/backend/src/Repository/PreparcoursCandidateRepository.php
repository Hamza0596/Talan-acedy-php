<?php

namespace App\Repository;

use App\Entity\PreparcoursCandidate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method PreparcoursCandidate|null find($id, $lockMode = null, $lockVersion = null)
 * @method PreparcoursCandidate|null findOneBy(array $criteria, array $orderBy = null)
 * @method PreparcoursCandidate[]    findAll()
 * @method PreparcoursCandidate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PreparcoursCandidateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PreparcoursCandidate::class);
    }

}

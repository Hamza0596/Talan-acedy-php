<?php

namespace App\Repository;

use App\Entity\Candidature;
use App\Entity\CandidatureState;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CandidatureState|null find($id, $lockMode = null, $lockVersion = null)
 * @method CandidatureState|null findOneBy(array $criteria, array $orderBy = null)
 * @method CandidatureState[]    findAll()
 * @method CandidatureState[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CandidatureStateRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CandidatureState::class);
    }

}

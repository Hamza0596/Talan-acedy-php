<?php

namespace App\Repository;

use App\Entity\ProjectSubject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ProjectSubject|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProjectSubject|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProjectSubject[]    findAll()
 * @method ProjectSubject[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjectSubjectRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ProjectSubject::class);
    }

}

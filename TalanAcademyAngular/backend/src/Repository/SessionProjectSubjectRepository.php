<?php

namespace App\Repository;

use App\Entity\SessionProjectSubject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SessionProjectSubject|null find($id, $lockMode = null, $lockVersion = null)
 * @method SessionProjectSubject|null findOneBy(array $criteria, array $orderBy = null)
 * @method SessionProjectSubject[]    findAll()
 * @method SessionProjectSubject[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SessionProjectSubjectRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SessionProjectSubject::class);
    }

}

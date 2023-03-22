<?php

namespace App\Repository;

use App\Entity\SessionProjecSubject;
use App\Entity\SessionProjectSubject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SessionProjecSubject|null find($id, $lockMode = null, $lockVersion = null)
 * @method SessionProjecSubject|null findOneBy(array $criteria, array $orderBy = null)
 * @method SessionProjecSubject[]    findAll()
 * @method SessionProjecSubject[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @codeCoverageIgnore
 */
class SessionProjecSubjectRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SessionProjectSubject::class);
    }

}

<?php

namespace App\Repository;

use App\Entity\SubmissionWorks;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SubmissionWorks|null find($id, $lockMode = null, $lockVersion = null)
 * @method SubmissionWorks|null findOneBy(array $criteria, array $orderBy = null)
 * @method SubmissionWorks[]    findAll()
 * @method SubmissionWorks[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SubmissionWorksRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SubmissionWorks::class);
    }
}

<?php

namespace App\Repository;

use App\Entity\MentorsAppreciation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MentorsAppreciation|null find($id, $lockMode = null, $lockVersion = null)
 * @method MentorsAppreciation|null findOneBy(array $criteria, array $orderBy = null)
 * @method MentorsAppreciation[]    findAll()
 * @method MentorsAppreciation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MentorsAppreciationRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MentorsAppreciation::class);
    }
}

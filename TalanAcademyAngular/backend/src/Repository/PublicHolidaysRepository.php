<?php

namespace App\Repository;

use App\Entity\PublicHolidays;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PublicHolidays|null find($id, $lockMode = null, $lockVersion = null)
 * @method PublicHolidays|null findOneBy(array $criteria, array $orderBy = null)
 * @method PublicHolidays[]    findAll()
 * @method PublicHolidays[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PublicHolidaysRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PublicHolidays::class);
    }


    /**
     * @return PublicHolidays[] Returns an array of PublicHolidays objects
     */
    public function findHolidaysWithoutDate()
    {
        $req = $this->createQueryBuilder('p')
            ->select('IDENTITY(y.holidays)')
            ->join('p.yearPublicHolidays', 'y');
        $req2 = $this->createQueryBuilder('p1');
        $req2->andWhere($req2->expr()->notIn('p1.id', $req->getDQL()));
        return $req2->getQuery()->getArrayResult();
    }

}

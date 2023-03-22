<?php

namespace App\Repository;

use App\Entity\YearPublicHolidays;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method YearPublicHolidays|null find($id, $lockMode = null, $lockVersion = null)
 * @method YearPublicHolidays|null findOneBy(array $criteria, array $orderBy = null)
 * @method YearPublicHolidays[]    findAll()
 * @method YearPublicHolidays[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class YearPublicHolidaysRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, YearPublicHolidays::class);
    }

    public function findHolidaysByInterval($start, $end)
    {
        return $this->createQueryBuilder('y')
            ->join('y.holidays', 'h')
            ->addSelect('h')
            ->andWhere('y.date <= :end')
            ->andWhere('y.date >= :start')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getArrayResult();
    }

    public function getDate()
    {
        return $this->createQueryBuilder('y')
            ->select('y.date')
            ->getQuery()
            ->getArrayResult();

    }

    public function findHolidaysByStart($start)
    {
        return $this->createQueryBuilder('y')
            ->addSelect('y.date')
            ->andWhere('y.date >= :start')
            ->setParameter('start', $start)
            ->getQuery()
            ->getArrayResult();
    }

    public function findHolidaysByPreviousYear(){

        return $this->createQueryBuilder('y')
            ->join('y.holidays', 'holidays')
            ->select('y.date','holidays.id')
            ->andWhere('holidays.date IS NOT NULL')
            ->andWhere('YEAR(y.date) like :year')
            ->setParameter('year', date('Y')-1 )
            ->getQuery()
            ->getArrayResult();
    }

}

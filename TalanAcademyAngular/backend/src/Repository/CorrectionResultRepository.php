<?php

namespace App\Repository;

use App\Entity\CorrectionResult;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CorrectionResult|null find($id, $lockMode = null, $lockVersion = null)
 * @method CorrectionResult|null findOneBy(array $criteria, array $orderBy = null)
 * @method CorrectionResult[]    findAll()
 * @method CorrectionResult[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @codeCoverageIgnore
 */
class CorrectionResultRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CorrectionResult::class);
    }

    public function findOrdersByCorrection($value)
    {
        return $this->createQueryBuilder('c')
            ->select('c', 'o')
            ->andWhere('c.correction = :val')
            ->setParameter('val', $value)
            ->join('c.orderCourse', 'o')
            ->getQuery()
            ->getResult();
    }

    public function findResultByOrderAndCorrected($order, $corrected)
    {
        return $this->createQueryBuilder('c')
            ->select('c.result')
            ->join('c.correction', 'correction')
            ->andWhere('c.orderCourse = :order')
            ->setParameter('order', $order)
            ->andWhere('correction.corrected = :corrected')
            ->setParameter('corrected', $corrected)
            ->getQuery()
            ->getResult();
    }

    public function checkCorrectedCorrection($corrections)
    {
        $req = $this->createQueryBuilder('c')
            ->select('
           c as results,
           corr as corrections')
            ->innerJoin('c.correction', "corr");

        $orStatementsCorrections = $req->expr()->orX();
        foreach ($corrections as $correction) {
            $orStatementsCorrections->add(
                $req->expr()->eq('corr.id', $correction)
            );
        }
        $req->andWhere($orStatementsCorrections)
            ->andWhere('c.result = :emptyString')
            ->setParameter('emptyString', '');

        return $req->getQuery()
            ->getArrayResult();
    }

}

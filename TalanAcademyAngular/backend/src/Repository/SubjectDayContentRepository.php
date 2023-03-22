<?php

namespace App\Repository;

use App\Entity\SubjectDayContent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SubjectDayContent|null find($id, $lockMode = null, $lockVersion = null)
 * @method SubjectDayContent|null findOneBy(array $criteria, array $orderBy = null)
 * @method SubjectDayContent[]    findAll()
 * @method SubjectDayContent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SubjectDayContentRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SubjectDayContent::class);
    }

    public function getSubjectsFromContent($sessionDay)
    {
        return $this->createQueryBuilder('s')
            ->innerJoin('s.subject','subject')
            ->select('subject.id')
            ->andWhere('s.sessionDay = :sessionDay')
            ->setParameter('sessionDay', $sessionDay)
            ->getQuery()
            ->getArrayResult()
            ;
    }

    public function getCurrentSubjectDayContent($dayCourse,$subject)

    {
        return $this->createQueryBuilder('s')
            ->where('s.sessionDay = :sessionDay')
            ->andWhere('s.subject = :subject')
            ->setParameters(['sessionDay'=>$dayCourse,'subject'=>$subject])
            ->getQuery()
            ->getResult();
    }
}

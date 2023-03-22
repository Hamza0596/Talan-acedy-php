<?php

namespace App\Repository;

use App\Entity\Session;
use App\Entity\SessionJokerCheck;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SessionJokerCheck|null find($id, $lockMode = null, $lockVersion = null)
 * @method SessionJokerCheck|null findOneBy(array $criteria, array $orderBy = null)
 * @method SessionJokerCheck[]    findAll()
 * @method SessionJokerCheck[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @codeCoverageIgnore
 */
class SessionJokerCheckRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SessionJokerCheck::class);
    }

    public function findBySessionAndDate(Session $session, $date){
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT j
                 FROM App\Entity\SessionJokerCheck j
                 WHERE j.sessionJokerCheck = :session
                 and (j.average = :date or j.submittedWork = :date or j.correction = :date)'
        )
            ->setParameter('session', $session)
            ->setParameter('date',$date);
        return $query->execute();
    }
}

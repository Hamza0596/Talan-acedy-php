<?php

namespace App\Repository;

use App\Entity\Session;
use App\Entity\Staff;
use App\Entity\Student;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Student|null find($id, $lockMode = null, $lockVersion = null)
 * @method Student|null findOneBy(array $criteria, array $orderBy = null)
 * @method Student[]    findAll()
 * @method Student[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StudentRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Student::class);
    }

    public function counts()
    {
        return $this
            ->createQueryBuilder('s')
            ->select("count(s.id)")
            ->andWhere('s.roles LIKE :role')
            ->setParameter('role', '%' . User::ROLE_INSCRIT . '%')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getRegistredData($start, $length, $orders, $search, $columns)
    {
        $query = $this->createQueryBuilder('s')
            ->andWhere('s.status IS NULL')
            ->andWhere('s.roles LIKE :role')
            ->setParameter('role', '%' . User::ROLE_INSCRIT . '%');

        if ($search['value'] != '') {
            $query
                ->andWhere('s.firstName LIKE :val OR s.lastName LIKE :val OR s.email LIKE :val ')
                ->setParameter('val', '%' . trim($search['value']) . '%');
        }
        $countResult = count($query->getQuery()->getResult());
        $query->setFirstResult($start)->setMaxResults($length);

        // Order
        foreach ($orders as $order) {
            if ($order['name'] != '') {
                $orderColumn = null;

                switch ($order['name']) {
                    case 'firstName':
                        {
                            $orderColumn = 's.firstName';
                            break;
                        }
                    case 'lastName':
                        {
                            $orderColumn = 's.lastName';
                            break;
                        }
                    case 'email':
                        {
                            $orderColumn = 's.email';
                            break;
                        }
                    default:
                        break;
                }

                if ($orderColumn !== null) {
                    $query->orderBy($orderColumn, $order['dir']);
                }
            }
        }

        $results = $query->getQuery()->getResult();


        return array(
            "results" => $results,
            "countResult" => $countResult
        );
    }

    public function findAllCandidateByRole($role = null)
    {
        return $this->createQueryBuilder('s')
            ->select('count(s.id)')
            ->where('s.roles LIKE :roles')
            ->setParameter('roles', '%"' . $role . '"%')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findCandidateByRoleAndSession(Session $session, $role)
    {
        return $this->createQueryBuilder('s')
            ->select('s')
            ->join('s.session', 'session')
            ->andWhere('session = :session')
            ->andWhere('s.roles LIKE :role')
            ->setParameter('role', '%' . $role . '%')
            ->setParameter('session', $session)
            ->getQuery()
            ->getResult();
    }

    public function getAllApprentis()
    {
        return $this
            ->createQueryBuilder('s')
            ->select("count(s.id)")
            ->where('s.roles LIKE :roles')
            ->setParameter('roles', '%ROLE_APPRENTI%')
            ->getQuery()
            ->getSingleScalarResult();
    }
    public function getAllCorsair()
    {
        return $this
            ->createQueryBuilder('s')
            ->select("count(s.id)")
            ->where('s.roles LIKE :roles')
            ->setParameter('roles', '%ROLE_CORSAIRE%')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findApprentisBySession(Session $session)
    {
        return $this->createQueryBuilder('s')
            ->select('s')
            ->leftJoin('s.sessionUserDatas', 'sessionUserDatas')
            ->join('sessionUserDatas.session', 'session')
            ->where('session.id LIKE :session')
            ->setParameter('session', $session->getId())
            ->getQuery()
            ->getResult();
    }
}

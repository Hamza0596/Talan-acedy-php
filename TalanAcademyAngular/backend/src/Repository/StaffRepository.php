<?php

namespace App\Repository;

use App\Entity\Staff;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Staff|null find($id, $lockMode = null, $lockVersion = null)
 * @method Staff|null findOneBy(array $criteria, array $orderBy = null)
 * @method Staff[]    findAll()
 * @method Staff[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StaffRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Staff::class);
    }

    public function findStaffBySearchfield($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.status IS NULL')
            ->andWhere('s.firstName LIKE :val OR s.lastName LIKE :val OR s.email LIKE :val OR s.function LIKE :val OR s.status LIKE :val')
            ->setParameter('val', '%' . $value . '%')
            ->getQuery()
            ->getResult();
    }

    public function counts()
    {
        return $this
            ->createQueryBuilder('s')
            ->select("count(s.id)")
            ->andWhere('s.status IS NULL')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getRequiredData($start, $length, $orders, $search, $columns, $extraSearch)
    {
        $query = $this->createQueryBuilder('s')
            ->andWhere('s.status IS NULL')
            ->leftJoin('s.cursus', 'c');

        if ($search['value'] != '') {
            $query
                ->andWhere('s.firstName LIKE :val OR s.lastName LIKE :val OR s.email LIKE :val OR s.function LIKE :val OR c.name LIKE :val ')
                ->setParameter('val', '%' . trim($search['value']) . '%');
        }
        if(!is_null($extraSearch) && is_array($extraSearch)){
            //vérification par chaque champ du filtre
            foreach ($extraSearch as $searchingField){
                switch ($searchingField['name']){
                    case 'lastName' : $query->andWhere('s.firstName like :lastName')->setParameter('lastName', '%'.$searchingField['value'].'%'); break;
                    case 'firstName' : $query->andWhere('s.lastName like :firstName')->setParameter('firstName', '%'.$searchingField['value'].'%'); break;
                    case 'email' : $query->andWhere('s.email like :email')->setParameter('email', '%'.$searchingField['value'].'%'); break;
                    case 'function' : $query->andWhere('s.function like :function')->setParameter('function', '%'.$searchingField['value'].'%'); break;
                    case 'name' : $query->andWhere('c.name like :name')->setParameter('name', '%'.$searchingField['value'].'%'); break;

                }
            }
        }
        $countResult=count($query->getQuery()->getResult());
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
                    case 'function':
                        {
                            $orderColumn = 's.function';
                            break;
                        }
                    case 'cursus':
                        {
                            $orderColumn = 's.cursus';
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

    public function findStaffsByCursusAndRoles($cursus, $role)
    {
        return $this->createQueryBuilder('s')
            ->select('s')
            ->join('s.cursus', 'c')
            ->andWhere('c = :cursus')
            ->andWhere('s.roles LIKE :role')
            ->setParameter('role', '%' . $role . '%')
            ->setParameter('cursus', $cursus)
            ->getQuery()
            ->getResult();
    }

    public function getCursusOfMentor(Staff $mentor)
    {
        return $this->createQueryBuilder('s')
            ->select('s')
            ->join('s.cursus', 'c')
            ->where('s.id LIKE :mentor')
            ->setParameter('mentor', $mentor->getId())
            ->select('count(c.id)')
            ->getQuery()
            ->getSingleScalarResult();

    }


}

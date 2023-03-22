<?php

namespace App\Repository;

use App\Entity\SessionMentor;
use App\Entity\SessionUserData;
use App\Entity\Staff;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SessionMentor|null find($id, $lockMode = null, $lockVersion = null)
 * @method SessionMentor|null findOneBy(array $criteria, array $orderBy = null)
 * @method SessionMentor[]    findAll()
 * @method SessionMentor[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SessionMentorRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SessionMentor::class);
    }

    public function getNbrSessionForMentor($val)
    {
        return $this->createQueryBuilder('s')
            ->select('count(s.id)')
            ->join('s.session', 'ss')
            ->join('s.mentor', 'sm')
            ->andWhere('ss.endDate < :today')
            ->andWhere('sm.id = :val')
            ->andWhere('s.status = :status')
            ->setParameter('today', new \DateTime())
            ->setParameter('val', $val)
            ->setParameter('status', SessionMentor::ACTIVE)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getNbrOnRenegadePerMentorSessions(Staff $user)
    {
     return $this->createQueryBuilder('s')
         ->select('count(s)')
         ->innerJoin('s.session','ss')
         ->innerJoin('ss.sessionUserDatas', 'sessionUserDatas')
         ->andWhere('s.mentor = :idMentor')
         ->andWhere('sessionUserDatas.status LIKE :renegat')
         ->setParameter('idMentor', $user->getId())
         ->setParameter('renegat', SessionUserData::ELIMINATED)
         ->getQuery()->getSingleScalarResult();
    }
    public function getCorsairForMentor(Staff $user)
    {
     return $this->createQueryBuilder('s')
         ->select('count(s)')
         ->innerJoin('s.session','ss')
         ->innerJoin('ss.sessionUserDatas', 'sessionUserDatas')
         ->andWhere('s.mentor = :idMentor')
         ->andWhere('sessionUserDatas.status = \'corsair\'')
         ->setParameter('idMentor', $user->getId())
         ->getQuery()->getSingleScalarResult();
    }

    public function getNbrSessionPlannedForMentor($val)
    {
        return $this->createQueryBuilder('s')
            ->select('count(s.id)')
            ->join('s.session', 'ss')
            ->join('s.mentor', 'sm')
            ->andWhere('ss.startDate > :today')
            ->andWhere('sm.id = :val')
            ->andWhere('s.status = :status')
            ->setParameter('today', new \DateTime())
            ->setParameter('val', $val)
            ->setParameter('status', SessionMentor::ACTIVE)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getNbrSessionFinishedForMentor($val)
    {
        $today=new \DateTime();
        return $this->createQueryBuilder('s')
            ->select('count(s.id)')
            ->join('s.session', 'ss')
            ->join('s.mentor', 'sm')
            ->andWhere(':today > ss.endDate')
            ->andWhere('sm.id = :val')
            ->andWhere('s.status = :status')
            ->setParameter('today',  $today->setTime(00, 00, 00))
            ->setParameter('val', $val)
            ->setParameter('status', SessionMentor::ACTIVE)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getNbrSessionInProgressForMentor($val)
    {
        $today=new \DateTime();
        return $this->createQueryBuilder('s')
            ->select('count(s.id)')
            ->join('s.session', 'ss')
            ->join('s.mentor', 'sm')
            ->andWhere(':today BETWEEN ss.startDate AND ss.endDate')
            ->andWhere('sm.id = :val')
            ->andWhere('s.status = :status')
            ->setParameter('today',  $today->setTime(00, 00, 00))
            ->setParameter('val', $val)
            ->setParameter('status', SessionMentor::ACTIVE)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getAllMentorSession($params)
    {
        $query = $this->createQueryBuilder('session_mentor')
            ->join('session_mentor.mentor', 'mentor')
            ->join('session_mentor.session', 'session')
            ->join('session.cursus', 'cursus')
            ->where('mentor.id = :mentorId')
            ->andWhere('session_mentor.status = :status')
            ->setParameters(['mentorId' => $params[4], 'status' => SessionMentor::ACTIVE])
            ->select('session.id', 'session.startDate','session.endDate','session.ordre', 'cursus.name', 'session.moy')
            ->orderBy('session.startDate', 'DESC');
        if (!is_null($params[3]) && $params[3]['value'] != '') {
            $query
                ->andWhere('cursus.name LIKE :val OR session.startDate LIKE :val OR DATE_FORMAT(session.startDate, \'%d-%m-%Y\') LIKE :val OR session.moy LIKE :val OR session.ordre LIKE :val')
                ->setParameter('val', '%' . trim($params[3]['value']) . '%');
        }
        $countResult = count($query->getQuery()->getResult());
        if($params[1]!=-1) {
            $query->setFirstResult($params[0])->setMaxResults($params[1]);
        }

        // Order
        foreach ($params[2] as $order) {
            if ($order['name'] != '') {
                $orderColumn = null;

                switch ($order['name']) {
                    case 'Session':
                        {
                            $orderColumn = 'cursus.name';
                            break;
                        }


                    case 'Date de début':
                        {
                            $orderColumn = 'session.startDate';
                            break;
                        }
                    case 'Moyenne':
                        {
                            $orderColumn = 'session.moy';
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

        if(!is_null($params[5]) && is_array($params[5])){
            //vérification par chaque champ du filtre
            foreach ($params[5] as $searchingField){
                switch ($searchingField['name']){
                    case 'cursus' : $query->andWhere('cursus.name like :session')->setParameter('session', '%'.$searchingField['value'].'%'); break;
                    case 'session' :
                        if(intval($searchingField['value'])) {
                            $query->andWhere('session.ordre like :ordre')->setParameter('ordre', $searchingField['value']);
                            break;
                        }
                    case 'startDate' : $query->andWhere('session.startDate like :startDate OR DATE_FORMAT(session.startDate, \'%d-%m-%Y\') LIKE :startDate')->setParameter('startDate', $searchingField['value']); break;
                }
            }
        }


        $results = $query->getQuery()->getResult();
        return array(
            "results" => $results,
            "countResult" => $countResult
        );

    }

    public function countAllMentorSession($staff)
    {
        return $this->createQueryBuilder('session_mentor')
            ->join('session_mentor.mentor', 'mentor')
            ->join('session_mentor.session', 'session')
            ->join('session.cursus', 'cursus')
            ->where('mentor.id = :mentorId')
            ->andWhere('session_mentor.status = :status')
            ->setParameters(['mentorId' => $staff, 'status' => SessionMentor::ACTIVE])
            ->select('count(session.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}

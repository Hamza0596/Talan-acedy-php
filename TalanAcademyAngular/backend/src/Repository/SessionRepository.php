<?php

namespace App\Repository;

use App\Entity\Cursus;
use App\Entity\Session;
use App\Entity\SessionDayCourse;
use App\Entity\SessionMentor;
use App\Entity\SessionUserData;
use App\Entity\Staff;
use App\Entity\Student;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @method Session|null find($id, $lockMode = null, $lockVersion = null)
 * @method Session|null findOneBy(array $criteria, array $orderBy = null)
 * @method Session[]    findAll()
 * @method Session[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SessionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Session::class);
    }

    public function countAll()
    {
        return $this->createQueryBuilder('s')
            ->select('count(s.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findSessionsByCursus(Cursus $cursus)
    {
        return $this->createQueryBuilder('s')
            ->select('s.startDate')
            ->where('s.cursus = :cursus')
            ->setParameter('cursus', $cursus)
            ->getQuery()
            ->getArrayResult();
    }

    public function findSessionByMentor(Staff $staff, $sessionId)
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.sessionUserDatas', 'sessionUserDatas')
            ->join('s.sessionMentors', 'sessionMentors')
            ->join('s.cursus', 'sc')
            ->join('sc.staff', 'scStaff')
            ->leftJoin('sessionUserDatas.user', 'apprentis')
            ->where('scStaff.id = :idStaff')
            ->setParameter('idStaff', $staff->getId())
            ->andWhere('sc.id = :idCursus')
            ->andWhere('s.id = :sessionId')
            ->setParameter('sessionId', $sessionId)
            ->setParameter('idCursus', $staff->getCursus()->getId())
            ->andWhere('sessionMentors.status = :status')
            ->setParameter('status', SessionMentor::ACTIVE)
            ->select('s')
            ->addSelect('count(apprentis.id) as nbApprentis')
            ->groupBy('s.id')
            ->orderBy('s.startDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function countSessionsByCursus(Staff $staff)
    {
        return $this->createQueryBuilder('s')
            ->join('s.cursus', 'sc')
            ->where('sc.id = :id')
            ->setParameter('id', $staff->getCursus()->getId())
            ->select('count(s.id) as numberSession')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getSessionsDays(Session $session)
    {
        return $this->createQueryBuilder('s')
            ->where('s.id = :id')
            ->setParameter('id', $session->getId())
            ->join('s.modules', 'm')
            ->join('m.DayCourses', 'd')
            ->select('d.id')
            ->orderBy('m.orderModule','ASC')
            ->addOrderBy('d.ordre','ASC')
            ->getQuery()
            ->getResult();
    }

    public function findSessionsWaitingAndInProgress()
    {
        return $this->createQueryBuilder('s')
            ->where('s.endDate > :today')
            ->setParameter('today', new \DateTime())
            ->getQuery()
            ->getResult();
    }

    public function findSessionInProgrssAndWaiting($cursus)
    {
        return $this->createQueryBuilder('s')
            ->where('s.endDate > :today')
            ->setParameter('today', new \DateTime())
            ->join('s.cursus', 'c')
            ->andWhere('c.id LIKE :cursus')
            ->setParameter('cursus', $cursus->getId())
            ->getQuery()
            ->getResult();
    }

    public function findSessionsInProgress($cursus = null)
    {
        $today=new \DateTime();
        $query = $this->createQueryBuilder('s')
            ->where(':today BETWEEN s.startDate AND s.endDate')
            ->setParameter('today', $today->setTime(00, 00, 00));
        if ($cursus) {
            $query
                ->join('s.cursus', 'c')
                ->andWhere('c.id LIKE :cursus')
                ->setParameter('cursus', $cursus);
        }
        return $query->getQuery()->getResult();
    }

    public function findSessionFinished($cursus = null)
    {
        $today=new \DateTime('now');
        $query = $this->createQueryBuilder('s')
            ->select("count(s.id)")
            ->where(':today > s.endDate')
            ->setParameter('today',$today->setTime(00, 00, 00));
        if ($cursus) {
            $query
                ->join('s.cursus', 'c')
                ->andWhere('c.id LIKE :cursus')
                ->setParameter('cursus', $cursus);
        }
        return $query->getQuery()->getSingleScalarResult();
    }

    public function findSessionFinishedArray($cursus = null)
    {
        $query = $this->createQueryBuilder('s')
            ->join('s.sessionUserDatas','sessionUserDatas')
            ->where(':today > s.endDate')
            ->setParameter('today', new \DateTime())
            ->orderBy('s.ordre');
        if ($cursus) {
            $query
                ->join('s.cursus', 'c')
                ->andWhere('c.id LIKE :cursus')
                ->setParameter('cursus', $cursus);
        }
        return $query->getQuery()->getResult();
    }

    public function findSessionPlanned($cursus = null)
    {
        $query = $this->createQueryBuilder('s')
            ->select("count(s.id)")
            ->where(':today < s.startDate')
            ->setParameter('today', new \DateTime());
        if ($cursus) {
            $query
                ->join('s.cursus', 'c')
                ->andWhere('c.id LIKE :cursus')
                ->setParameter('cursus', $cursus);
        }
        return $query->getQuery()->getSingleScalarResult();
    }

    public function findSessionsOrdred()
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.sessionUserDatas', 'sessionUserDatas')
            ->leftJoin('sessionUserDatas.user', 'apprentis')
            ->select('s')
            ->addSelect('count(apprentis.id) as nbApprentis')
            ->groupBy('s.id')
            ->orderBy('s.startDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function countSessionsDays(Session $session)
    {
        return $this->createQueryBuilder('s')
            ->where('s.id = :id')
            ->setParameter('id', $session->getId())
            ->join('s.modules', 'm')
            ->join('m.DayCourses', 'd')
            ->select('count(d.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countValidatingDaysBySession(Session $session)
    {
        return $this->createQueryBuilder('s')
            ->join('s.modules', 'm')
            ->join('m.DayCourses', 'd')
            ->select('count(d.id)')
            ->where('s.id = :id')
            ->andWhere('d.status = :stausValidating')
            ->setParameter('id', $session->getId())
            ->setParameter('stausValidating', SessionDayCourse::VALIDATING_DAY)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countSessionsModules(Session $session)
    {
        return $this->createQueryBuilder('s')
            ->where('s.id = :id')
            ->setParameter('id', $session->getId())
            ->join('s.modules', 'm')
            ->select('count(m.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countDaysValidate(Session $session)
    {
        return $this->createQueryBuilder('c')
            ->select('c')
            ->join('c.modules', 'cm')
            ->join('cm.DayCourses', 'cmd')
            ->select('count(cmd.id)')
            ->where('c.id = :id')
            ->andWhere('cmd.status = :status')
            ->setParameter('status', SessionDayCourse::VALIDATING_DAY)
            ->setParameter('id', $session->getId())
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getPassedSession($params)
    {
        $query = $this->createQueryBuilder('session')
            ->join('session.sessionUserDatas', 'sessionUserDatas')
            ->join('sessionUserDatas.user', 'user')
            ->join('session.cursus', 'cursus')
            ->where('user.id = :id')
            ->andWhere('session.endDate < :now');
            $user = $params[4];
            if($user instanceof Staff){
                $id = $user->getStudent();
            }
            elseif ($user instanceof Student){
                $id = $user;
            }
            $query->setParameters(['id' => $id, 'now' => (new \DateTime())->setTime(0, 0)])
            ->select('session.id', 'session.startDate', 'session.endDate', 'sessionUserDatas.status', 'cursus.name')
            ->orderBy('session.endDate', 'ASC');
        if (!is_null($params[3]) && $params[3]['value'] != '') {
            if($params[3]['value']=='Abondon')
            {
                $params[3]['value']=SessionUserData::ABANDONMENT;

            }
                $query
                    ->andWhere('cursus.name LIKE :val OR sessionUserDatas.status  LIKE :val')
                    ->setParameter('val', '%' . trim($params[3]['value']) . '%');

        }
        $countResult = count($query->getQuery()->getResult());

        $query->setFirstResult($params[0])->setMaxResults($params[1]);

        // Order
        foreach ($params[2] as $order) {
            if ($order['name'] != '') {
                $orderColumn = null;

                switch ($order['name']) {
                    case 'cursus':
                        {
                            $orderColumn = 'cursus.name';
                            break;
                        }


                    case 'finalResult':
                        {
                            $orderColumn = 'sessionUserDatas.status';
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

    public function getAllSession()
    {
        $query = $this->createQueryBuilder('session')
            ->join('session.cursus', 'cursus')
            ->orderBy('session.startDate', 'DESC');

        $results = $query->getQuery()->getResult();
        $countResult = count($results);
        return array(
            "results" => $results,
            "countResult" => $countResult
        );

    }


    /**
     * @param $user
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countPassedSession($user)
    {

        return $this->createQueryBuilder('session')
            ->join('session.sessionUserDatas', 'sessionUserDatas')
            ->join('sessionUserDatas.user', 'user')
            ->where('user.id = :id')
            ->setParameter('id', $user)
            ->select('count(session)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findSessionsByStartDateAndCursusExceptOneSession($cursus, $startDate, $id)
    {
        return $this->createQueryBuilder('session')
            ->andWhere('session.cursus = :cursus')
            ->andWhere('session.startDate = :startDate')
            ->andWhere('session.id != :sessionId')
            ->setParameter('cursus', $cursus)
            ->setParameter('startDate', $startDate)
            ->setParameter('sessionId', $id)
            ->getQuery()
            ->getResult();

    }

    public function findSessionUntreated(){
        return $this->createQueryBuilder('session')
            ->andWhere('session.status NOT LIKE :finished')
            ->setParameter('finished', Session::TERMINE)
            ->getQuery()
            ->getResult();
    }

}

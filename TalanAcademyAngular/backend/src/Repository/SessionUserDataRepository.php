<?php

namespace App\Repository;

use App\Entity\Session;
use App\Entity\SessionDayCourse;
use App\Entity\SessionMentor;
use App\Entity\SessionUserData;
use App\Entity\Staff;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SessionUserData|null find($id, $lockMode = null, $lockVersion = null)
 * @method SessionUserData|null findOneBy(array $criteria, array $orderBy = null)
 * @method SessionUserData[]    findAll()
 * @method SessionUserData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @codeCoverageIgnore
 */
class SessionUserDataRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SessionUserData::class);
    }

    public function findSessionInProgressByUser($user)
    {
        return $this->createQueryBuilder('s')
            ->join('s.session', 'session')
            ->join('s.user', 'user')
            ->where('session.endDate >= :today')
            ->where('session.startDate <= :today')
            ->andWhere('user.id = :user')
            ->setParameter('user', $user)
            ->setParameter('today', (new \DateTime())->setTime(0, 0))
            ->getQuery()
            ->getOneOrNullResult();

    }

    public function findSessionsWaitingAndInProgressByUser($user)
    {
        return $this->createQueryBuilder('s')
            ->join('s.session', 'ss')
            ->where(':today < ss.endDate')
            ->andWhere('s.user = :user')
            ->setParameter('user', $user)
            ->setParameter('today', new \DateTime())
            ->getQuery()
            ->getOneOrNullResult();

    }

    public function findSessionsWaitingByUser($user)
    {
        return $this->createQueryBuilder('s')
            ->join('s.session', 'ss')
            ->where(':today < ss.startDate')
            ->andWhere('s.user = :user')
            ->setParameter('user', $user)
            ->setParameter('today', new \DateTime())
            ->getQuery()
            ->getResult();
            

    }

    public function findUsersJokersForSessionsInProgress()
    {
        return $this->createQueryBuilder('s')
            ->join('s.user', 'su')
            ->join('s.session', 'ss')
            ->join('ss.cursus', 'c')
            ->select('c.name', 'ss.startDate', 'su.id', 'su.firstName', 'su.lastName', 's.nbrJoker')
            ->where(':today BETWEEN ss.startDate AND ss.endDate')
            ->setParameter('today', new \DateTime())
            ->getQuery()
            ->getResult();
    }

    public function getApprenticeData($start, $length, $orders, $search, $columns, $extraSearch, Session $session = null)
    {
        $query = $this->createQueryBuilder('sessionUserDatas')
            ->join('sessionUserDatas.session', 'session')
            ->where('session.id LIKE :session')
            ->setParameter('session', $session->getId())
            ->andWhere('sessionUserDatas.status not LIKE :status')
            ->setParameter('status', SessionUserData::ABANDONMENT)
            ->leftJoin('sessionUserDatas.user', 'user');


        if (!is_null($search) && $search['value'] != '') {
            $query
                ->andWhere('user.firstName LIKE :val OR user.lastName LIKE :val OR sessionUserDatas.status LIKE :val OR sessionUserDatas.nbrJoker LIKE :val')
                ->setParameter('val', '%' . trim($search['value']) . '%');
        }
        if (!is_null($extraSearch) && is_array($extraSearch)) {
            foreach ($extraSearch as $searchingField) {
                switch ($searchingField['name']) {
                    case 'firstName' :
                        $query->andWhere('user.firstName LIKE :firstName')->setParameter('firstName', '%' . $searchingField['value'] . '%');
                        break;
                    case 'lastName' :
                        $query->andWhere('user.lastName LIKE :lastName')->setParameter('lastName', '%' . $searchingField['value'] . '%');
                        break;
                    case 'nbrJoker' :
                        $query->andWhere('sessionUserDatas.nbrJoker LIKE :nbrJoker')->setParameter('nbrJoker', $searchingField['value']);
                        break;
                    case 'status' :
                        if ($searchingField['value'] == "qualified") {
                            $query->andWhere('sessionUserDatas.status LIKE :qualified')->setParameter('qualified', SessionUserData::QUALIFIED);
                            break;
                        } elseif ($searchingField['value'] == "eliminated") {
                            $query->andWhere('sessionUserDatas.status LIKE :eliminated')->setParameter('eliminated', SessionUserData::ELIMINATED);
                            break;
                        } elseif ($searchingField['value'] == "confirmed") {
                            $query->andWhere('sessionUserDatas.status LIKE :confirmed')->setParameter('confirmed', SessionUserData::CONFIRMED);
                            break;
                        } elseif ($searchingField['value'] == "notSelected") {
                            $query->andWhere('sessionUserDatas.status LIKE :notSelected')->setParameter('notSelected', SessionUserData::NOTSELECTED);
                            break;
                        } elseif ($searchingField['value'] == "apprenti") {
                            $query->andWhere('sessionUserDatas.status LIKE :apprenti')->setParameter('apprenti', SessionUserData::APPRENTI);
                            break;
                        }


                }
            }
        }

        $countResult = count($query->getQuery()->getResult());
        if ($length != -1) {
            $query->setFirstResult($start)->setMaxResults($length);
        }

        // Order
        foreach ($orders as $order) {
            if ($order['name'] != '') {
                $orderColumn = null;

                switch ($order['name']) {
                    case 'firstName':
                        {
                            $orderColumn = 'user.firstName';
                            break;
                        }
                    case 'joker':
                        {
                            $orderColumn = 'sessionUserDatas.nbrJoker';
                            break;
                        }
                    case 'roles':
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

    public function countApprentice(Session $session)
    {
        return $this
            ->createQueryBuilder('sessionUserDatas')
            ->select("count(sessionUserDatas.id)")
            ->andWhere('sessionUserDatas.session = :session')
            ->andWhere('sessionUserDatas.status not LIKE :status')
            ->setParameter('status', SessionUserData::ABANDONMENT)
            ->setParameter('session', $session)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countCorasaire($session)
    {
        return $this->createQueryBuilder('su')
            ->select("count(su.id)")
            ->where('su.status = :status')
            ->setParameter('status', SessionUserData::QUALIFIED)
            ->andWhere('su.session = :session')
            ->setParameter('session', $session)
            ->getQuery()
            ->getSingleScalarResult();

    }

    public function countConfirmed($session)
    {
        return $this->createQueryBuilder('su')
            ->select("count(su.id)")
            ->where('su.status = :status')
            ->setParameter('status', SessionUserData::CONFIRMED)
            ->andWhere('su.session = :session')
            ->setParameter('session', $session)
            ->getQuery()
            ->getSingleScalarResult();

    }

    public function getLastFinishedSession($apprentice)
    {
        return $this->createQueryBuilder('sud')
            ->join('sud.user', 'user')
            ->join('sud.session', 'session')
            ->where('user.id = :user')
            ->andWhere('session.endDate < :now')
            ->setParameters(['user' => $apprentice, 'now' => new \DateTime()])
            ->orderBy('session.endDate', 'DESC')
            ->getQuery()
            ->getResult();
    }


    public function getFirstPendingSession($apprentice)
    {
        return $this->createQueryBuilder('sud')
            ->join('sud.user', 'user')
            ->join('sud.session', 'session')
            ->where('user.id = :user')
            ->andWhere('session.startDate > :now')
            ->setParameters(['user' => $apprentice, 'now' => new \DateTime()])
            ->orderBy('session.startDate')
            ->getQuery()
            ->getResult();

    }

    public function getAprrentisForMentor(Staff $mentor)
    {
        return $this
            ->createQueryBuilder('sessionUserData')
            ->select("count(sessionUserData.id)")
            ->where('sessionUserData.status LIKE :apprenti')
            ->setParameter('apprenti', SessionUserData::APPRENTI)
            ->join('sessionUserData.session', 'session')
            ->join('session.cursus', 'cursus')
            ->andWhere('cursus.id LIKE :cursus')
            ->setParameter('cursus', $mentor->getCursus()->getId())
            ->join('session.sessionMentors', 'sessionMentors')
            ->andWhere('sessionMentors.status LIKE :active')
            ->setParameter('active', SessionMentor::ACTIVE)
            ->join('sessionMentors.mentor', 'mentor')
            ->andWhere('mentor.id LIKE :mentor')
            ->setParameter('mentor', $mentor->getId())
            ->getQuery()
            ->getSingleScalarResult();


    }

    public function getSessionInfoForMentor(Staff $mentor, $status)
    {
        $query = $this
            ->createQueryBuilder('sessionUserData')
            ->select("count(sessionUserData.id)")
            ->where('sessionUserData.status LIKE :status');
        if ($status == SessionUserData::APPRENTI) {
            $query->setParameter('status', SessionUserData::APPRENTI);
        } elseif ($status == SessionUserData::QUALIFIED) {
            $query->orWhere('sessionUserData.status LIKE :notSelected')
                ->orWhere('sessionUserData.status LIKE :confirmed')
                ->setParameters(['status' => SessionUserData::QUALIFIED, 'notSelected' => SessionUserData::NOTSELECTED, 'confirmed' => SessionUserData::CONFIRMED]);
        } elseif ($status == SessionUserData::ELIMINATED) {
            $query->setParameter('status', SessionUserData::ELIMINATED);
        }
        $query
            ->join('sessionUserData.session', 'session')
            ->join('session.cursus', 'cursus')
            ->andWhere('cursus.id LIKE :cursus')
            ->setParameter('cursus', $mentor->getCursus()->getId())
            ->join('session.sessionMentors', 'sessionMentors')
            ->andWhere('sessionMentors.status LIKE :active')
            ->setParameter('active', SessionMentor::ACTIVE)
            ->join('sessionMentors.mentor', 'mentor')
            ->andWhere('mentor.id LIKE :mentor')
            ->setParameter('mentor', $mentor->getId());
        return $query->getQuery()
            ->getSingleScalarResult();
    }


    public function getSessionInfoForAdmin($status)
    {
        $query = $this
            ->createQueryBuilder('sessionUserData')
            ->select("count(sessionUserData.id)")
            ->where('sessionUserData.status LIKE :status');
        if ($status == SessionUserData::APPRENTI) {
            $query->setParameter('status', SessionUserData::APPRENTI);
        } elseif ($status == SessionUserData::QUALIFIED) {
            $query->orWhere('sessionUserData.status LIKE :notSelected')
                ->orWhere('sessionUserData.status LIKE :confirmed')
                ->setParameters(['status' => SessionUserData::QUALIFIED, 'notSelected' => SessionUserData::NOTSELECTED, 'confirmed' => SessionUserData::CONFIRMED]);
        } elseif ($status == SessionUserData::ELIMINATED) {
            $query->setParameter('status', SessionUserData::ELIMINATED);
        }
        return $query->getQuery()
            ->getSingleScalarResult();
    }

    public function getSessionInfoForAdminPerSession($session, $status)
    {
        $query = $this
            ->createQueryBuilder('sessionUserData')
            ->select("count(sessionUserData.id)")
            ->where('sessionUserData.status LIKE :status');
        if ($status == SessionUserData::APPRENTI) {
            $query->setParameter('status', SessionUserData::APPRENTI);
        } elseif ($status == SessionUserData::QUALIFIED) {
            $query->setParameter('status', SessionUserData::QUALIFIED);
        } elseif ($status == SessionUserData::ELIMINATED) {
            $query->setParameter('status', SessionUserData::ELIMINATED);
        }
        $query->andWhere('sessionUserData.session = :session')
            ->setParameter('session', $session);
        return $query->getQuery()
            ->getSingleScalarResult();
    }

    public function getNonAffectedApprenticeOnSubject($session, $affectedApprentice)
    {
        $query = $this
            ->createQueryBuilder('sessionUserDatas')
            ->join('sessionUserDatas.user', 'user')
            ->where('sessionUserDatas.session = :session')
            ->andWhere('sessionUserDatas.status  LIKE :status');
        if ($affectedApprentice != null) {
            $query->andWhere('sessionUserDatas.user NOT IN (:affectedApprentice)')
                ->setParameter('affectedApprentice', $affectedApprentice);
        }
        $query->setParameter('status', SessionUserData::APPRENTI)
            ->setParameter('session', $session);

        return $query->getQuery()
            ->getResult();
    }
    public function getSessionParameter(User $user, Session $session)
    {
        return $this->createQueryBuilder('sud')
            ->join('sud.session', 'session')
            ->join('sud.user', 'user')
            ->where('user.id = :user')
            ->andWhere('session.id = :session')
            ->setParameters(['user' => $user, 'session' => $session])
           // ->select('sud.id', 'sud.repoGit', 'sud.profilSlack') -- this is old request
            ->select('sud.id', 'sud.nbrJoker', 'sud.status','sud.interactionSlack','sud.mission')
            ->getQuery()
            ->getResult();
    }


}

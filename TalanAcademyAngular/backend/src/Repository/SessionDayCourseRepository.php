<?php

namespace App\Repository;

use App\Entity\DayCourse;
use App\Entity\Module;
use App\Entity\Session;
use App\Entity\SessionDayCourse;
use App\Entity\SessionModule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SessionDayCourse|null find($id, $lockMode = null, $lockVersion = null)
 * @method SessionDayCourse|null findOneBy(array $criteria, array $orderBy = null)
 * @method SessionDayCourse[]    findAll()
 * @method SessionDayCourse[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SessionDayCourseRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SessionDayCourse::class);
    }

    /**
     * @param $id
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function nbDay($id)
    {
        return $this->createQueryBuilder('dc')
            ->select('count(dc.id)')
            ->where('dc.module = :idModule')
            ->setParameter('idModule', $id)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findDaysOrdred(SessionModule $module)
    {
        return $this->createQueryBuilder('dcs')
            ->select('dcs')
            ->join('dcs.module', 'module')
            ->where('module.id LIKE :module')
            ->setParameter('module', $module->getId())
            ->orderBy('dcs.ordre')
            ->getQuery()
            ->getResult();
    }



    public function getRequiredData($start, $length, $orders, $search, $columns, $dayIdsPassed,$extraSearch)
    {

        $query = $this->createQueryBuilder('day')
            ->leftJoin('day.module', 'module')
            ->orderBy('day.ordre', 'DESC')
            ->andWhere('day.status = :validant')
            ->andWhere('day.id IN (:dayIdsPassed)')
            ->setParameter('validant', SessionDayCourse::VALIDATING_DAY)
            ->setParameter('dayIdsPassed', $dayIdsPassed)
            ->orderBy('day.dateDay','DESC');


        if ($search['value'] != '') {
            $query
                ->andWhere('day.description LIKE :val')
                ->setParameter('val', '%' . trim($search['value']) . '%');
        }
        if(!is_null($extraSearch) && is_array($extraSearch)){
            //vérification par chaque champ du filtre
            foreach ($extraSearch as $searchingField){
                switch ($searchingField['name']){
                    case 'module' : $query->andWhere('module.title like :module')->setParameter('module', '%'.$searchingField['value'].'%'); break;
                    case 'day' : $query->andWhere('day.description like :day')->setParameter('day', '%'.$searchingField['value'].'%'); break;
                }
            }
        }
        $countResult = count($query->getQuery()->getResult());
        if ($length != -1){
            $query->setFirstResult($start)->setMaxResults($length);
        }

        // Order
        foreach ($orders as $order) {
            if ($order['name'] != '') {
                $orderColumn = null;
                if ($order['name'] == 'course') {
                    $orderColumn = 'day.ordre';
                    $query->orderBy('module.orderModule', $order['dir']);
                    $query->addOrderBy($orderColumn, $order['dir']);

                }
                if ($order['name'] == 'module') {
                    $orderColumn = 'module.orderModule';
                    $query->orderBy($orderColumn, $order['dir']);
                    $query->addOrderBy('day.ordre', 'ASC');
                }

            }
        }



        $results = $query->getQuery()->getResult();

        return array(
            "results" => $results,
            "countResult" => $countResult
        );

    }

    public function findDaysValidation($dayIdsPassed)
    {
        return $this->createQueryBuilder('day')
            ->join('day.module', 'module')
            ->andWhere('day.status = :validant')
            ->andWhere('day.id IN (:dayIdsPassed)')
            ->setParameter('validant', SessionDayCourse::VALIDATING_DAY)
            ->setParameter('dayIdsPassed', $dayIdsPassed)
            ->addorderBy('module.orderModule', 'DESC')
            ->addorderBy('day.ordre', 'DESC')
            ->getQuery()
            ->getResult();

    }

    public function getEvaluatingDay($params)
    {
        $query = $this->createQueryBuilder('sd')
            ->select('sd.description')
            ->join('sd.module', 'module')
            ->join('module.session', 'session')
            ->andWhere('sd.id IN (:dayId)')
            ->setParameter('dayId', $params[5])
            ->addSelect('sr.comment', 'sr.rating', 'module.title','sd.id','student.id as studentId' )
            ->leftJoin('sd.apprentices', 'sr')
            ->leftJoin('sr.student', 'student')
            ->orderBy('sd.dateDay', 'DESC')
        ;
        $query->andWhere('sr.student = :user OR ' . $query->expr()->isNull('sr.student'))
            ->setParameter('user', $params[4]);

        if ($params[3]['value'] != '') {
            $query
                ->andWhere('sd.description LIKE :val OR sr.comment LIKE :val OR module.title LIKE :val')
                ->setParameter('val', '%' . trim($params[3]['value']) . '%');
        }
        if(!is_null($params[6]) && is_array($params[6])){
            //vérification par chaque champ du filtre
            foreach ($params[6] as $searchingField){
                switch ($searchingField['name']){
                    case 'module' : $query->andWhere('module.title like :module')->setParameter('module', '%'.$searchingField['value'].'%'); break;
                    case 'day' : $query->andWhere('sd.description like :day')->setParameter('day', '%'.$searchingField['value'].'%'); break;
                    case 'rating' :
                        if($searchingField['value']==0){
                            $query->andWhere('sr.rating is NULL');
                        }else{
                            $query->andWhere('sr.rating like :rating')->setParameter('rating', '%' . $searchingField['value'] . '%');
                            break;
                        }
                }
            }
        }
        $countResult = count($query->getQuery()->getResult());
        if ($params[1] != -1) {
            $query->setFirstResult($params[0])->setMaxResults($params[1]);
        }

        // Order
        foreach ($params[2] as $order) {
            if ($order['name'] != '') {
                $orderColumn = null;

                switch ($order['name']) {
                    case 'module':
                        {
                            $orderColumn = 'module.title';
                            break;
                        }
                    case 'course':
                        {
                            $orderColumn = 'sd.description';
                            break;
                        }

                    case 'comment':
                        {
                            $orderColumn = 'sr.comment';
                            break;

                        }
                    case 'note':
                        {
                            $orderColumn = 'sr.rating';
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

    public function getValidatingDay($params)
    {


        $query = $this->createQueryBuilder('sd')
            ->join('sd.module', 'module')
            ->andWhere('sd.id IN (:dayId)')
            ->setParameter('dayId', $params[5])
            ->select('module.title', 'sd.description', 'sd.id')
            ->andWhere('sd.status = :status')
            ->setParameter('status', SessionDayCourse::VALIDATING_DAY)
            ->orderBy('sd.dateDay','DESC');


        if ($params[3]['value'] != '') {
            $query
                ->andWhere('sd.description LIKE :val OR module.title LIKE :val')
                ->setParameter('val', '%' . trim($params[3]['value']) . '%');
        }


        if (!is_null($params[6]) && is_array($params[6])) {
            foreach ($params[6] as $searchingField) {
                switch ($searchingField['name']) {
                    case 'module' :
                        $query->andWhere('module.title LIKE :module')->setParameter('module', '%' . $searchingField['value'] . '%');
                        break;
                    case 'day' :
                        $query->andWhere('sd.description LIKE :day')->setParameter('day', '%' . $searchingField['value'] . '%');
                        break;
                }
            }
        }

        $countResult = count($query->getQuery()->getResult());
        if ($params[1] != -1) {
            $query->setFirstResult($params[0])->setMaxResults($params[1]);
        }
        // Order
        foreach ($params[2] as $order) {
            if ($order['name'] != '') {
                $orderColumn = null;

                switch ($order['name']) {
                    case 'leçon':
                        {
                            $orderColumn = 'sd.description';
                            break;
                        }

                    case 'module':
                        {
                            $orderColumn = 'module.title';
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



    public function getValidatingDay2($params)
    {


        $query = $this->createQueryBuilder('sd')
            ->join('sd.module', 'module')
            ->andWhere('sd.id IN (:dayId)')
            ->setParameter('dayId', $params)
            ->select('module.title', 'sd.description', 'sd.id')
            ->andWhere('sd.status = :status')
            ->setParameter('status', SessionDayCourse::VALIDATING_DAY)
            ->orderBy('sd.dateDay','DESC');


     
        $countResult = count($query->getQuery()->getResult());
     
        $results = $query->getQuery()->getResult();


        return array(
            "results" => $results,
            "countResult" => $countResult
        );

    }
    public function getSessionEvaluatingDay2($params)
    {
        $query = $this->createQueryBuilder('sd')
            ->select('sd.description')
            ->join('sd.module', 'module')
            ->andWhere('sd.id IN (:dayId)')
            ->setParameter('dayId', $params)
            ->addSelect('module.title', 'sd.description', 'sd.id')
            ->orderBy('sd.dateDay', 'DESC');
      

        $countResult = count($query->getQuery()->getResult());

        $results = $query->getQuery()->getResult();

        
        return array(
            "results" => $results,
            "countResult" => $countResult
        );

    }

    public function getSessionEvaluatingDay($params)
    {
        $query = $this->createQueryBuilder('sd')
            ->select('sd.description')
            ->join('sd.module', 'module')
            ->andWhere('sd.id IN (:dayId)')
            ->setParameter('dayId', $params[4])
            ->addSelect('module.title', 'sd.description', 'sd.id')
            ->orderBy('sd.dateDay', 'DESC');
        if ($params[3]['value'] != '') {
            $query
                ->andWhere('module.title LIKE :val OR sd.description LIKE :val')
                ->setParameter('val', '%' . trim($params[3]['value']) . '%');
        }
        if (!is_null($params[5]) && is_array($params[5])) {
            foreach ($params[5] as $searchingField) {
                switch ($searchingField['name']) {
                    case 'module' :
                        $query->andWhere('module.title LIKE :module')->setParameter('module', '%' . $searchingField['value'] . '%');
                        break;
                    case 'day':
                        $query->andWhere('sd.description LIKE :day')->setParameter('day', '%' . $searchingField['value'] . '%');
                        break;
                    case 'dateDay':
                        $query->andWhere('DATE_FORMAT(sd.dateDay, \'%d-%m-%Y\') LIKE :date')->setParameter('date', $searchingField['value']);
                }
            }
        }
        $countResult = count($query->getQuery()->getResult());
        if ($params[1] != -1) {
            $query->setFirstResult($params[0])->setMaxResults($params[1]);
        }

        // Order
        foreach ($params[2] as $order) {
            if ($order['name'] != '') {
                $orderColumn = null;
                switch ($order['name']) {
                    case 'Module':
                        {
                            $orderColumn = 'module.title';
                            break;
                        }

                    case 'Lecon':
                        {
                            $orderColumn = 'sd.description';
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

    public function countAllDaysBySession(Session $session)
    {
        return $this->createQueryBuilder('day')
            ->join('day.module', 'module')
            ->select("count(day.id)")
            ->andWhere('module.session = :session')
            ->setParameter('session', $session)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findLastValidationDay($session)
    {
        return $this->createQueryBuilder('d')
            ->join('d.module', 'module')
            ->where('module.session = :session')
            ->setParameter('session', $session)
            ->andWhere('d.status LIKE :status')
            ->setParameter('status', DayCourse::VALIDATING_DAY)
            ->getQuery()
            ->getResult();
    }

    public function findDaysWithModuleBetweenTwoOrders($module, $first, $end)
    {
        return $this->createQueryBuilder('dc')
            ->select('dc')
            ->join('dc.module', 'module')
            ->where('module.id LIKE :module')
            ->andWhere('dc.ordre BETWEEN :first AND :end')
            ->setParameter('module', $module)
            ->setParameter('first', $first)
            ->setParameter('end', $end)
            ->orderBy('dc.ordre')
            ->getQuery()
            ->getResult();
    }

    public function getOrdredDayCoursesByModule(SessionModule $module)
    {
        return $this->createQueryBuilder('dc')
            ->select('dc')
            ->join('dc.module', 'module')
            ->where('module = :module')
            ->setParameter('module', $module)
            ->orderBy('dc.ordre', 'ASC')
            ->getQuery()
            ->getResult();
    }



}

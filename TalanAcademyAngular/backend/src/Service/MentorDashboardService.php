<?php


namespace App\Service;


use App\Entity\Session;
use App\Entity\SessionUserData;
use App\Entity\Staff;
use App\Entity\Student;
use App\Entity\User;
use App\Repository\CursusRepository;
use App\Repository\SessionRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Validator\Constraints\DateTime;

class MentorDashboardService
{

    /**
     * @var SessionRepository
     */
    private $sessionRepository;
    /**
     * @var CursusRepository
     */
    private $cursusRepository;
    /**
     * @var StatisticalStudentService
     */
    private $statisticalStudentService;
    /**
     * @var SessionService
     */
    private $sessionService;

    private $manager;

    public function __construct(SessionRepository $sessionRepository,CursusRepository $cursusRepository,StatisticalStudentService $statisticalStudentService,SessionService $sessionService, ObjectManager $manager)
    {
        $this->sessionRepository = $sessionRepository;
        $this->cursusRepository = $cursusRepository;
        $this->statisticalStudentService = $statisticalStudentService;
        $this->sessionService = $sessionService;
        $this->manager=$manager;
    }

    public function getSessions($session, SessionService $sessionService, ObjectManager $manager, CalculateAverageService $averageService)
    {
        $staticSession = [];
        $session1 =  $this->sessionRepository->find($session);

        $result = [];
            $progress = $this->sessionService->progression($session1);
            $result ['progression'] = $progress;
        $numberApprentis = $sessionService->apprenticesCount($session1);
            $result ['numberApprentis'] = $numberApprentis;
            $cursusName = $session1->getCursus()->getName();
            $result ['cursusName'] = $cursusName;
            $dateStart = $session1->getstartDate();
            $dateEnd = $session1->getEndDate();
            $result ['startDate'] = $dateStart;
            if ($dateStart->format('Y-m-d') > (new \DateTime('now'))->format('Y-m-d')){
                $result ['status'] = 'en attente';
            }elseif ($dateEnd->format('Y-m-d') < (new \DateTime('now'))->format('Y-m-d')){
                $result ['status'] = 'terminée';
            } elseif ($dateStart->format('Y-m-d') <= (new \DateTime('now'))->format('Y-m-d') &&
                (new \DateTime('now'))->format('Y-m-d') <= $dateEnd->format('Y-m-d') )  {
                $result ['status'] = 'en cours';
            }
            //appel les stars
            $stars = $sessionService->calculateSessionRates($session1);
            $result['stars'] = $stars['stars'];
            $result['daysCountStarted'] = $stars['daysCountStarted'];
            $result['moy'] = $session1->getMoy();
            $result['id'] = $session1->getId();
            $result['ordre'] = $session1->getOrdre();

            $scores=[];
            $sum=0;
            $apprentis=$manager->getRepository(Student::class)->findApprentisBySession($session1);
            foreach ($apprentis as $apprenti){
                $scores[]=$averageService->calculateMinMaxScore($session1, $apprenti);
            }
            $min=0;
            $max=0;
            if(count($scores)!=0) {
                $min=$scores[0]['average'];
                $max=$scores[0]['average'];
                foreach ($scores as $score) {
                    $sum += $score['average'];
                    if($min>$score['average']){
                        $min=$score['average'];
                    }
                    if($max<$score['average']){
                        $max=$score['average'];
                    }
                }
            }
            $scoreMoy=0;
            if(count($scores)!=0) {
                $scoreMoy = $sum / count($scores);
            }
            $result['scoreMoy']=$scoreMoy;
            $result['min']=$min;
            $result['max']=$max;
            $staticSession [] = $result;
        return $staticSession;
    }
    public function countCursus(Staff $staff)
    {
        return $this->cursusRepository->countForStaff($staff);
    }
    public function staticStudentAll($cursus)
    {
        $student = [];
        $candidate = $this->statisticalStudentService->getCandidateByCursus($cursus,User::ROLE_CANDIDAT);
        $student['candidate'] = $candidate;
        $apprentie = $this->statisticalStudentService->getCandidateByCursus($cursus,User::ROLE_APPRENTI);
        $student['apprentie'] = $apprentie;
        $corsaire = 0;
        $student['corsaire'] = $corsaire;
        $renegat = 0;
        $student['renegat'] = $renegat;
        return $student;
    }
}

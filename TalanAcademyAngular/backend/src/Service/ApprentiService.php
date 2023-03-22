<?php
/**
 * Created by PhpStorm.
 * User: jghada
 * Date: 25/05/2019
 * Time: 16:40
 */

namespace App\Service;


use App\Entity\Affectation;
use App\Entity\Correction;
use App\Entity\CorrectionResult;
use App\Entity\DayCourse;
use App\Entity\ResourceRecommendation;
use App\Entity\Session;
use App\Entity\SessionDayCourse;
use App\Entity\SessionOrder;
use App\Entity\Resources;
use App\Entity\SessionUserData;
use App\Entity\StudentReview;
use App\Entity\SubjectDayContent;
use App\Entity\SubmissionWorks;
use App\Repository\SessionUserDataRepository;
use Doctrine\ORM\EntityManagerInterface;

class ApprentiService
{

    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var SessionService
     */
    private $sessionService;
    /**
     * @var CalculateAverageService
     */
    private $averageService;
    /**
     * @var AssociateDateService
     */
    private $associateDateService;
    /**
     * @var SessionUserDataRepository
     */
    private $sessionUserDataRepository;

    public function __construct(AssociateDateService $associateDateService, SessionUserDataRepository $sessionUserDataRepository, EntityManagerInterface $em, SessionService $sessionService, CalculateAverageService $averageService)
    {
        $this->em = $em;
        $this->sessionService = $sessionService;
        $this->averageService = $averageService;
        $this->associateDateService = $associateDateService;
        $this->sessionUserDataRepository = $sessionUserDataRepository;
    }

/**
 * get user info for dashboardApprentice and /profile data
 */

    public function getDashboardApprentiStat($sessionUser)
    {
        $stat = [];
        $session = $sessionUser->getSession();
        $stat['starteDate'] =$session->getName();
        $stat['cursusId']=$session->getCursus()->getId();
        $stat['sessionId']= $sessionUser->getSession()->getId();
        $stat['sessionName']=$session->getCursus()->getName();
        $stat['sessionImage']=$session->getCursus()->getImage();
        $stat['nbSessionModule'] = $this->em->getRepository(Session::class)->countSessionsModules($session);
        $stat['nbSessionDay'] = $this->em->getRepository(Session::class)->countSessionsDays($session);
        $stat['nbValidatingDay'] = $this->em->getRepository(Session::class)->countValidatingDaysBySession($session);
    
        $stat['progress'] = $this->sessionService->progression($session);
        $stat['nbrDayPassedTotal'] = count($this->associateDateService->getPassedDateDayArrayId($session));
        $stat['nbPastValidatingDay'] = $this->sessionService->countPastValidatingDay($session)['nbPastValidatingDay'];
    
        $stat['nbrJokerTotal'] = $session->getJokerNbr();
        $stat['nbrJoker'] = $sessionUser->getNbrJoker();
    
    
        $average = $this->averageService->calculateMinMaxScore($session, $sessionUser->getUser());
        $stat['average'] = $average['average'];
        $stat['mission'] = $sessionUser->getMission();
        $stat['nbrDaysUserEvaluation'] = $this->em->getRepository(StudentReview::class)->countNbrEvaluatingDayForUser($session, $sessionUser->getUser());
        $stat['nbrPrposedResources'] = $this->em->getRepository(Resources::class)->getCountProposedResourcesByApprentice($sessionUser->getUser());
        return $stat;

    }





    ///////////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////
    //NOT used



    public function createCorrectionsMetrics($corrections, $lastValidationDay = null)
    {
        $correctionList = [];
        foreach ($corrections as $correction) {
            $tmp = [];
            $tmp['correction'] = $correction;
            $tmp['submittedWork'] = null;
            if ($lastValidationDay) {
                $tmp['submittedWork'] = $this->em->getRepository(SubmissionWorks::class)->findOneBy(['student' => $correction->getCorrected(), 'course' => $lastValidationDay]);
            }
            $correctionResults = $this->em->getRepository(CorrectionResult::class)->findBy(['correction' => $correction]);
            $orders = [];
            foreach ($correctionResults as $correctionResult) {
                $tmpOrders = [];
                $tmpOrders['order'] = $correctionResult->getOrderCourse();
                $tmpOrders['CorrectionResult'] = $correctionResult;
                $orders[] = $tmpOrders;
            }

            $tmp['correctionResults'] = $correctionResults;
            $tmp['orders'] = $orders;
            $correctionList[] = $tmp;
        }

        return $correctionList;

    }
    public function getDashboardData($sessionUser) {
        $stat = [];
        $session = $sessionUser->getSession();
        $stat['sessionName']=$session->getCursus()->getName();
        $stat['sessionImage']=$session->getCursus()->getImage();
        $stat['cursusId']=$session->getCursus()->getId();
        $stat['sessionId']= $sessionUser->getSession()->getId();
        $stat['nbSessionModule'] = $this->em->getRepository(Session::class)->countSessionsModules($session);
        $stat['nbSessionDay'] = $this->em->getRepository(Session::class)->countSessionsDays($session);
        $stat['progress'] = $this->sessionService->progression($session);
        return $stat;
    }
  
    /**
     * check if apprentice has right to access to the session's content
     * @param $sessionUser
     * @return bool
     */
    public function apprenticeHasRights($sessionUser)
    {
        return $sessionUser->getRepoGit() && $sessionUser->getProfilSlack();

    }

    public function getRatingMsgMetric($rating)
    {
        $msgMetric = [];
        switch ($rating) {
            case 1:
                {
                    $msgMetric[0] = "<div class='mb-2 px-0 rating-smiley'><i class=\"far fa-angry fa-2x px-0\"></i></div><p class=' text-center px-0'>Cette journée m'a <b>énervé.</b><br><span class='rating-message-smiley'></b>Le cours m'a <b>perdu</b> et je n'ai <b>pas apprécié</b> les activités.</span></p>";
                    break;
                }
            case 2:
                {
                    $msgMetric[0] = "<div class='mb-2 px-0 rating-smiley''><i class=\"far fa-frown fa-2x px-0 \"></i></div> <p class=' text-center px-0'>Franchement c'était <b>pas top</b>.<br><span class='rating-message-smiley'> C'était <b>pas clair</b> et/ou <b>mal écrit.</b> Les activités n'avaient <b>pas un grand intérêt.</b></span></p>";
                    break;
                }
            case 3:
                {
                    $msgMetric[0] = "<div class='mb-2 px-0 rating-smiley''><i class=\"far fa-meh fa-2x px-0 \"></i></div> <p class=' text-center px-0'>C'était <b>pas mal</b>.<br><span class='rating-message-smiley'> J'ai quelques remarques et/ou suggestions...</span></p>";
                    break;
                }
            case 4:
                {
                    $msgMetric[0] = "<div class='mb-2 px-0 rating-smiley''><i class=\"far fa-smile fa-2x px-0\"></i></div> <p class=' text-center px-0'>C'était <b>bien</b>.<br><span class='rating-message-smiley'>Le cours était <b>clair</b> et les activités <b>intéressantes</b> !</span></p>";
                    break;
                }
            case 5:
                {
                    $msgMetric[0] = "<div class='mb-2 px-0 rating-smiley''><i class=\"far fa-grin-hearts fa-2x px-0\"></i></div> <p class=' text-center px-0'><b>WAOW!</b><br><span class='rating-message-smiley'> J'ai <b>adoré</b> la journée !</span></p>";
                    break;
                }
            default:
                $msgMetric[0] = "<p></p>";

        }
        $msgMetric[1]['tooltip'] = 'Mauvaise';
        $msgMetric[2]['tooltip'] = 'Pas top';
        $msgMetric[3]['tooltip'] = 'Pas mal';
        $msgMetric[4]['tooltip'] = 'Bien';
        $msgMetric[5]['tooltip'] = 'Excellent';

        return $msgMetric;

    }


    public function getStatusOfSessionByApprentice($apprentice)
    {

        $nbPassedSession = 0;
        $sessionUserDatas = $this->em->getRepository(SessionUserData::class)->findBy(['user' => $apprentice]);
        foreach ($sessionUserDatas as $sessionUserData) {
            $session = $sessionUserData->getSession();
            $sessionEndDate = $session->getEndDate();
            $sessionStartDate = $session->getStartDate();
            /*if the apprentice has a session in progress, return the session's id*/
            if ($sessionEndDate->setTime(0, 0) >= (new \DateTime())->setTime(0, 0) && $sessionStartDate->setTime(0, 0) <= (new \DateTime())->setTime(0, 0)) {
                return ['status' => 'actuelle', 'sessionUserData' => $sessionUserData];
            } elseif ($sessionEndDate < new \DateTime()) {
                $nbPassedSession++;
            }

        }
        //if foreach loop finished and the function didn't return a response yet =>there's no session in progress;
        //if we have a passed session,we'll return the recently finished session,
        //else we have neither a session in progress nor a passed session just a pending session

        if ($nbPassedSession > 0) {
            $recentlyFinishedSession = null;
            $recentlyFinishedSessions = $this->sessionUserDataRepository->getLastFinishedSession($apprentice);
            if (!is_null($recentlyFinishedSessions)) {
                $recentlyFinishedSession = $recentlyFinishedSessions[0];
            }
            return ['status' => 'passée', 'sessionUserData' => $recentlyFinishedSession];
        } else {
            $recentlyPendingSession = null;
            $recentlyPendingSessions = $this->sessionUserDataRepository->getFirstPendingSession($apprentice);
            if (!is_null($recentlyPendingSessions)) {
                $recentlyPendingSession = $recentlyPendingSessions[0];
            }

            return ['status' => 'en attente', 'sessionUserData' => $recentlyPendingSession];

        }

    }

    public function createValidationsMetric($daysValidation, $user)
    {
        $daysValidationList = [];
        foreach ($daysValidation as $dayValidation) {
            $tmp = [];
            $tmp['dayValidation'] = $dayValidation;
            $tmp['exception'] = 'notSubmitted';
            if ($validation = $this->em->getRepository(SubmissionWorks::class)->findOneBy(['course' => $dayValidation, 'student' => $user])) {
                $tmp['exception'] = 'notCorrected';
                $dayCorrection = $this->em->getRepository(SessionDayCourse::class)->findOneBy(['module' => $dayValidation->getModule(), 'ordre' => $dayValidation->getOrdre() + 1]);
                $corrections = $this->em->getRepository(Correction::class)->findBy(['corrected' => $user, 'day' => $dayCorrection]);
                $dayDateMax = $this->associateDateService->getPlanifiedDateFromSessionDay($dayCorrection);
                $today = new \DateTime();
                date_time_set($dayDateMax, Session::H_MAX_CORRECTION, 0, 0);
                if ($corrections) {
                    $tmp['exception'] = 'notException';
                    $tmp['corrections'] = $corrections;
                    $ordersAndResults = [];
                    $orders = $this->em->getRepository(SessionOrder::class)->findBy(['dayCourse' => $dayValidation]);
                    foreach ($orders as $order) {
                        $tmpOrder = [];
                        $tmpOrder['order'] = $order;
                        $results = $this->em->getRepository(CorrectionResult::class)->findResultByOrderAndCorrected($order, $user);
                        foreach ($results as $result) {
                            if (!$result['result'] && $today < $dayDateMax) {
                                $result['result'] = 'waitingCorrection';
                            }
                        }
                        $tmpOrder['myCorrections'] = $results;
                        $ordersAndResults[] = $tmpOrder;
                    }

                    $tmp['ordersAndResults'] = $ordersAndResults;
                    $score = $this->averageService->calculateDayScore($dayCorrection, $user);
                    if ($score) {
                        $moy = 0;
                        if ($score['total'] != 0) {
                            $moy = $score['note'] * 100 / $score['total'];
                            $moy = round($moy);
                        }
                        $tmp['average'] = $moy;
                    }
                }

            }
            $daysValidationList[] = $tmp;
        }
        return $daysValidationList;
    }

    public function removeValidatingDayForCorection(AssociateDateService $associateDateService, Session $session, $dayArray)
    {
        $currentAndPreviousDay = $associateDateService->getCurrentDayAndPreviousDay($session);
        $currentDay = $currentAndPreviousDay['currentDay'];
        $previousDay = $currentAndPreviousDay['previousDay'];
        $indexCurrentDay = 0;
        $indexPreviousDay = 0;
        for ($i = 1; $i < count($dayArray); $i++) {
            if (!is_null($currentDay) && $dayArray[$i] == $currentDay->getId()) {
                $indexCurrentDay = $i;
            }
            if (!is_null($previousDay) && $dayArray[$i] == $previousDay['id']) {
                $indexPreviousDay = $i;
            }
        }
        $today = new \DateTime();
        $hmaxCorrection = $session->getHMaxCorection();
        $deadline = $today->format('H') < $hmaxCorrection;
        if (!is_null($currentDay)) {
            if ($currentDay->getStatus() === DayCourse::VALIDATING_DAY) {
                array_splice($dayArray, $indexCurrentDay);
            } elseif ($currentDay->getStatus() === DayCourse::CORRECTION_DAY && $deadline) {
                array_splice($dayArray, $indexCurrentDay);
                array_splice($dayArray, $indexPreviousDay);
            }
        }
        return $dayArray;

    }

    /**
     * getCurrentContentSubject returns the current day of subject on which the candidate is assigned
     * @param $dayCourse
     * @param $user
     * @return mixed
     */

    public function getCurrentContentSubject($dayCourse, $user)
    {
        $currentSubject = $this->getCurrentSubject($dayCourse, $user);
        return $this->em->getRepository(SubjectDayContent::class)->getCurrentSubjectDayContent($dayCourse, $currentSubject);

    }

    /**
     * getCurrentSubject returns the current subject on which the candidate is assigned
     * @param $dayCourse
     * @param $user
     * @return array
     */
    public function getCurrentSubject($dayCourse, $user)
    {
        $subjectsFromContent = $this->em->getRepository(SubjectDayContent::class)->getSubjectsFromContent($dayCourse);
        $subjectsFromStudent = $this->em->getRepository(Affectation::class)->getSubjectsForStudent($user);
        return  array_uintersect($subjectsFromContent, $subjectsFromStudent, function ($val1, $val2) {
            return strcmp($val1['id'], $val2['id']);
        });


    }

    public function getNonAffectedApprentice($project)
    {
        $session = $project->getSession();
        $affectedApprentice = $this->em->getRepository(Affectation::class)->getAffectedStudentOnProject($project);
        return $this->em->getRepository(SessionUserData::class)->getNonAffectedApprenticeOnSubject($session, $affectedApprentice);

    }

}

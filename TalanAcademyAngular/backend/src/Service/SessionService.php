<?php
/**
 * Created by PhpStorm.
 * User: sarfaoui
 * Date: 02/05/2019
 * Time: 17:43
 */

namespace App\Service;

use App\Entity\Cursus;
use App\Entity\Session;
use App\Entity\Resources;
use App\Entity\SessionDayCourse;
use App\Entity\SessionMentor;
use App\Entity\SessionUserData;
use App\Entity\StudentReview;
use App\Entity\User;
use App\Entity\Student;
use App\Entity\YearPublicHolidays;
use App\Repository\SessionDayCourseRepository;
use App\Repository\SessionRepository;
use App\Repository\StudentRepository;
use App\Repository\StudentReviewRepository;
use App\Repository\SessionUserDataRepository;
use App\Service\CalculateAverageService;
use App\Service\ApprentiService;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;




class SessionService extends AbstractController
{

    const STAR_OUTLINE = ' text-muted';
    const STAR_HALF = '-half';
    const STAR_HALF_SESSION = '-half-alt';

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var HolidaysService
     */
    private $holidaysService;
    /**
     * @var SessionRepository
     */
    private $sessionRepository;
    /**
     * @var SessionDayCourseRepository
     */
    private $sessionDayCourseRepository;
    /**
     * @var SessionUserDataRepository
     */
    private $sessionUserRepository;

    private $associateDateService;
    /**
     * @var AdminStaticService
     */
    private $adminStaticService;
    private $studentRepository;
    private $studentReviewRepository;
    private $apprentiService;
    private $sessionUserDataRepository;


    /**
     * SessionService constructor.
     * @param EntityManagerInterface $em
     * @param HolidaysService $holidaysService
     * @param SessionUserDataRepository $sessionUserRepository
     * @param SessionRepository $sessionRepository
     * @param SessionDayCourseRepository $sessionDayCourseRepository
     * @param AssociateDateService $associateDateService
     * @param AdminStaticService $adminStaticService
     * @param LoggerInterface $logger
     */
    public function __construct(EntityManagerInterface $em,
                                HolidaysService $holidaysService,
                                SessionUserDataRepository $sessionUserRepository,
                                SessionRepository $sessionRepository,
                                SessionDayCourseRepository $sessionDayCourseRepository,
                                AssociateDateService $associateDateService,
                                AdminStaticService $adminStaticService,
                                LoggerInterface $logger,
                                StudentRepository $studentRepository,
                                StudentReviewRepository $studentReviewRepository
                                )
    {
        $this->em = $em;
        $this->holidaysService = $holidaysService;
        $this->sessionRepository = $sessionRepository;
        $this->sessionDayCourseRepository = $sessionDayCourseRepository;
        $this->sessionUserRepository = $sessionUserRepository;
        $this->associateDateService = $associateDateService;
        $this->adminStaticService = $adminStaticService;
        $this->logger = $logger;
        $this->studentRepository = $studentRepository;
        $this->studentReviewRepository = $studentReviewRepository;
    }



    // functions for /admin/session/{id} details : students - reviews - validations


    public function getStudentsValidations(Session $session, ApprentiService $apprentiService, CalculateAverageService $averageService){

        $sessionUserDatas = $session->getSessionUserDatas();
        if (sizeof($sessionUserDatas) == 0) {
                $finalOutput = [];
                $finalOutput['recordsTotal'] = 0;
                $finalOutput['recordsFiltered'] = 0;
                $finalOutput['data'] = [];
                return $finalOutput;
    
        } else {
    
            $dayIdDateArray = $this->associateDateService->getPassedDateDayArrayId($session);
            $dayIdDateArray = $apprentiService->removeValidatingDayForCorection($this->associateDateService, $session, $dayIdDateArray);
            
                $results = $this->sessionDayCourseRepository->getValidatingDay2($dayIdDateArray);
                $objects = $results["results"];
             //   $totalObjectsCount = $this->sessionDayCourseRepository->getRepository(SessionUserData::class)->countApprentice($session);
              //  $filteredObjectsCount = $results["countResult"];
    
            //    $finalOutput = [];
            //    $finalOutput['recordsTotal'] = $totalObjectsCount;
            //    $finalOutput['recordsFiltered'] = $filteredObjectsCount;
            //    $finalOutput['data'] = [];
                foreach ($objects as $key => $day) {
                    $dayCourse = $this->sessionDayCourseRepository->find($day['id']);
                    $correctionDay = $this->sessionDayCourseRepository->findOneBy(['module' => $dayCourse->getModule(), 'ordre' => $dayCourse->getOrdre() + 1]);
                    $note = $averageService->calculateAverageDayForAllUsers($correctionDay, $session);

                    $validation=[
                        'module' => $day['title'],
                        'course' => $day['description'],
                        'date' => $this->associateDateService->getPlanifiedDateFromSessionDay($dayCourse)->format('d-m-Y'), 
                        'score'=>$note['average'],
                    ];
         
                    $validations[]=$validation;
                
                }
    
               }
    
    
        return $validations;
    

    }

    public function getStudentsReviews(Session $session){
        
            $dayIdDateArray = $this->associateDateService->getPassedDateDayArrayId($session);
            $results = $this->sessionDayCourseRepository->getSessionEvaluatingDay2($dayIdDateArray);

            $objects = $results["results"];
            $review = [];
            foreach ($objects as $key => $day) {
                $dayCourse = $this->sessionDayCourseRepository->find($day['id']);
                $rating = $this->studentReviewRepository->findRatingAverage($dayCourse);
                if (!empty($rating))
                {
                    $formattedRating = $this->formatRatingByRatingValue($rating);
                    $ratingAvg=$formattedRating['avg'] * 100 / 5;
                    $comments = $this->studentReviewRepository->findCommentNotNullByDay($dayCourse);
                    if (empty($comments)) $comments =null;
                
                    $review=[
                        'module' => $day['title'],
                        'course' => $day['description'],
                        'rating' => $ratingAvg, 
                        'ratingDetails'=>$formattedRating,
                        'comments' => $comments
                    ];
            
                    $reviews[]=$review;
                }
            }
            return $reviews;
        }


        private function formatRatingByRatingValue($ratings)
        {
            $output = array('avg' => 0, 'totalVoters' => 0, 'stars' => array('1' => 0, '2' => 0, '3' => 0, '4' => 0, '5' => 0));
            $totalVoters = 0;
            $totalVotersValue = 0;
            foreach ($ratings as $rating) {
                $ratingValue = $rating['rating_value'];
                $ratingCount = intval($rating['rating_count']);
                $totalVoters += $ratingCount;
                $totalVotersValue += ($ratingValue * $ratingCount);
                $output['stars'][$ratingValue] = $ratingCount;
            }
            if ($totalVoters != 0) {
                $avg = $totalVotersValue / $totalVoters;
                $output['avg'] = $avg;
                $output['totalVoters'] = $totalVoters;
            }
            return $output;
        }

    
    public function getSessionStudentsDetails (Session $session,CalculateAverageService $averageService ){
        $apprentis = $this->studentRepository->findApprentisBySession($session);
        $students = [];
        foreach ($apprentis as $apprenti) {
           // $scores[] = $this->averageService->calculateMinMaxScore($session1, $apprenti);
            $userdata=$this->sessionUserRepository->getSessionParameter($apprenti,$session)[0]; 
            $student=[
                'id'=> $apprenti->getId(),
                'email' => $apprenti->getEmail(),
                'image' => $apprenti->getImage(),
                'firstname' => $apprenti->getFirstName(),
                'lastname' => $apprenti->getLastName(),
                'status' => $userdata['status'],
                'nbrJocker' => $userdata['nbrJoker'],
                'nbrJockerTotal' => $session->getJokerNbr(),
                'score'=> $averageService->calculateMinMaxScore($session, $apprenti)['average'],
                'mission' => $userdata['mission'],
                'interaction' => $userdata['interactionSlack'],
                'countReviews' => $this->studentReviewRepository->countNbrEvaluatingDayForUser($session, $apprenti),
                'avgReviews' => $this->studentReviewRepository->getAverageEvaluatingDay($apprenti, $session),
                'countPrposedResources' => $this->em->getRepository(Resources::class)->getCountProposedResourcesByApprentice($apprenti)
         ];
            $students[]=$student;
         
        }
        return $students;

    }

    public function getStudentSessionCv(Student $student){
        
        $sessionUser = $this->sessionUserRepository->findSessionInProgressByUser($student);
        //$userdata=$this->sessionUserRepository->getSessionParameter($student,$session)[0]; 
        if (!$sessionUser)
            return $this->json('L\'apprenti n\'a pas déposé de cv !',404);

        $candidature = $sessionUser->getCandidature();
        $binaryFileResponse = new BinaryFileResponse($this->getParameter('cv_candidate_directory') . DIRECTORY_SEPARATOR . $candidature->getCv());
        $binaryFileResponse->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_INLINE,
            $binaryFileResponse->getFile()->getFilename()
        );
        $binaryFileResponse->headers->set('Content-Type', 'application/pdf');
        return  $binaryFileResponse;

    }


    //functions calls

    public function prog(Session $session)
    {
        $progress = 0;
        $today = new \DateTime("now");
        $starDate = $session->getStartDate();
        $endDate = $session->getEndDate();
        $daysSessionsNum = $this->em->getRepository(Session::class)->countSessionsDays($session);

        if ($today >= $starDate && $today < $endDate && $daysSessionsNum != 0) {

            $passedDays = $this->associateDateService->getDayDateArray($session, new \DateTime());
            $nbrejoursetudie = count($passedDays);
            $progress = (($nbrejoursetudie) * 100) / ($daysSessionsNum);
        }
        else if ($today < $starDate) {
            $progress = 0;
        } else if ($today >= $endDate) {
            $progress = 100;
        }
        return $progress;

    }
    /**
     * @param Session $session
     * @return float|int
     * @throws \Exception
     */
    public function progression(Session $session)
    {
        $today = new \DateTime("now");
        $starDate = $session->getStartDate();
        $endDate = $session->getEndDate();
        $progress = 0;
        $nbrejoursetudie = 0;
        $daysSessionsNum = $this->em->getRepository(Session::class)->countSessionsDays($session);
        $publicHolidays = $this->em->getRepository(YearPublicHolidays::class)->findAll();
//        $startDateStr = date('Y-m-d', strtotime($date));
//        $endDateStr = date('Y-m-d', strtotime('' . $endDate->format('Y-m-d')));
//        $todayStr = date('Y-m-d', strtotime($today->format('Y-m-d')));
        $startDateStr = $starDate->format('Y-m-d');
        $endDateStr = $endDate->format('Y-m-d');
        $todayStr = $today->format('Y-m-d');
        $arr = [];
        if ($todayStr >= $startDateStr && $todayStr < $endDateStr && $daysSessionsNum != 0) {
            for ($i = 0; $i < $daysSessionsNum; $i++) {
                $dates = date('w', strtotime($startDateStr . ' + ' . $i . ' days'));
                if ($dates != 0 && $dates != 6 && $todayStr >= date('Y-m-d', strtotime($startDateStr . '+' . $i . 'days'))) {
                    $nbrejoursetudie += 1;
                    array_push($arr, date('Y-m-d', strtotime($startDateStr . '+' . $i . 'days')));

                }
            }

            foreach ($publicHolidays as $day) {
                if (in_array('' . $day->getDate()->format('Y-m-d'), $arr)) {
                    $nbrejoursetudie -= 1;
                }
            }

            $progress = (($nbrejoursetudie) * 100) / ($daysSessionsNum);
        } else if ($todayStr < $startDateStr) {
            $progress = 0;
        } else if ($todayStr >= $endDateStr) {
            $progress = 100;
        }

        return $progress;
    }

 /**
     * @param Session $session
     * @return array
     */
    private function getStars(Session $session)
    {
        $rating = $this->em->getRepository(StudentReview::class)->findRatingAverageBySession($session);
        $start_1 = self::STAR_OUTLINE;
        $start_2 = self::STAR_OUTLINE;
        $start_3 = self::STAR_OUTLINE;
        $start_4 = self::STAR_OUTLINE;
        $start_5 = self::STAR_OUTLINE;

        switch ($rating) {
            case 0:
                break;
            case 1:
                $start_1 = '';
                break;
            case 2:
                $start_1 = '';
                $start_2 = '';
                break;
            case 3:
                $start_1 = '';
                $start_2 = '';
                $start_3 = '';
                break;
            case 4:
                $start_1 = '';
                $start_2 = '';
                $start_3 = '';
                $start_4 = '';
                break;
            case 5:
                $start_1 = '';
                $start_2 = '';
                $start_3 = '';
                $start_4 = '';
                $start_5 = '';
                break;
            case ($rating > 0 && $rating < 1):
                $start_1 = self::STAR_HALF ;
                break;
            case ($rating > 1 && $rating < 2):
                $start_1 = '';
                $start_2 = self::STAR_HALF;
                break;
            case ($rating > 2 && $rating < 3):
                $start_1 = '';
                $start_2 = '';
                $start_3 = self::STAR_HALF;
                break;
            case ($rating > 3 && $rating < 4):
                $start_1 = '';
                $start_2 = '';
                $start_3 = '';
                $start_4 = self::STAR_HALF;
                break;
            case ($rating > 4 && $rating < 5):
                $start_1 = '';
                $start_2 = '';
                $start_3 = '';
                $start_4 = '';
                $start_5 = self::STAR_HALF;
                break;
            default:
                break;
        }
        return [$start_1, $start_2, $start_3, $start_4, $start_5, $rating];
    }

    //////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////
    ///////NOT used

    /**
     * @param $sessions
     * @return array
     * @throws \Exception
     */
    public function CreateSessionsMetrics($sessions)
    {
        $sessionsList = array();
        foreach ($sessions as $session) {
            /** @var Session $session */
            $tmp = array();
            //si tu as besoin
            $tmp['session'] = $session;
            $tmp['mentorsCount'] = $this->mentorsCount($session);
            $tmp['candidateCount'] = $this->apprenticesCount($session);
            $tmp['progression'] = $this->progression($session);
            $tmp['stars'] = $this->getStars($session);
            $tmp['ordre'] = $session->getOrdre();
            $tmp['daysCountStarted'] = $this->daysCountStartedSession($session);
            $sessionsList[] = $tmp;
        }
        return $sessionsList;
    }

  
 
    /**
     * @param Session $session
     * @return int
     * @throws \Exception
     */
    private function daysCountStartedSession(Session $session)
    {

        $today = new \DateTime("now");
        if ($today <= $session->getStartDate()) {
            $diff = $session->getStartDate()->diff($today);
        } else {
            $diff = 0;
        }
        return $diff;
    }

    /**
     * @param Session $session
     * @return false|int|string
     */
    public function getOrdre(Session $session)
    {
        $sessions = $this->em->getRepository(Session::class)->findBy(['cursus' => $session->getCursus()], ['startDate' => 'DESC']);

        $key = array_search($session, $sessions);
        $ordre = count($sessions) - $key;

        return $ordre;
    }

    public function updateOrdre(Cursus $cursus, EntityManagerInterface $em)
    {
        $sessions = $em->getRepository(Session::class)->findBy(['cursus' => $cursus]);
        foreach ($sessions as $session) {
            $ordre = $this->getOrdre($session);
            $session->setOrdre($ordre);
            $em->persist($session);
            $em->flush();
        }
    }

    /**
     * @param Session $session
     * @param int $nbJour
     * @return array|\DateTime|mixed
     */
    public function applaySessionEndDate(Session $session, $nbJour = 0)
    {
        $startDateClone = clone $session->getStartDate();
        $endDate = $this->holidaysService->calculateEndDate($startDateClone, $nbJour);
        $session->setEndDate($endDate);
        $this->em->persist($session);
        $this->em->flush();
        return $endDate;
    }

    public function addMentorSession(Session $session, $mentors)
    {
        foreach ($mentors as $mentor) {
            if (!$this->em->getRepository(SessionMentor::class)->findBy(['mentor' => $mentor, 'session' => $session])) {
                $sessionMentor = new SessionMentor();
                $sessionMentor->setStatus(SessionMentor::INACTIVE);
                $sessionMentor->setSession($session);
                $sessionMentor->setMentor($mentor);
                $this->em->persist($sessionMentor);
            }
        }

        $this->em->flush();
    }

    public function createMentorsMetrics($mentors, $session)
    {
        $mentorList = [];
        foreach ($mentors as $mentor) {
            $tmp = [];
            $sm = $this->em->getRepository(SessionMentor::class)->findOneBy(['session' => $session, 'mentor' => $mentor]);
            if ($sm) {
                $tmp['mentor'] = $mentor;
                $tmp['status'] = $sm->getStatus();
                $tmp['nbrSessions'] = $this->em->getRepository(SessionMentor::class)->getNbrSessionForMentor($mentor->getId());
                $tmp['id'] = $sm->getId();
            }
            if ($tmp) {
                $mentorList[] = $tmp;
            }
        }
        return $mentorList;
    }

    public function countPastValidatingDay(Session $session)
    {
        $pastValidatingDays = [];
        $pastValidatingDaysDate = [];
        $nbPastValidatingDay = 0;
        $currentDate = (new \DateTime());
        $daysData = $this->associateDateService->getDayDateArray($session);
        $i = 0;
        while ($i < count((array)$daysData) && $daysData[$i]['date'] < $currentDate) {
            if ($daysData[$i]['day']->getStatus() == SessionDayCourse::VALIDATING_DAY) {
                $nbPastValidatingDay++;
                $pastValidatingDays[] = $daysData[$i]['day'];
                $pastValidatingDaysDate[] = $daysData[$i]['date']->format('Y-m-d');
            }
            $i++;
        }
        $result['nbPastValidatingDay'] = $nbPastValidatingDay;
        $result['pastValidatingDays'] = $pastValidatingDays;
        $result['pastValidatingDaysDate'] = $pastValidatingDaysDate;
        return $result;

    }

    public function getSessionInProgressByUser($user)
    {
        $result = $this->em->getRepository(SessionUserData::class)->findSessionInProgressByUser($user);
        return $result[0]->getSession();
    }


    public function retraitJokerFromUser($user_id)
    {
        $user = $this->em->getRepository(User::class)->find($user_id);
        $sessionUser = $this->sessionUserRepository->findSessionInProgressByUser($user);
        $this->logger->info(' : nombre des jokers :' . $sessionUser->getNbrJoker());
        if ($sessionUser->getNbrJoker() === 0) {
            $newNbrJoker = 0;
        } else {
            $newNbrJoker = $sessionUser->getNbrJoker() - 1;
        }
        $sessionUser->setNbrJoker($newNbrJoker);
        if ($newNbrJoker == 0) {
            $sessionUser->setStatus(SessionUserData::ELIMINATED);
        }
        $this->em->persist($sessionUser);
        $this->em->flush();
    }

    public function calculateSessionRates(Session $session)
    {
        $tmp = [];
        $tmp['stars'] = $this->getStars($session);
        $tmp['daysCountStarted'] = $this->daysCountStartedSession($session);
        return $tmp;
    }

    public function getSessionsStat($cursus)
    {
        $sessionsStat = [];
        $sessionsStat['inProgress'] = count($this->em->getRepository(Session::class)->findSessionsInProgress($cursus));
        $sessionsStat['waiting'] = $this->em->getRepository(Session::class)->findSessionPlanned($cursus);
        $sessionsStat['finished'] = $this->em->getRepository(Session::class)->findSessionFinished($cursus);

        return $sessionsStat;
    }

   
    public function changeStatusApprentice(CalculateAverageService $averageService, $sessionId = null)
    {
        $today = new \DateTime('now');
        $today = new \DateTime($today->format('Y-m-d H:i'));
        $sessions = $this->em->getRepository(Session::class)->findSessionUntreated();

        if ($sessionId){
            $sessions = [];
            $session = $this->em->getRepository(Session::class)->find($sessionId);
            if ($session->getStatus()!== Session::TERMINE){
                $sessions[]= $session;
            }

        }

        $this->logger->info('nombre des sessions non traitées: '.count($sessions));
        foreach ($sessions as $session) {
            $endDate = $session->getEndDate();
            $this->logger->info('date fin de la session: '. $endDate->format('Y-m-d'));
            $endDate = new \DateTime($endDate->format('Y-m-d H:i'));
            $endDate->add(new \DateInterval('P1D'));
            if ($endDate < $today) {
                $this->logger->info('session du '.$session->getStartDate()->format('Y-m-d').'au '.$session->getEndDate()->format('Y-m-d').' :session termminée non traitée');
                $sessionUserDatas = $session->getSessionUserDatas();
                if ($sessionUserDatas) {
                    $this->logger->info('nombre des apprentis de cette session: '.count($sessionUserDatas));
                    foreach ($sessionUserDatas as $sessionUserData) {
                        if($sessionUserData->getStatus() != SessionUserData::ABANDONMENT) {
                            $average = $averageService->calculateMinMaxScore($session, $sessionUserData->getUser());
                            $percentageOrder = $session->getPercentageOrder();
                            if ($sessionUserData->getNbrJoker() == 0 || $average['average'] < $percentageOrder || !$sessionUserData->getMission()) {
                                $sessionUserData->setStatus(SessionUserData::ELIMINATED);
                                $this->logger->info('changement du statut à éliminé');
                            } elseif ($sessionUserData->getNbrJoker() > 0 && $average['average'] >= $percentageOrder && $sessionUserData->getMission()) {
                                $sessionUserData->setStatus(SessionUserData::QUALIFIED);
                                $this->logger->info('changement du statut à qualifié');
                            }
                            $this->em->persist($sessionUserData);
                        }
                    }
                }
                $session->setStatus(Session::TERMINE);
                $this->logger->info('session du '.$session->getStartDate()->format('Y-m-d').' a bien été traitée ');
                $this->em->persist($session);
                $this->em->flush();
            }
        }
    }

    public function verifySessionInProgress(Session $session)
    {
        $startDate = $session->getStartDate();
        $endDate = $session->getEndDate();
        $today = new \DateTime();
        if (($startDate <= $today) && ($endDate > $today) ) {
            return true;
        }
        return false;
    }

    public function updateDateSessionDayCourse()
    {
        $today = new \DateTime('now');
        $sessions = $this->em->getRepository(Session::class)->findAll();
        foreach ($sessions as $session){
            $datesArray = $this->associateDateService->getDayDateArray($session);
            foreach ($datesArray as $date){
                if (strtotime($date['date']->format('Y-m-d'))<= strtotime($today->format('Y-m-d'))){
                    $sessionDayCourse = $this->em->getRepository(SessionDayCourse::class)->find($date['id']);
                    $sessionDayCourse->setDateDay($date['date']);
                    $this->em->persist($sessionDayCourse);
                    $this->em->flush();
                }
            }
        }
    }

    public function updateDateCurrentSessionDayCourse()
    {
        $today = new \DateTime('now');
        $sessions = $this->em->getRepository(Session::class)->findAll();
        foreach ($sessions as $session){
            $datesArray = $this->associateDateService->getDayDateArray($session);
            foreach ($datesArray as $date){
                if (strtotime($date['date']->format('Y-m-d')) == strtotime($today->format('Y-m-d'))){
                    $sessionDayCourse = $this->em->getRepository(SessionDayCourse::class)->find($date['id']);
                    $sessionDayCourse->setDateDay($date['date']);
                    $this->em->persist($sessionDayCourse);
                    $this->em->flush();
                }
            }
        }
    }
  /**
     * @param Session $session
     * @return int
     */
    private function mentorsCount(Session $session)
    {
        $staff = $this->em->getRepository(SessionMentor::class)->findBy(['session' => $session, 'status' => SessionMentor::ACTIVE]);
        return count($staff);
    }

    /**
     * @param Session $session
     * @return int
     */
    public function apprenticesCount(Session $session)
    {
        return $this->em->getRepository(SessionUserData::class)->countApprentice($session);
    }

  }

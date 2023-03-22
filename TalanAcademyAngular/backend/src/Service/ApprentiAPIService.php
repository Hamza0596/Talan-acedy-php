<?php
/**
 * Created by PhpStorm.
 * User: jghada
 * Date: 04/06/2020
 * Time: 10:51
 */

namespace App\Service;
use Psr\Log\LoggerInterface;
use App\Controller\Web\CandidateController;
use App\Entity\Candidature;
use App\Entity\Correction;
use App\Entity\CorrectionResult;
use App\Entity\Cursus;
use App\Entity\DayCourse;
use App\Entity\ResourceRecommendation;
use App\Entity\Resources;
use App\Entity\Session;
use App\Entity\SessionActivityCourses;
use App\Entity\SessionDayCourse;
use App\Entity\SessionModule;
use App\Entity\SessionResources;
use App\Entity\Student;
use App\Entity\StudentReview;
use App\Entity\SubmissionWorks;
use App\Entity\User;
use App\Form\ApprenticeDataType;
use App\Form\ImageUserType;
use App\Form\ResourcesType;
use App\Form\StudentReviewType;
use App\Form\StudentType;
use App\Form\SubmissionWorksType;
use App\Form\UserEditEmailType;
use App\Form\UserEditPasswordType;
use App\Repository\SessionDayCourseRepository;
use App\Repository\SessionUserDataRepository;
use App\Repository\StudentReviewRepository;
use App\Repository\UserRepository;
use App\Service\ResourceRecommendationExtension;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Mpdf\Tag\S;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use function MongoDB\BSON\fromJSON;


class ApprentiAPIService extends AbstractController
{
    const CORRECTION = 'correction';
    const CORRECTIONDAY = 'jour-correction';
    const MESSAGE = 'message';
    const COMMENT = 'comment';
    const DESCRIPTION = 'description';
    const STUDENT = 'student';
    const COURSE = 'course';
    const FORMATDATE = 'Y-m-d H:i:s';
    const CORRECTIONID = 'correctionId';
    const ORDRE = 'ordre';
    const CORRECTIONS = 'corrections';
    const CURRENT_DAY = 'currentDay';
    const MODULE = 'module';
    const CORRECTED = 'corrected';
    const EXCEPTION = 'exception';
    const ERROR = 'errors';
    const INVALID_REQUEST = 'Requête invalide';
    const COURSES = 'course';
    const DEADLINE = 'deadline';
    const HMAX = '23:59:59';
    const SESSION_USER_DATA = 'sessionUserData';
    const Y_M_D = 'Y-m-d';
    const Y_M_D_H_I_S = 'Y-m-d H:i:s';
    const RATING_METRIC_MSG = 'ratingMetricMsg';
    const CORRECTION_LIST = 'correctionsList';
    const MY_CORRECTIONS_LIST = 'myCorrectionsList';
    const USER_ID = 'userId';
    const STUDENT_COMMENT = 'studentComment';
    const RATING_AVERAGE = 'ratingAverage';
    const STUDENT_REVIEW_FORM = 'studentReviewForm';
    const DAY_COURSES = 'courses';
    const AFFECTATIONS = 'affectations';
    const TODAY_IS_VALIDATION_DAY = 'todayIsValidatingDay';
    const RESOURCES = 'ressources';
    const COUNT_RESOURCES = 'countResources';
    const SUBMITTED_WORK = 'submittedWork';
    const SUBMISSION_WORK_FORM = 'submissionWorkForm';
    const DEADLINE_CORRECTION = 'deadlineCorection';
    const PASSED_SESSION = 'passedSession';
    const DAY_CURSUS = 'dayCursus';
    const SUCCESS = 'success';
    const LIST_MODULES = 'listModules';
    const CURRENT_SESSION = 'currentSession';
    const CURSUS_DIRECTORY = 'cursus_directory';
    const CURSUS_DIRECTORY_DEFAULT ='cursus_directory_default';
    const SESSION_STATUS ='sessionStatus';
    const STARTDATE ='startDate';


    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var SessionUserDataRepository
     */
    private $sessionUserDataRepository;
    /**
     * @var AssociateDateService
     */
    private $associateDateService;
    /**
     * @var FormErrorsService
     */
    private $formErrorsService;
    /**
     * @var Mailer
     */
    private $mailer;
    /**
     * @var CalculateAverageService
     */
    private $averageService;
    /**
     * @var ApprentiService
     */
    private $apprentiService;
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;
    /**
     * @var $userRopository
     */
    private $userRepository;
    private $sessionDayCourseRepository;
    private $studentReviewRepository;



    private $imageUserUploadService;

    /**
     * ApprentiApiService constructor.
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param EntityManagerInterface $entityManager
     * @param FormErrorsService $formErrorsService
     * @param SessionUserDataRepository $sessionUserDataRepository
     * @param AssociateDateService $associateDateService
     * @param Mailer $mailer
     * @param CalculateAverageService $averageService
     * @param ApprentiService $apprentiService
     * @param UserRepository $userRepository
     * @param ResourceRecommendationExtension $resourceRecommendationExtension
     */
    public function __construct(UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $entityManager, FormErrorsService $formErrorsService, SessionUserDataRepository $sessionUserDataRepository, AssociateDateService $associateDateService,
                                Mailer                       $mailer, CalculateAverageService $averageService, ApprentiService $apprentiService, UserRepository $userRepository, ImageUserUploadService $imageUserUploadService, SessionDayCourseRepository $sessionDayCourseRepository,
                                StudentReviewRepository      $studentReviewRepository,
                                ResourceRecommendationExtension $resourceRecommendationExtension
                                )
    {
        $this->entityManager = $entityManager;
        $this->sessionUserDataRepository = $sessionUserDataRepository;
        $this->sessionDayCourseRepository = $sessionDayCourseRepository;
        $this->associateDateService = $associateDateService;
        $this->formErrorsService = $formErrorsService;
        $this->mailer = $mailer;
        $this->averageService = $averageService;
        $this->apprentiService = $apprentiService;
        $this->passwordEncoder = $passwordEncoder;
        $this->userRepository = $userRepository;
        $this->imageUserUploadService = $imageUserUploadService;
        $this->sessionUserDataRepository = $sessionUserDataRepository;
        $this->associateDateService = $associateDateService;
        $this->studentReviewRepository = $studentReviewRepository;
        $this->apprentiService = $apprentiService;
        $this->resourceRecommendationExtension=$resourceRecommendationExtension;

    }

    public function findCurrentDaysByUser(Student $student)
    {

        //if session in progress or waiting
        $sessionUserData = $this->sessionUserDataRepository->findSessionsWaitingAndInProgressByUser($student);
        $result = [];
        if ($sessionUserData) {
            $session = $sessionUserData->getSession();
            $startdate=$session->getStartDate();
            $startedate = $session ->getName();
            $result[self::STARTDATE] = $startedate;

            //if waiting
            if ($startdate>(new \DateTime())->setTime(0, 0))
            {
                $result[self::SESSION_STATUS] = 'waiting';        
                $result[self::MESSAGE] = 'La session n\'a pas encore commencé !';
            }
            else
            {
                //la session a commencé
                $result[self::SESSION_STATUS] = 'started';        
                $session = $sessionUserData->getSession();
                $currentDay = $this->associateDateService->getCurrentDayAndPreviousDay($session)[self::CURRENT_DAY];
                $currentDayDetail = [];
                $currentDayDetail['id'] = $currentDay->getId();
                $currentDayDetail[self::DESCRIPTION] = $currentDay->getDescription();
                $dayStatus = $currentDay->getStatus();
                $currentDayDetail['status'] = $dayStatus;
                $currentDayDetail[self::ORDRE] = $currentDay->getOrdre();
                $currentDayDetail['idModule'] = $currentDay->getModule()->getId();
              //  $currentDayDetail['synopsis'] = $currentDay->getSynopsis();
                //si jour validant
                if ($dayStatus == SessionDayCourse::VALIDATING_DAY) {
                    $currentDayDetail['hMaxSubmit'] = $session->getHMaxSubmit();
                    $submittedWork = $this->entityManager->getRepository(SubmissionWorks::class)->findOneBy([self::STUDENT => $student, self::COURSE => $currentDay]);
                    //si soumission
                    if ($submittedWork) {
                        $currentDayDetail['submittedWork'] = $submittedWork->getRepoLink();
                    } else {
                        //si travail non encore soumis
                        $currentDayDetail['submittedWork'] = '';
                    }

                }
                //si jour de correction 
                elseif ($dayStatus == SessionDayCourse::CORRECTION_DAY) {
                    $currentDayDetail['hMaxCorrection'] = $session->getHMaxCorection();
                }
               
                $result[self::CURRENT_DAY] = $currentDayDetail;
                $result[self::MESSAGE] = 'Leçon chargée avec succés !';
            }
            $stat = $this->apprentiService->getDashboardData($sessionUserData);
            $result[self::CURRENT_SESSION] = $stat;
        }
         else 
         {
            //pas de session en cours
            $result[self::SESSION_STATUS] = 'not found';        
            $cursusList = $this->entityManager->getRepository(Cursus::class)->findVisibleCursus();
            $candidatureNotAllow = $this->entityManager->getRepository(Candidature::class)->getCandidatureByArrayOfStatus($student, Candidature::STATUS_NOT_ALLOW);

            if ($candidatureNotAllow) {
                $candidatures = $this->entityManager->getRepository(Candidature::class)->getCandidatureByApprentice($student);
                $result['candidatures'] = $candidatures;
                $result['cursusList'] = $cursusList;
                $result[self::MESSAGE] = 'Candidatures chargées avec succés !';
            } else {
                $result['cursusList'] = $cursusList;
                $result[self::MESSAGE] = 'Cursus chargés avec succés !';
            }
        }
        $result['code'] = 1;

        return $result;
    }

  
    public function getModules(Student $student, SessionDayCourse $sessionDayCourse)
    {

        $sessionModule = $sessionDayCourse->getModule();
        $session = $sessionModule->getSession();
        $sessionModules = $this->entityManager->getRepository(SessionModule::class)->findModulesSession($session);
        $modules = [];
        foreach ($sessionModules as $sessionModule) {
            $module = [];
            $module['id'] = $sessionModule->getId();
            $module['title'] = $sessionModule->getTitle();
            $module[self::DESCRIPTION] = $sessionModule->getDescription();
            $module['type'] = $sessionModule->getType();
            $module['ordre'] = $sessionModule->getOrderModule();
            $module['days'] = $this->getDayCoursesByModule($sessionModule, $student, $sessionDayCourse->getOrdre());
            $modules[] = $module;
        }
        $result = [];
        $result['modules'] = $modules;
        $result['code'] = 1;
        $result[self::MESSAGE] = 'modules chargés avec succés !';
        return $result;
    }

    public function getDayCoursesByModule(SessionModule $sessionModule, $student, $ordre)
    {
        $days = [];
        $daysList = $this->entityManager->getRepository(SessionDayCourse::class)->findDaysOrdred($sessionModule);
        foreach ($daysList as $day) {
            $dayDetail = [];
            $dayDetail['id'] = $day->getId();
            $dayDetail[self::DESCRIPTION] = $day->getDescription();
            $dayDetail['status'] = $day->getStatus();
            $dayDetail['idModule'] = $day->getModule()->getId();
            $dayDetail['orderModule'] = $day->getModule()->getOrderModule();
            $dayDetail[self::ORDRE] = $day->getOrdre();
            $dayDate = $this->associateDateService->getPlanifiedDateFromSessionDay($day);
            if ($dayDate->setTime(0, 0) <= ((new DateTime())->setTime(0, 0)->modify('+1 day'))) {
                $dayDetail['synopsis'] = $day->getSynopsis();
                $resourcesList = $day->getResources();
                $resources = [];
                foreach ($resourcesList as $resource) {
                    $resourceDetail = [];
                    $resourceDetail['id'] = $resource->getId();
                    $resourceDetail['title'] = $resource->getTitle();
                    $resourceDetail['url'] = $resource->getUrl();
                    $resourceDetail[self::COMMENT] = $resource->getComment();
                    $resourceDetail['voted'] = $this->checkRecommendation($resource, $student);
                    $resources[] = $resourceDetail;

                }
                $dayDetail['resources'] = $resources;
            }


            $days[] = $dayDetail;
        }

        return $days;
    }

    public function checkRecommendation($resource, $user)
    {
        $resourceRecommendation = $this->entityManager->getRepository(ResourceRecommendation::class)
        ->findOneBy(['apprentice' => $user, 'resource' => $resource]);
        if ($resourceRecommendation) {
            return $resourceRecommendation->getScore();
        } else {
            return false;
        }
    }

    public function getProposedResources(User $user)
    {
          if(  $resources = $this->entityManager->getRepository(Resources::class)->getProposedResourcesByApprentice($user))
          {
            $result = [];
            $result['resources'] = $resources;
            $result['code'] = 200;
            $result[self::MESSAGE] = 'Ressources chargées avec succès !';
            return $result;
          }
        $result['code'] = 400;
        $result[self::MESSAGE] = 'Aucune ressource proposée !';
        return $this->json($result,400);
    }

    public function saveResource(Request $request, $student, $sessionDayCourse=null)
    {
        if ($sessionDayCourse) {
            $resource = new Resources();
            $data = $request->request->all();
            $day = $this->entityManager->getRepository(DayCourse::class)->findOneBy(['reference' => $sessionDayCourse->getReference()]);
            $resourceWithSameUrl = $this->entityManager->getRepository(Resources::class)->findOneBy(['url' => $data['url'], 'day' => $day]);
            $result = [];
            if($day!=null){
                if ($resourceWithSameUrl == null) {
                    $resource->setUrl($data['url']);
                    $resource->setTitle($data['title']);
                    $resource->setComment($data['comment']);
                    $resource->setStatus(Resources::TOAPPROVE);
                    $resource->setResourceOwner($student);
                    $resource->setDay($day);
                    $resource->setRef("resource_" . time() . "_" . mt_rand());

                    $this->entityManager->persist($resource);
                    $this->entityManager->flush();
                    $result['code'] = 201;
                    $result[self::MESSAGE] = "Ressource ajouté avec succès !";
                    return $this->json($result,201);
                } else {
                    $result['code'] = 401;
                    $result[self::MESSAGE] = "Il existe déjà une ressource avec cette même URL!";
                }
            }
            else
            {
                $result['code'] = 404;
                $result[self::MESSAGE] = "Vous ne pouvez proposer une ressource pour cette leçon !";
            }

            return $this->json($result,401);
            
        }

}
    public function getSessionParameter(User $user, Session $session)
    {

        $parameters = $this->sessionUserDataRepository->getSessionParameter($user, $session);
        $result = [];
        $result['parameters'] = $parameters;
        $result['code'] = 1;
        $result[self::MESSAGE] = "Paramètres chargés avec succès !";
        return $result;
    }

    public function saveSessionParameter(Request $request, User $user, Session $session)
    {
        $sessionUserData = $this->sessionUserDataRepository->findOneBy(['user' => $user, 'session' => $session]);
        $data = $request->request->all();
        $form = $this->createForm(ApprenticeDataType::class, $sessionUserData);
        $form->submit($data);
        $result = [];
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($sessionUserData);
            $this->entityManager->flush();
            $result['code'] = 1;
            $result[self::MESSAGE] = "paramètres ajoutés avec succès !";
        } else {
            $result['code'] = 0;
            $result[self::MESSAGE] = $this->formErrorsService->getErrorsFromForm($form);
        }
        return $result;
    }

    public function getCorrectionsByDay(User $user, SessionDayCourse $sessionDayCourse=null)
    {
        if($sessionDayCourse->getStatus()==self::CORRECTIONDAY){
            $deadlineCorrection = $this->getDeadline($sessionDayCourse, self::CORRECTION);
            if ($sessionDayCourse->getStatus() == SessionDayCourse::CORRECTION_DAY && $deadlineCorrection) {
                $corrections = $this->entityManager->getRepository(Correction::class)->findCorrectionsByUser($user, $sessionDayCourse);
                $result = [];
                $lastValidationDay = $this->entityManager->getRepository(SessionDayCourse::class)->findOneBy([self::MODULE => $sessionDayCourse->getModule(), self::ORDRE => $sessionDayCourse->getOrdre() - 1]);

                $submissionWork = $this->entityManager->getRepository(SubmissionWorks::class)->findOneBy(['student' => $user, 'course' => $lastValidationDay]);
                if (!$submissionWork) {
                    $result['code'] = 200;
                    $result[self::MESSAGE] = "Vous n'avez pas de correction pour aujourd'hui!<br>
                                        Vous n'avez pas soumis votre travail! Un joker vous a été retiré! !";
                } else {
                    if ($corrections) {
                        $result['code'] = 200;
                        $result[self::CORRECTIONS] = $this->getCorrectionMetrics($corrections, $sessionDayCourse);
                        $result[self::MESSAGE] = "Corrections chargées avec succès !";
                    } else {
                        $result['code'] = 200;
                        $result[self::MESSAGE] = "Aucune correction trouvée, veuillez contacter l'administrateur !";
                    }
                }
            } else {
                $result['code'] = 200;
                $result[self::MESSAGE] = "Le délai de correction est dépassé! !";
            }
        }else{
            $result['code'] = 200;
            $result[self::MESSAGE] = "Aucune correction pour aujourd’hui!";
        }

        return $result;
    }

    public function getDeadline(SessionDayCourse $sessionDayCourse, $type)
    {
        $sessionModule = $sessionDayCourse->getModule();
        $session = $sessionModule->getSession();
        if ($type == self::CORRECTION) {
            $hMax = $session->getHMaxCorection();
        } elseif ($type == 'submit') {
            $hMax = $session->getHMaxSubmit();
        }
        $dateCurrentDay = $this->associateDateService->getPlanifiedDateFromSessionDay($sessionDayCourse);
        $date = new \DateTime();
        if ($hMax == 24) {
            $maxDate = $dateCurrentDay->setTime(23, 59, 59)->format(self::FORMATDATE);
            $deadline = $date->format(self::FORMATDATE) < $maxDate;
        } else {
            $maxDate = $dateCurrentDay->setTime($hMax, 00, 00)->format(self::FORMATDATE);
            $deadline = $date->format(self::FORMATDATE) < $maxDate;
        }
        return $deadline;

    }

    public function getCorrectionMetrics($corrections, $sessionDayCourse)
    {
        $correctionsArray = [];
        foreach ($corrections as $correction) {
            $correction['submittedWork'] = null;
            $correctionObject = $this->entityManager->getRepository(Correction::class)->find($correction[self::CORRECTIONID]);
            if (empty($correctionObject->getCorrectionResults()[0]->getResult())) {
                $correction[self::CORRECTED] = 0;
                $lastValidationDay = $this->entityManager->getRepository(SessionDayCourse::class)->findOneBy([self::MODULE => $sessionDayCourse->getModule(), self::ORDRE => $sessionDayCourse->getOrdre() - 1]);
                if ($lastValidationDay != null) {
                    $submittedWork = $this->entityManager->getRepository(SubmissionWorks::class)->findOneBy([self::STUDENT => $correction['correctedId'], self::COURSE => $lastValidationDay]);
                    $correction['submittedWork'] = $submittedWork->getRepoLink();
                    $correctionResults = $this->entityManager->getRepository(CorrectionResult::class)->findBy(['correction' => $correction[self::CORRECTIONID]]);
                    $formattedCorrectionResult = $this->getCorrectionResultMetrics($correctionResults);
                    $correction['correctionResults'] = $formattedCorrectionResult;
                }
            } else {
                $correction[self::CORRECTED] = 1;
            }
            $correctionsArray[] = $correction;

        }
        return $correctionsArray;
    }

    public function getCorrectionResultMetrics($correctionResults)
    {
        $formattedCorrectionResult = [];
        foreach ($correctionResults as $correctionResult) {
            $tmp = [];
            $tmp['orderDescription'] = $correctionResult->getOrderCourse()->getDescription();
            $tmp['correctionResult'] = $correctionResult->getId();
            $formattedCorrectionResult[] = $tmp;
        }
        return $formattedCorrectionResult;
    }

    public function saveCorrection(User $user,Request $request, SessionDayCourse $sessionDayCourse=null)
    {
        if($sessionDayCourse->getStatus()==self::CORRECTIONDAY){
            $data = $request->request->all();
            $deadlineCorrection = $this->getDeadline($sessionDayCourse, self::CORRECTION);
            if ($deadlineCorrection) {
                $comment = $data[self::COMMENT];
                $correctionId=$this->entityManager->getRepository(Correction::class)->findBy(['day'=>$sessionDayCourse,'corrector'=>$user])[0]->getId();
                $correction = $this->entityManager->getRepository(Correction::class)->find($correctionId);
                $correction->setComment($comment);
                $correctionResults = $data['corrections'];
                foreach ($correctionResults as $correctionResult) {
                    $correctionResultId = $correctionResult['id'];
                    $result = $correctionResult['result'];
                    $correctionResult = $this->entityManager->getRepository(CorrectionResult::class)->find($correctionResultId);

                    if ($result) {
                        $correctionResult->setResult(CorrectionResult::TRUE);
                    } else {
                        $correctionResult->setResult(CorrectionResult::FALSE);
                    }
                    $this->entityManager->persist($correctionResult);
                }
                $this->entityManager->persist($correction);
                $this->entityManager->flush();
                $result = [];
                $result['code'] = 201;
                $result[self::MESSAGE] = "Votre correction a été enregistrée avec succès!";
                return $this->json($result,201);

            } else {
                $result['code'] = 200;
                $result[self::MESSAGE] = "Le délai de correction est dépassé!";
            }
        }else{
            $result['code'] = 200;
            $result[self::MESSAGE] = "Aucune correction pour aujourd’hui!";
        }
        return $result;
    }

    public function submitWork(Request $request, Student $student, SessionDayCourse $sessionDayCourse=null)
    {
        if($sessionDayCourse ){
            if($sessionDayCourse->getStatus()=="jour-validant") {
                $repoLink = $request->request->all();
                $deadline = $this->getDeadline($sessionDayCourse, 'submit');
                if ($deadline) {
                    $submissionWork = $this->entityManager->getRepository(SubmissionWorks::class)->findOneBy([self::STUDENT => $student, self::COURSE => $sessionDayCourse]);
                    if ($submissionWork == null) {
                        $submissionWork = new SubmissionWorks();
                        $submissionWork->setStudent($student);
                        $submissionWork->setCourse($sessionDayCourse);
                    }
                    $submissionWorkForm = $this->createForm(SubmissionWorksType::class, $submissionWork);
                    $submissionWorkForm->submit($repoLink);
                    if ($submissionWorkForm->isSubmitted() && $submissionWorkForm->isValid()) {
                        $this->entityManager->persist($submissionWork);
                        $this->entityManager->flush();
                        $result = [];
                        $result['code'] = 201;
                        $result[self::MESSAGE] = "Soumission avec succès!";
                        return $this->json($result, 201);
                    } else {
                        $result = [];
                        $result['code'] = 200;
                        $result[self::MESSAGE] = $this->formErrorsService->getErrorsFromForm($submissionWorkForm);
                        return $result;
                    }

                } else {
                    $result['code'] = 200;
                    $result[self::MESSAGE] = "Le délai de soumission est dépassé!";
                    return $result;
                }
            }
             elseif ($sessionDayCourse->getStatus()!="jour-validant") {
            $result['code'] = 400;
            $result[self::MESSAGE] = "C'est n'est pas un jour de soumission d'activité !";
            return $this->json($result,400);
            }
        } else {
            $result['code'] = 404;
            $result[self::MESSAGE] = "Vérifiez la leçon du jour!";
            return $this->json($result,404);
        }
    }

    public function saveStudentReview(Request $request, Student $student, SessionDayCourse $sessionDayCourse=null)
    {
        if($sessionDayCourse){
            $comment = $request->request->get(self::COMMENT);
            $rating = $request->request->get('rating');
            $result = [];
            $studentReview = new StudentReview();
            $ratingResult=$this->entityManager->getRepository(StudentReview::class)
                ->findOneBy([self::STUDENT => $student, self::COURSE => $sessionDayCourse]);
            if  ( !$ratingResult){
                if ($rating <= 3 && !$comment ) {
                    $result['code'] = 400;
                    $result[self::MESSAGE] = 'Veuillez ajouter vos commentaires pour cette évaluation';
                    return $this->json($result,400);
                } else {

                    $studentReview->setStudent($student)
                        ->setCourse($sessionDayCourse)
                        ->setRating($rating)
                        ->setComment($comment);
                    $this->entityManager->persist($studentReview);
                    $this->entityManager->flush();

                    $result['code'] = 200;
                    $result[self::MESSAGE] = "Votre évaluation a été ajoutée avec succès, merci pour votre contribution.";
                }
                return $result;
            }
            $result['code'] = 403;
            $result[self::MESSAGE] = "Votre évaluation a été déja ajoutée ";
            return $this->json($result,403);
        }

        $result['code'] = 404;
        $result[self::MESSAGE] = "la leçon du jour est introuvable";
        return $this->json($result,404);
}


    public function getStudentReview(Student $student, SessionDayCourse $sessionDayCourse=null)
    {
        $result = [];
        if ($sessionDayCourse){
        $studentReview = $this->entityManager->getRepository(StudentReview::class)->findOneBy([self::STUDENT => $student, self::COURSE => $sessionDayCourse]);
        if ($studentReview) {
            $review = [];
            if ($studentReview->getComment()) {
                $review[self::COMMENT] = $studentReview->getComment();
            } else {
                $review[self::COMMENT] = 'NULL';
            }
            $review['rating'] = $studentReview->getRating();
            $result['code'] = 200;
            $result['studentReview'] = $review;
            $result[self::MESSAGE] = "Evaluation chargée avec succès !";
        } else {
        
            $result[self::MESSAGE] = "Aucune évaluation trouvée!";
            return $this->json($result);
        }
        return $result;
    }
        $result['code'] = 404;
        $result[self::MESSAGE] = "Leçon introuvable !";
        return $this->json($result,404);
    }

    public function sendReclamation(Request $request, Student $student, SessionDayCourse $sessionDayCourse)
    {
        $result = [];
        $remarque = $request->request->get('reclamation');
        $body = $this->renderView("dashboard/users/mailReclamation.html.twig", [
            'apprenti' => $student->getFirstName() . ' ' . $student->getLastName(),
            'day' => $sessionDayCourse->getDescription(),
            'body' => $remarque,
        ]);
        $admins = $this->entityManager->getRepository(User::class)->findByRole('ROLE_ADMIN');
        foreach ($admins as $admin) {
            $this->mailer->sendMail($admin->getEmail(), 'Réclamation de correction', $body);
        }
        $result['code'] = 1;
        $result[self::MESSAGE] = "Réclamation envoyée avec succès !";
        return $result;
    }

    public function saveResourceRecommendation(Request $request, User $user, SessionResources $sessionResources=null)
    {
        if($sessionResources){
        $result = [];
        $score = $request->request->get('score');
        $resourceRecommendentaion = $this->entityManager->getRepository(ResourceRecommendation::class)->findOneBy(['apprentice' => $user, 'resource' => $sessionResources]);
        if ($resourceRecommendentaion) {
            $result['code'] = 400;
            $result[self::MESSAGE] = "Vous avez déjà évalué cette ressource !";
            return $this->json($result,400);
        } else {
            $resourceRecommendentaion = new ResourceRecommendation();
            $resourceRecommendentaion->setApprentice($user)
                ->setResource($sessionResources)
                ->setScore($score);
            $this->entityManager->persist($resourceRecommendentaion);
            $this->entityManager->flush();
            $result['code'] = 200;
            $result[self::MESSAGE] = "Evaluation enregistrée avec succès !";
            return $result ;
        }

    }    else {
        $result['code'] = 404;
        $result[self::MESSAGE] = " Ressource introuvable  !";
        return $this->json($result, 404);
    }}

    public function listCorrections(Student $student)
    {
        $result = [];
        $sessionInProgress = $this->sessionUserDataRepository->findSessionInProgressByUser($student);
        if ($sessionInProgress) {
            $session = $sessionInProgress->getSession();

            $dayIdsPassed = $this->associateDateService->getPassedDateDayArrayId($session);
            $dayIdsPassed = $this->removeValidatingDayForCorection($session, $dayIdsPassed);
            $daysValidation = $this->entityManager->getRepository(SessionDayCourse::class)->findDaysValidation($dayIdsPassed);
            $validationsList = $this->createValidationsMetric($daysValidation, $student);
            $result[self::CORRECTIONS] = $validationsList;
            $result[self::MESSAGE] = 'Corrections chargées avec succès !';
            return $this->json($result, 200);
        }
        $result[self::MESSAGE] = 'Pas de session en cours !';
        return $this->json($result, 404);

    }

    public function removeValidatingDayForCorection(Session $session, $dayArray)
    {
        $currentAndPreviousDay = $this->associateDateService->getCurrentDayAndPreviousDay($session);
        $currentDay = $currentAndPreviousDay[self::CURRENT_DAY];
        $previousDay = $currentAndPreviousDay['previousDay']['day'];
        $indexCurrentDay = 0;
        $indexPreviousDay = 0;
        for ($i = 1; $i < count($dayArray); $i++) {
            if (!is_null($currentDay) && $dayArray[$i] == $currentDay->getId()) {
                $indexCurrentDay = $i;
            }
            if (!is_null($previousDay) && $dayArray[$i] == $previousDay->getId()) {
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

    public function createValidationsMetric($daysValidation, $user)
    {
        $daysValidationList = [];
        foreach ($daysValidation as $dayValidation) {
            $result = [];
            $result['course'] = $dayValidation->getDescription();
            $result['courseId'] = $dayValidation->getId();
            $result[self::MODULE] = $dayValidation->getModule()->getTitle();
            $result[self::EXCEPTION] = 'notSubmitted';
            $submission=$this->entityManager->getRepository(SubmissionWorks::class)->findOneBy(['course' => $dayValidation, 'student' => $user]);
            if ($submission) {
                $result['repoLink']=$submission->getRepoLink();
                $result[self::EXCEPTION] = 'notCorrected';
                $dayCorrection = $this->entityManager->getRepository(SessionDayCourse::class)->findOneBy([self::MODULE => $dayValidation->getModule(), self::ORDRE => $dayValidation->getOrdre() + 1]);
                $corrections = $this->entityManager->getRepository(Correction::class)->findBy([self::CORRECTED => $user, 'day' => $dayCorrection]);
                $tmp = [];
                if ($corrections) {
                    $result[self::EXCEPTION] = 'notException';
                    foreach ($corrections as $correction) {
                        $correctionDetail = [];
                        $correctionDetail['corrector'] = $correction->getCorrector()->getFullName();
                        $correctionDetail['comment'] = $correction->getComment();
                        $correctionResultsArray = $correction->getCorrectionResults();
                        $ordersAndResults = [];
                        foreach ($correctionResultsArray as $correctionResult) {
                            $correctionResults = [];
                            $correctionResults['order'] = $correctionResult->getOrderCourse()->getDescription();
                            $correctionResults['result'] = $correctionResult->getResult();
                            $ordersAndResults[] = $correctionResults;
                        }
                        $correctionDetail['ordersAndResult'] = $ordersAndResults;
                        $tmp[] = $correctionDetail;
                    }
                    $result[self::CORRECTIONS] = $tmp;

                    $score = $this->averageService->calculateDayScore($dayCorrection, $user);
                    if ($score) {
                        $moy = 0;
                        if ($score['total'] != 0) {
                            $moy = $score['note'] * 100 / $score['total'];
                            $moy = round($moy);
                        }
                        $result['average'] = $moy;
                    }
                }

            }
            $daysValidationList[] = $result;
        }

        return $daysValidationList;
    }

    public function dashboardApprentice(User $user)

    {

        $result = [];

        $sessionUserData = $this->sessionUserDataRepository->findSessionInProgressByUser($user);

        if ($sessionUserData) {

            $stat = $this->apprentiService->getDashboardApprentiStat($sessionUserData);

            $result['dashboard'] = $stat;

            $result['code'] = 200;

            $result[self::MESSAGE] = "Bilan chargé avec succès !";

        } else {

            $result['code'] = 404;

            $result[self::MESSAGE] = "Pas de session en cours !";

            return $this->json($result,404);

        }

        return $result;



    }
    public function checkWatingSession(User $user)
    {
        
        $result = [];
        $sessionUserData = $this->sessionUserDataRepository->findSessionsWaitingByUser($user);

        if ($sessionUserData) {
            $result['code'] = 200;
            $result['present'] = true;
            $result[self::MESSAGE] = "Session en attente chargé avec succès !";
            
        } else {
            $result['code'] = 200;
            $result['present'] = false;
            $result[self::MESSAGE] = "Pas de session en attente";
        }
        return $this->json($result, $result['code']);
    }



    public function saveStudent(Student $student, $data)
    {
        $student->setFirstName(ucfirst(isset($data["firstName"])?$data["firstName"]:$student->getFirstName()));
        $student->setLastName(ucfirst(isset($data["lastName"])?$data["lastName"]:$student->getLastName()));
        $student->setLinkedin( isset($data["linkedin"])?$data["linkedin"]:$student->getLinkedin());
        $student->setTel(isset($data["tel"])?$data["tel"]:$student->getTel());

        $this->entityManager->persist($student);
        $this->entityManager->flush();
    }

    public function editProfile(Request $request, Student $student)
    {
        $result = [];
        $data = $request->request->all();
        $form = $this->createForm(StudentType::class, $student);
        $form->submit($data,false);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->saveStudent($student, $data);
            $result['result'] = $student;
            $result['code'] = 200;
            $result ['message'] = "Données modifiées avec succès !";
            return $result;
        }
        $result['code'] = 400;
        $result ['message'] = $this->formErrorsService->getErrorsFromForm($form);
        return $this->json($result,400);
    }


//    public function editEmail(Request $request, User $user) {
//        $result = [];
//        $data = $request->request->all();
//
//            $user->setEmail($data['username']);
//            $this->entityManager->persist($user);
//            $this->entityManager->flush();
//            $result['result'] = $user;
//            $result['code'] = 1;
//            $result ['message'] = "Email modifié avec succès !";
//
//            return $result;
//    }
    public function editPassword(Request $request, User $user)
    {
        $result = [];
        $data = $request->request->all();
        $form = $this->createForm(UserEditPasswordType::class);
        if ($this->passwordEncoder->isPasswordValid($user, $data['oldpassword'])) {
            if ($data['oldpassword'] != $data['password']) {
                $form->submit($data);

                if ($form->isSubmitted() && $form->isValid()) {
                    $user->setPassword($this->passwordEncoder->encodePassword($user, $data['password']));
                    $this->entityManager->persist($user);
                    $this->entityManager->flush();
                    $result['result'] = $user;
                    $result['code'] = 200;
                    $result ['message'] = "Mot de passe modifié avec succès !";
                    return $result;
                }
                $result['code'] = 400;
                $result ['message'] = ($this->formErrorsService->getErrorsFromForm($form));
                return $this->json($result, 400);

            } else {
                $result['code'] = 400;
                $result ['message'] = "Choisir un mot de passe différent du mot de passe actuel ! ";
                return $this->json($result, 400);}}



            $result['code'] = 400;
            $result ['message'] = "Vérifier votre mot de passe actuel ! ";
            return $this->json($result, 400);

    }
public function getCursusFromModule ($id)
    {
        /**
         * @var SessionModule $module
         */
        $module = $this->entityManager->getRepository(SessionModule::class)->find($id);
        return $module->getSession()->getCursus();
    }

    public function saveImage(User $user, Request $request)
    {
        if(in_array($request->files->get('image')->getMimeType(),["image/png", "image/jpeg"])) {
            $fileName = $this->imageUserUploadService->upload($request->files->get('image'), $user);
            $user->setImage($fileName);

            $this->entityManager->persist($user);
            $this->entityManager->flush();
            $result['code'] = 200;
            $result ['message'] = "Photo de profil modifiée avec succès !";
        } else {
            $result['code'] = 400;
            $result ['message'] = "Veuillez sélectionner une image de type jpeg ou png.";
        }
        return $result;
    }

    public function getImage(User $user)
    {
        $imagePath = $this->getParameter('image_user_directory');
        $imageName = $user->getImage();
        if ($imageName && file_exists($imagePath . $imageName)) {
            return new BinaryFileResponse($imagePath . $imageName);
        }
        return false;
    }

    public function getCursusImage(Cursus $cursus)
    {
        $imagePath = $this->getParameter(self::CURSUS_DIRECTORY);
        $imageName = $cursus->getImage();
        if ($imageName && file_exists($imagePath . $imageName)) {
            return new BinaryFileResponse($imagePath . $imageName);
        }
        $imagePathDefault = $this->getParameter(self::CURSUS_DIRECTORY_DEFAULT);
        return new BinaryFileResponse($imagePathDefault);
    }

    public function getCourseBySession(Request $request,Session $session = null,$user){
        $passedSession = true;
        if (!$session) {
            $passedSession = false;
            $sessionUser = $this->sessionUserDataRepository->findSessionInProgressByUser($user);
            if ($sessionUser) {
                $session = $sessionUser->getSession();
            } else {
                return $this->json(['code'=>200,'message'=>"Pas de session en cours !"]);
            }

        }
//        $modules=$this->entityManager->getRepository(SessionModule::class)->findModulesSession($session);
        $modules=$this->entityManager->getRepository(SessionModule::class)->findBy(['session'=>$session]);
        $listModules=[];
        foreach ($modules as $module){
            $listCourses=[];
            $courses=$this->entityManager->getRepository(SessionDayCourse::class)->findBy(['module'=>$module]);
            foreach ($courses as $course){
                $listRessources=[];
                $ressources=$this->entityManager->getRepository(SessionResources::class)->findBy(['day'=>$course]);
                foreach ($ressources as $ressource){
                    array_push($listRessources,[
                        'id'=>$ressource->getId(),
                        'url'=>$ressource->getUrl(),
                        'title'=>$ressource->getTitle(),
                        'score'=>$this->resourceRecommendationExtension->cursusResourceScore($ressource->getRef()),
                        'voted'=>$this->checkRecommendation($ressource, $user)
                    ]);
                }
                $listActivities=[];
                $activities=$this->entityManager->getRepository(SessionActivityCourses::class)->findBy(['day'=>$course]);
                foreach ($activities as $activity){
                    array_push($listActivities,[
                        'title'=>$activity->getTitle(),
                        'content'=>$activity->getContent()
                    ]);
                }

                array_push($listCourses,[
                    'id'=>$course->getId(),
                    'order'=>$course->getOrdre(),
                    'description'=>$course->getDescription(),
                    'status'=>$course->getStatus(),
                    'synopsis'=>$course->getSynopsis(),
                    'ressources'=>$listRessources,
                    'activities'=>$listActivities]);
            }
            usort($listCourses, function($a, $b) {
                return $a['order'] - $b['order'];
            });
           
            array_push($listModules,[
                'title'=>$module->getTitle(),
                'order'=>$module->getOrderModule(),
                'description'=>$module->getDescription(),
                'type'=>$module->getType(),
                'DayCourses'=>$listCourses]);
                usort($listModules, function($a, $b) {
                    return $a['order'] - $b['order'];
                });
        }

        $days = $this->associateDateService->getDayDateArray($session);
        $currentDayId = Null;
        $currentDayDate = Null;
        for ($i = count($days) - 1; $i >= 0; $i--) {
            if ($days[$i]['date']->setTime(0, 0) <= ((new DateTime())->setTime(0, 0))) {
                $currentDayId = $days[$i]['id'];
                $currentDayDate = $days[$i]['date'];
                break;
            }
        }
        if ($passedSession) {
            $currentDay = $this->sessionDayCourseRepository->find($days[0]['id']);
            $currentDayDate = $days[0]['date'];
        } else {
            $currentDay = $this->sessionDayCourseRepository->find($currentDayId);
        }
        $currentModuleTitle=$currentDay->getModule()->getTitle();
        $currentModuleId=$currentDay->getModule()->getId();

        $cuurentLessonTitle=$currentDay->getDescription();
        $cuurentLessonId=$currentDay->getId();

        $currentSessionDay=['currentModuleId'=>$currentModuleId,'currentModuleTitle'=>$currentModuleTitle,'dayLessonId'=>$cuurentLessonId,'dayLessonTitle'=>$cuurentLessonTitle];
        return
            [
                'code'=>200,
                self::CURRENT_SESSION=>$session,
                self::LIST_MODULES => $listModules,

                self::CURRENT_DAY => $currentSessionDay,
            ];
    }


}

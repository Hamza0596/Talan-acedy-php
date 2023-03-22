<?php
/**
 * Created by PhpStorm.
 * User: jghada
 * Date: 04/06/2020
 * Time: 10:08
 */

namespace App\Controller\API\Apprenti;

use App\Entity\Affectation;
use App\Entity\Correction;
use App\Entity\Cursus;
use App\Entity\DayCourse;
use App\Entity\Module;
use App\Entity\ResourceRecommendation;
use App\Entity\Resources;
use App\Entity\Session;
use App\Entity\SessionDayCourse;
use App\Entity\SessionModule;
use App\Entity\SessionResources;
use App\Entity\Student;
use App\Entity\StudentReview;
use App\Entity\SubmissionWorks;
use App\Entity\User;
use App\Repository\DayCourseRepository;
use App\Repository\SessionActivityCoursesRepository;
use App\Repository\SessionDayCourseRepository;
use App\Repository\SessionUserDataRepository;
use App\Repository\StudentRepository;
use App\Repository\StudentReviewRepository;
use App\Service\ApprentiAPIService;
use App\Service\ApprentiService;
use App\Service\AssociateDateService;
use App\Service\ResourceRecommendationExtension;

use App\Service\CandidatureApiService;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use DateTime;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class ApprentiAPIController
 * @package App\Controller
 * @Rest\Route("/apprentice")
 */

class ApprentiAPIController extends AbstractController
{

    /**
     * @var ApprentiAPIService
     */
    private $apprentiApiService;
    /**
     * @var SessionActivityCoursesRepository
     */
    private $sessionActivityCoursesRepository;
    /**
     * @var CandidatureApiService
     */
    private $candidatureApiService;

    /**
     * @var StudentRepository
     */
    private $studentRepository;
    /**
     * @var ResourceRecommendationExtension
     */
    private $resourceRecommendationEService;

    private $entityManager;

    private $dayCourseRepository;
    private $sessionDayCourseRepository;
    private $security;
    private $sessionUserDataRepository;
    private $associateDateService;
    private $studentReviewRepository;
    private $apprentiService;

    public function __construct(ApprentiAPIService               $apprentiApiService,
                                AssociateDateService             $associateDateService,
                                SessionActivityCoursesRepository $sessionActivityCoursesRepository,
                                CandidatureApiService            $candidatureApiService,
                                StudentRepository                $studentRepository,
                                EntityManagerInterface           $entityManager,
                                DayCourseRepository              $dayCourseRepository,
                                Security                         $security,
                                SessionDayCourseRepository       $sessionDayCourseRepository,
                                SessionUserDataRepository        $sessionUserDataRepository,
                                StudentReviewRepository          $studentReviewRepository,
                                ApprentiService                  $apprentiService,
                                ResourceRecommendationExtension $resourceRecommendationEService)
    {
        $this->apprentiApiService = $apprentiApiService;
        $this->sessionActivityCoursesRepository = $sessionActivityCoursesRepository;
        $this->candidatureApiService = $candidatureApiService;
        $this->studentRepository = $studentRepository;
        $this->entityManager = $entityManager;
        $this->dayCourseRepository = $dayCourseRepository;
        $this->sessionDayCourseRepository = $sessionDayCourseRepository;
        $this->security = $security;
        $this->sessionUserDataRepository = $sessionUserDataRepository;
        $this->associateDateService = $associateDateService;
        $this->studentReviewRepository = $studentReviewRepository;
        $this->apprentiService = $apprentiService;
        $this->resourceRecommendationEService = $resourceRecommendationEService;
    }


    /**
     *      API for user profile
     *       get user informations for user profile / edit profile
     *      get-update user images
     *      edit password
     *      get informations for displaying cards in the dashboard
     *      
     */

    /**
     * @Rest\Get("/profile")
     * @Rest\View()
     * @return array
     */
    public function dashboardApprentice()
    {
        $user = $this->security->getUser();
        return $this->apprentiApiService->dashboardApprentice($user);

    }
     
/**
     * @Rest\Patch("/profile")
     * @Rest\View(serializerGroups={"user"})
     * @param Request $request
     * @return array
     */
    public function editProfile(Request $request)
    {
        $user = $this->security->getUser();
        return $this->apprentiApiService->editProfile($request, $user);
    }

/**
     * @Rest\Get("/profile/image")
     * @Rest\View(serializerGroups={"user"})
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function getImage()
    {
        $user = $this->security->getUser();
        return $this->apprentiApiService->getImage($user);
    }

/**
     * @Rest\Post("/profile/image")
     * @Rest\View()
     * @param User $user
     * @param Request $request
     * @return array
     */
    public function saveImage(Request $request)
    {
        $user = $this->security->getUser();
        return $this->apprentiApiService->saveImage($user, $request);
    }

   
  /**
     * @Rest\Patch("/profile/password")
     * @Rest\View(serializerGroups={"user"})
     * @param Request $request
     * @return array
     */
    public function editPassword(Request $request)
    {
        $user = $this->security->getUser();
        return $this->apprentiApiService->editPassword($request, $user);
    }



    /**
     * day content for dashboard
     * @Rest\Get("/dashboard/{id}")
     * @Rest\View()
     * @param Student $student
     * @return array
     */
    public function getCurrentDays(Student $student)
    {
        return $this->apprentiApiService->findCurrentDaysByUser($student);

    }


      /**
     * @Rest\Get("/session-content")
     * @Rest\View(serializerGroups={"current_session"})
     */
    public function getCourseBySession( Request $request, Session $session = null)
    {
        $user = $this->security->getUser();
        return $this->apprentiApiService->getCourseBySession($request, $session, $user);
    }


    /**
     *  APIs for user work submission and corrections 
     *  get corrections to do for user
     *  add correction 
     * list coorections (the corrections done by other for user)
     * send reclamation for user
     */

    /**
     * @Rest\Get("/corrections")
     * @Rest\View()
     * @return array
     */

     public function getCorrectionsByDay()
     {
         $user = $this->security->getUser();
         $sessionDayCourseId = $this->getCurrentDays($user)['currentDay']['id'];
         $sessionDayCourse = $this->entityManager->getRepository(SessionDayCourse::class)->find($sessionDayCourseId);
         return $this->apprentiApiService->getCorrectionsByDay($user, $sessionDayCourse);
     }
 
     /**
      * @Rest\Post("/corrections")
      * @Rest\View()
      * @param Request $request
      * @return array
      */
 
     public function saveCorrection(Request $request)
     {
         $user = $this->security->getUser();
         $sessionDayCourseId = $this->getCurrentDays($user)['currentDay']['id'];
         $sessionDayCourse = $this->entityManager->getRepository(SessionDayCourse::class)->find($sessionDayCourseId);
         return $this->apprentiApiService->saveCorrection($user, $request, $sessionDayCourse);
     }

     /**
     * @Rest\Get("/corrections-all/{id}")
     * @Rest\View()
     * @param Student $student
     * @return array
     */
    public function listCorrections(Student $student)
    {
        return $this->apprentiApiService->listCorrections($student);
    }
 

     /**
      * @Rest\Post("/submission/{dayId}")
      * @ParamConverter("sessionDayCourse", options={"id"="dayId"})
      * @Rest\View()
      * @param Request $request
      * @param SessionDayCourse $sessionDayCourse
      * @return array
      */
     public function submitWork(Request $request, SessionDayCourse $sessionDayCourse = null)
     {
         $student = $this->security->getUser();
         return $this->apprentiApiService->submitWork($request, $student, $sessionDayCourse);
     }


      /**
     * @Rest\Post("/claim/{id}/{dayId}")
     * @ParamConverter("sessionDayCourse",options={"id"="dayId"})
     * @Rest\View()
     * @param Request $request
     * @param Student $student
     * @param SessionDayCourse $sessionDayCourse
     * @return array
     */
    public function sendReclamation(Request $request, Student $student, SessionDayCourse $sessionDayCourse)
    {
        return $this->apprentiApiService->sendReclamation($request, $student, $sessionDayCourse);
    }


     /**
      *     APIs for user day review
      */
 
     /**
      * @Rest\Post("/review/{dayId}")
      * @ParamConverter("sessionDayCourse",options={"id"="dayId"})
      * @Rest\View()
      * @param Request $request
      * @param Student $student
      * @param SessionDayCourse $sessionDayCourse
      * @return array
      */
     public function saveStudentReview(Request $request, SessionDayCourse $sessionDayCourse = null)
     {
         $student = $this->security->getUser();
         return $this->apprentiApiService->saveStudentReview($request, $student, $sessionDayCourse);
     }
 
     /**
      * @Rest\Get("/review/{dayId}")
      * @ParamConverter("sessionDayCourse",options={"id"="dayId"})
      * @Rest\View()
      * @param SessionDayCourse $sessionDayCourse
      * @return array
      */
     public function getStudentReview(SessionDayCourse $sessionDayCourse=null)
     {
         $student = $this->security->getUser();
         return $this->apprentiApiService->getStudentReview($student, $sessionDayCourse);
     }
 
      /**
     * resource rating (like / dislike)
     * @Rest\Post("/resource/recommendation/{resourceId}")
     * @ParamConverter("sessionResources",options={"id"="resourceId"})
     * @Rest\View()
     * @param Request $request
     * @param SessionResources $sessionResources
     * @return array
     */
    public function saveResourceRecommendation(Request $request, SessionResources $sessionResources=null)
    {
        $user = $this->security->getUser();
        return $this->apprentiApiService->saveResourceRecommendation($request, $user, $sessionResources);
    }

    /**
      *     APIs for user proposed resources 
      */
 

  /**
     * @Rest\Get("/resources")
     * @Rest\View()
     * @return array
     */
    public function getProposedResources()
    {
        $user = $this->security->getUser();
        return $this->apprentiApiService->getProposedResources($user);
    }

    /**
     * @Rest\Post("/resources/{dayId}")
     * @ParamConverter("sessionDayCourse", options={"id"="dayId"})
     * @Rest\View()
     * @param Request $request
     * @param User $user
     * @param SessionDayCourse $sessionDayCourse
     * @return array
     */
    public function saveProposedResource(Request $request, SessionDayCourse $sessionDayCourse = null)
    {
        $user = $this->security->getUser();
        return $this->apprentiApiService->saveResource($request, $user, $sessionDayCourse);

    }

   



    //////////////////////////////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////////////////
    //not used

    /**
     * @Rest\Get("/modules/{id}/{dayId}")
     * @ParamConverter("sessionDayCourse",options={"id"="dayId"})")
     * @Rest\View()
     * @param Student $student
     * @param SessionDayCourse $sessionDayCourse
     * @return array
     */
    public function getModulesByDay(Student $student, SessionDayCourse $sessionDayCourse)
    {
        return $this->apprentiApiService->getModules($student, $sessionDayCourse);
    }

    /**
     * @Rest\Get("/activities/{id}")
     * @Rest\View()
     * @param SessionDayCourse $sessionDayCourse
     * @return array
     */

    public function getActivities(SessionDayCourse $sessionDayCourse)
    {
        $activities = $this->sessionActivityCoursesRepository->getActivitiesByDay($sessionDayCourse);
        $result = [];
        $result['activities'] = $activities;
        $result['code'] = 1;
        $result['message'] = 'activités chargées avec succés !';
        return $result;
    }


    /**
     * @Rest\Get("/sessionParameter/{id}/{sessionId}")
     * @ParamConverter("session", options={"id" = "sessionId"})
     * @Rest\View()
     * @param User $user
     * @param Session $session
     * @return array
     */
    public function getSessionParameter(User $user, Session $session)
    {
        return $this->apprentiApiService->getSessionParameter($user, $session);
    }

    /**
     * @Rest\Post("/sessionParameter/{id}/{sessionId}")
     * @ParamConverter("session", options={"id" = "sessionId"})
     * @Rest\View()
     * @param Request $request
     * @param User $user
     * @param Session $session
     * @return array
     */

    public function saveSessionParameter(Request $request, User $user, Session $session)
    {
        return $this->apprentiApiService->saveSessionParameter($request, $user, $session);
    }


   
    


   

    /**
     * @Rest\Post("/apply/{id}")
     * @Rest\View(serializerGroups={"user", "candidature", "cursus"})
     * @param User $user
     * @param Request $request
     * @return array
     */
    public function applyCursus(User $user, Request $request)
    {
        return $this->candidatureApiService->applyCursus($user, $request);
    }

    

//    /**
//     * @Rest\Patch("/edit-email")
//     * @Rest\View(serializerGroups={"user"})
//     * @param Request $request
//     * @param Student $student
//     * @return array
//     */
//    public function editEmail(Request $request) {
//        $student = $this->security->getUser();
//        return $this->apprentiApiService->editEmail($request, $student);
//    }

  

    /**
     * @Rest\get("/get-users")
     * @Rest\View(serializerGroups={"user"})
     * @return array
     */
    public function getUsers()
    {
        return $this->studentRepository->findAll();
    }

    /**
     * @Rest\get("/get-cursus/{id}")
     * @Rest\View(serializerGroups={"cursus"})
     * @param $id
     * @return Cursus|null
     */
    public function getCursusByModule($id)
    {
        return $this->apprentiApiService->getCursusFromModule($id);
    }

    /**
     * @Rest\get("/get-course/{sessionDayCourse}")
     * @Rest\View(serializerGroups={"day"})
     * @param $id
     * @return \App\Entity\DayCourse|null
     */
    public function getDayCourseByReference(SessionDayCourse $sessionDayCourse)
    {
        return $this->dayCourseRepository->findOneBy(['reference' => $sessionDayCourse->getReference()]);
    }

    
    /**
     * @Rest\Get("/curriculum/image/{id}")
     * @Rest\View()
     * @param Cursus $cursus
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function getImageCursus(Cursus $cursus)
    {
        return $this->apprentiApiService->getCursusImage($cursus);
    }

   

    /**
     * @Rest\Post("/edit-formation/{id}/{cursusId}")
     * @ParamConverter("cursus", options={"id"="cursusId"})
     * @Rest\View()
     * @param Request $request
     * @param User $user
     * @param Cursus $cursus
     * @return mixed
     */
    public function editCandidateFormation(Request $request, User $user, Cursus $cursus)
    {
        return $this->candidatureApiService->editCandidateFormation($request, $user, $cursus);
    }

  
    
}
<?php

namespace App\Controller\API\Admin;

use App\Entity\Candidature;
use App\Entity\Session;
use App\Entity\Cursus;
use App\Entity\User;

use App\Entity\SessionUserData;
use App\Service\AdminDashboardService;
use App\Service\AdminStaticService;
use App\Service\StatisticalCursusService;

use App\Service\DataTableService;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use FOS\RestBundle\Controller\Annotations as Rest;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;


/**
 * Class AdminDashboardController
 * @package App\Controller
 * @Rest\Route("/admin")
 */
class AdminDashboardController
{
    const COLUMN = 'columns';

    private $adminDashboardService;
    private $manager;
    private $adminStaticService;
    private $statisticalCursusService;

    public function __construct(AdminDashboardService $adminDashboardService,
                                EntityManagerInterface $manager, 
                                 AdminStaticService $adminStaticService,
                                 StatisticalCursusService $statisticalCursusService)
    {
        $this->adminDashboardService=$adminDashboardService;
        $this->manager=$manager;
        $this->adminStaticService=$adminStaticService;
        $this->statisticalCursusService=$statisticalCursusService;
    }

    /**
     * get sttaistcis for Admin dashboard
     * @Rest\Get("/cursus/statistics")
     * @Rest\View()
     */
    public function dashAdmin() :JsonResponse
    {
        $countCursusSession = $this->adminDashboardService->countCursusSession()['cursus'];
        $candidats = $this->manager->getRepository(Candidature::class)
            ->countAcceptedCandidature();
        $apprentis = $this->manager->getRepository(SessionUserData::class)
            ->getSessionInfoForAdmin(SessionUserData::APPRENTI);
        $qualified = $this->manager->getRepository(SessionUserData::class)
            ->getSessionInfoForAdmin(SessionUserData::QUALIFIED);
        $eliminated = $this->manager->getRepository(SessionUserData::class)
            ->getSessionInfoForAdmin(SessionUserData::ELIMINATED);
        $sessionsInProgressArray = $this->manager->getRepository(Session::class)
            ->findSessionsInProgress();
        $sessionsInProgress = count($sessionsInProgressArray);
        $sessionFinished = $this->manager->getRepository(Session::class)->findSessionFinished();
        $sessionPlanned = $this->manager->getRepository(Session::class)->findSessionPlanned();
        $successRate = $this->adminStaticService->getSuccessRateCursus();

        return new JsonResponse([
                    'code '=> 200,
                    'message'=>'Statistiques des cursus chargés avec succès' ,
                    'cursus' => $countCursusSession,
                    'candidats' => $candidats,
                    'apprenti' => $apprentis,
                    'corsaire' => $qualified,
                    'sessionsInProgress' => $sessionsInProgress,
                    'sessionFinished' => $sessionFinished,
                    'sessionPlanned' => $sessionPlanned,
                    ]);
    }

    /**
     * get all users 
     * @Rest\Get("/users")
     * @Rest\View(serializerGroups={"users"})
     * @return array
     */
    public function fetchAll()
    {
       return $this->adminDashboardService->getUsers();

    }

     /**
     * Activate user
     *
     * @Rest\Post("/users/{id}/isactivated", name="user_edit_isactivated")
     * @param User $user
     * @return JsonResponse
     */
    public function activateUser(User $user)
    {
        $status = $user->getIsActivated() === true ? false : true;
        $user->setIsActivated($status);
        $this->manager->flush();
        return new JsonResponse([
            'isActivated' => $status,
            'status' => 201,
            'message' => "Le statut a été mis à jour avec succès !"]
            , 201);
        }

         /**
     * Activate user
     *
     * @Rest\Post("/users/{id}/email", name="user_edit_email")
     * @param User $user
     * @return JsonResponse
     */
    public function editUserEmail(Request $request, User $user)
    {
            return $this->adminDashboardService->editUserEmail($request, $user);
    }

     /**    
     * @Rest\Post("/users")
     * @Rest\View()
     * @param Request $request
     * @return array
     */


    public function addStaff(Request $request)
    {
       return $this->adminDashboardService->addStaff($request);

    }

    

    /**
     * get all sessions for admin dashboard
     *@Rest\Get("/sessions")
     * @return JsonResponse
     */
    public function dataTableSessionList(Request $request, AdminDashboardService $adminDashboardService):JsonResponse
    {
       
        $finalOutput = $adminDashboardService->adminDashboardSessionList();

        return  new JsonResponse([
            'code'=> 200,
            'message'=>'Données des sessions chargées avec succès' ,
            'result'=>$finalOutput
        ]);


    }


     /**
     * get all candidates data 
     * @Rest\Get("/candidates")
     * @Rest\View(serializerGroups={"candidature"})
     * @return array
     */
    public function fetchAllCandidates()
    {
       return $this->adminDashboardService->getCandidates();

    }

}

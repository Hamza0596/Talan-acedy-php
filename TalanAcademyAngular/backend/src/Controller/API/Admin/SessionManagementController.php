<?php

namespace App\Controller\API\Admin;

use App\Entity\Session;
use App\Entity\Student;
use App\Entity\SessionMentor;
use App\Service\AssociateDateService;
use App\Service\ApprentiService;
use App\Service\CalculateAverageService;
use App\Service\StaticalModuleSessionService;
use App\Service\SessionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\Annotations as Rest;


class SessionManagementController extends AbstractController
{
    const SESSION = 'session';
    const ERRORS = 'error';
    private $session;
    private $sessionService;
    private $averageService;

    public function __construct(SessionInterface $session,
                                SessionService $sessionService,
                                CalculateAverageService $averageService,
                                ApprentiService $apprentiService)
    {
        $this->session = $session;
        $this->sessionService = $sessionService;
        $this->averageService = $averageService;
        $this->apprentiService = $apprentiService;

    }

    /**
     * get sessions details for admin and mentor
     * 
     * @param Session $session
     * 
     * @Rest\Get("/admin/session/{id}")
     * @Rest\Get("/mentor/session/{id}", name="modules_session_mentor")
     */
    public function sessionDetails(Session $session)
    {
       /* $user = $this->getUser();
       
        if (true === $authorizationChecker->isGranted('ROLE_ADMIN')) {
            $params = ['id' => $session->getCursus()->getId()];
 
        } elseif (true === $authorizationChecker->isGranted('ROLE_MENTOR')) {
            $sessionMentor = $manager->getRepository(SessionMentor::class)->findOneBy(['session' => $session, 'mentor' => $user]);

        }

*/
        
        $students = $this->sessionService->getSessionStudentsDetails($session, $this->averageService);
        $reviews = $this->sessionService->getStudentsReviews($session);
        $validations = $this->sessionService->getStudentsValidations($session, $this->apprentiService, $this->averageService);

        return  new JsonResponse([
            'code'=> 200,
            'message'=>'Données des sessions chargées avec succès' ,
            'students'=>$students,
            'reviews'=>$reviews,
            'validations'=>$validations
        ]);


    }

    /**
     * get student cv for session
     * 
     * @param Session $session
     * @param Student $student
     * @ParamConverter("student", options={"id" = "iduser"})
     * @Rest\Get("/admin/users/{iduser}/cv")
     */

    
    public function getStudentSessionCv(Student $student)
    {
        return $this->sessionService->getStudentSessionCv($student);
    }

}
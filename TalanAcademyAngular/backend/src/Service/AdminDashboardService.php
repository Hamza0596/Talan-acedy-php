<?php


namespace App\Service;


use App\Entity\Session;
use App\Entity\Student;
use App\Entity\User;
use App\Entity\Staff;

use App\Form\UserEditEmailType;

use App\Entity\Cursus;
use App\Entity\SessionUserData;
use App\Repository\CursusRepository;
use App\Repository\UserRepository;
use App\Repository\CandidatureRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;



class AdminDashboardService extends AbstractController
{
    private $statisticalStudentService;
    private $statisticalCursusService;
    /**
     * @var StaticalSessionService
     */
    private $staticalSessionService;
    private $staticalCursusService;

    private $averageService;
    private $sessionService;
    private $cursusRepository;
    private $entityManager;
    private $userRepository;
    private $candidatureRepository;
     /**
     * @var FormErrorsService
     */
    private $formErrorsService;
    /**
     * AdminDashboardService constructor.
     * @param FormErrorsService $formErrorsService
     * */

    public function __construct(SessionService $sessionService, 
                                EntityManagerInterface $entityManager, 
                                StatisticalStudentService $statisticalStudentService, 
                                StatisticalCursusService $statisticalCursusService, 
                                StaticalSessionService $staticalSessionService, 
                                CalculateAverageService $averageService,
                                CursusRepository $cursusRepository,
                                UserRepository $userRepository,
                                CandidatureRepository $candidatureRepository,
                                FormErrorsService $formErrorsService )
    {
        $this->statisticalStudentService = $statisticalStudentService;
        $this->statisticalCursusService = $statisticalCursusService;
        $this->staticalSessionService = $staticalSessionService;
        $this->averageService = $averageService;
        $this->sessionService = $sessionService;
        $this->entityManager =$entityManager;
        $this->cursusRepository=$cursusRepository;
        $this->userRepository=$userRepository;
        $this->candidatureRepository=$candidatureRepository;
        $this->formErrorsService=$formErrorsService;
    }

    // getall users for /admin/users API

    public function getUsers()
    {
        return $this->userRepository->findBy([], ['registrationDate' => 'DESC']);
    }

    //addStaff for /admin/users Post API

   public function addStaff(Request $request)
   {
      
         $data= $request->request->all();

         if($this->userRepository->findOneByEmail($data['email']))
         {
            $result['message'] = "Choisir une autre adresse email !";
            return $this->json($result, 400);
         }
         $user = new Staff();
         $user->setEmail($data['email']);
         $user->setFirstName($data['firstName']);
         $user->setLastName($data['lastName']);
         $user->setToken(bin2hex(random_bytes(User::LENGHT_TOKEN)));
         $user->setPassword('admin12345');
         $user->setIsActivated(false);
        $user->setRegistrationDate(new \DateTime());
        if ($data['function'] == 'Mentor') {
            $user->setRoles([User::ROLE_MENTOR]);
            $cursus=$this->cursusRepository->findOneById($data['cursus']);
            $user->setCursus($cursus);
            $user->setFunction('mentor');
        }
        if ($data['function'] == 'Administrator') {
            $user->setRoles([User::ROLE_ADMIN]);
            $user->setCursus(null);
            $user->setFunction('administrateur');

        }
        $lastName = strtoupper($user->getLastName());
        $firstName = ucfirst(strtolower($user->getFirstName()));
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        $sub = "Talan Academy";
       /*    $mailer->sendMail($user->getEmail(), $sub, $body);
       {% extends 'dashboard/Mail/base-mail.html.twig' %}
        {% block body %}
        <p>Bonjour {{ staff.lastName }} {{ staff.firstName }} ,</p>
        <p>Vous êtes ajouté en tant que {{ staff.function | capitalize }} dans l'application <strong>TalanAcademy</strong>.<br>
            Vous devez cliquer <a href="{{ url('staff_add_password', {'token': staff.token}) }}">ICI</a> pour
            <strong>saisir votre mot de passe et activer</strong> votre compte.</p>
        <p></p>
        <p>Cordialement,<br>
        TalanAcademy</p>
        {% endblock %}*/
        $result['message'] = "L'utilisateur a été ajouté avec succès !";
        $result['email']=$user->getEmail();
        return $this->json($result, 200);
        
   }

   //Function for update user email API by admin

   public function editUserEmail(Request $request, $user)
   {
    $data = $request->request->all();
    $form = $this->createForm(UserEditEmailType::class);
    $form->submit($data);
    //Rechercher sil'email existe deja
    if ($form->isSubmitted() && $form->isValid()) {
        if ($this->userRepository->findOneByEmail($data['email'])!=null)
        {
            $result ['message'] = 'Cet email existe déjà !';
            return $this->json($result, 400);
        }
        $user->setEmail($data['email']);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        
        $result ['message'] = "Email modifié avec succès !";
        $result['email'] = $user->getEmail();
        return $this->json($result, 200);
    }

    $result ['message'] = 'La requête contient des erreurs !';
    $result ['errors'] = ($this->formErrorsService->getErrorsFromForm($form));
    return $this->json($result, 400);
}

    //getall sessions for admin dashboard

    public function adminDashboardSessionList()
    {
        $results = $this->entityManager->getRepository(Session::class)->getAllSession();
        $objects = $results["results"];
        foreach ($objects as $evaluation) {

            $session = $this->getSessionsStatics($evaluation);
            //$row = [];
            $cursus =    $evaluation->getCursus()->getName()   ;
            $sessionOrder = $evaluation->getOrdre();
        
            $startDateColumn = $evaluation->getId() ;
            $startDate= $evaluation->getStartDate()->format('d-m-Y');
            $endDate=$evaluation->getEndDate()->format('d-m-Y') ;
                
            $row = ['sessionId' =>$startDateColumn ,
            'cursus' => $cursus , 
            'sessionOrder'=> $sessionOrder, 
            'startDate' => $startDate ,
            'endDate' =>$endDate, 
            'nbrJokerTotal' => $evaluation->getJokerNbr(),
            'nbApprentis' => $this->sessionService->apprenticesCount($evaluation),  
            'countConfirmed' =>$this->entityManager->getRepository(SessionUserData::class)->countConfirmed($evaluation), 
            'averageMoy' => round($session["scoreMoy"]), 
            'stars' => number_format($session["stars"][5], 2, '.', ','),
            'percentageRating' => round($session["stars"][5] * 100 / 5),
            'advancement' => $session['advancement'] ];
           
            $finalOutput[] = $row;

        }
        $final['nbsessions'] = $results['countResult'];
        $final['sessionsInfo'] = $finalOutput;
        return $final;

    }

    public function getSessionsStatics($session1)
    {

        $staticSession = [];
        $result = [];
   
//            appel les stars
        $stars = $this->sessionService->calculateSessionRates($session1);
        $result['stars'] = $stars['stars'];
        $result['daysCountStarted'] = $stars['daysCountStarted'];
        $result['moy'] = $session1->getMoy();
        $result['ordre'] = $session1->getOrdre();
        $scores = [];
        $sum = 0;
        $apprentis = $this->entityManager->getRepository(Student::class)->findApprentisBySession($session1);
        foreach ($apprentis as $apprenti) {
            $scores[] = $this->averageService->calculateMinMaxScore($session1, $apprenti);
        }
      
        if (count($scores) != 0) {
            foreach ($scores as $score) {
                $sum += $score['average'];
            }
        }
        $scoreMoy = 0;
        if (count($scores) != 0) {
            $scoreMoy = $sum / count($scores);
        }
        $result['scoreMoy'] = round($scoreMoy);
        //rate
        $result['advancement'] =  round($this->sessionService->prog($session1), 0);
       // $staticSession [] = $result;
        return $result;
    }

    public function getCandidates()
    {
     //   return $this->candidatureRepository->getCandidatureData();
        return $this->candidatureRepository->findBy([], ['datePostule' => 'DESC']);

    }

    //////NOTUSED/////////////////////
    public function staticStudentAll()
    {
        $student = [];
        $candidate = $this->statisticalStudentService->getAllCandidate(User::ROLE_CANDIDAT);
        $student['candidate'] = $candidate;
        $apprentie = $this->statisticalStudentService->getAllCandidate(User::ROLE_APPRENTI);
        $student['apprentie'] = $apprentie;
        $corsaire = 0;
        $student['corsaire'] = $corsaire;
        $renegat = 0;
        $student['renegat'] = $renegat;
        return $student;
    }

    public function staticCursus()
    {
        return $this->statisticalCursusService->staticCursusBySession();
    }

    public function countCursusSession()
    {
        return ['cursus' => $this->statisticalCursusService->countAllCursus(), 'sessions' => $this->staticalSessionService->countAllSession()];
    }


    public function SessionCompleted()
    {
        return $this->staticalSessionService->sessionCompleted();
    }

    public function getCursus()
    {
        return $this->cursusRepository->findBy([], ['id' => 'DESC']);

    }
   

    public function getSessions($session1)
    {

        $staticSession = [];
        $result = [];
   /*     $dateStart = $session1->getStartDate();
        $dateEnd = $session1->getEndDate();
        $result ['startDate'] = $dateStart;
        if ($dateStart->format('Y-m-d') > (new \DateTime('now'))->format('Y-m-d')) {
            $result ['status'] = 'en attente';
        } elseif ($dateEnd->format('Y-m-d') < (new \DateTime('now'))->format('Y-m-d')) {
            $result ['status'] = 'terminée';
        } elseif ($dateStart->format('Y-m-d') <= (new \DateTime('now'))->format('Y-m-d') &&
            (new \DateTime('now'))->format('Y-m-d') <= $dateEnd->format('Y-m-d')) {
            $result ['status'] = 'en cours';
        }*/
//            appel les stars
        $stars = $this->sessionService->calculateSessionRates($session1);
        $result['stars'] = $stars['stars'];
        $result['daysCountStarted'] = $stars['daysCountStarted'];
        $result['moy'] = $session1->getMoy();
        $result['ordre'] = $session1->getOrdre();
        $scores = [];
        $sum = 0;
        $apprentis = $this->entityManager->getRepository(Student::class)->findApprentisBySession($session1);
        foreach ($apprentis as $apprenti) {
            $scores[] = $this->averageService->calculateMinMaxScore($session1, $apprenti);
        }
        $min = 0;
        $max = 0;
        if (count($scores) != 0) {
            $min = $scores[0]['average'];
            $max = $scores[0]['average'];
            foreach ($scores as $score) {
                $sum += $score['average'];
                if ($min > $score['average']) {
                    $min = $score['average'];
                }
                if ($max < $score['average']) {
                    $max = $score['average'];
                }
            }
        }
        $scoreMoy = 0;
        if (count($scores) != 0) {
            $scoreMoy = $sum / count($scores);
        }
        $result['scoreMoy'] = round($scoreMoy);
        $result['min'] = round($min);
        $result['max'] = round($max);
       // $staticSession [] = $result;
        return $result;
    }
    
  
  

}

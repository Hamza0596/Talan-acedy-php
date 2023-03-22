<?php


namespace App\Service;

use App\Entity\Cursus;
use App\Entity\DayCourse;
use App\Entity\StudentReview;
use App\Entity\User;
use App\Entity\Resources;
use App\Entity\ActivityCourses;
use App\Entity\ResourceRecommendation;

use App\Repository\DayCourseRepository;
use App\Repository\CursusRepository;
use App\Repository\ModuleRepository;


use App\Repository\StudentRepository;
use App\Repository\StudentReviewRepository;
use App\Service\CalculateAverageService;
use App\Service\ApprentiService;
use App\Service\ResourceRecommendationExtension;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
class CursusService extends AbstractController
{

    
    private $em;
    private $moduleRepository;
    private $cursusRepository;
    private $dayCourseRepository;
    private $studentRepository;
    private $apprentiService;
    private $resourceRecommendationExtension;

    
    public function __construct(EntityManagerInterface $em,
                                ModuleRepository $moduleRepository,
                                CursusRepository $cursusRepository,
                                DayCourseRepository $dayCourseRepository,

                                AssociateDateService $associateDateService,
                                AdminStaticService $adminStaticService,
                                LoggerInterface $logger,
                                StudentRepository $studentRepository,
                                StudentReviewRepository $studentReviewRepository,
                                ResourceRecommendationExtension $resourceRecommendationExtension
                                )
    {
        $this->em = $em;
        $this->moduleRepository = $moduleRepository;
        $this->cursusRepository = $cursusRepository;
        $this->dayCourseRepository = $dayCourseRepository;

        $this->associateDateService = $associateDateService;
        $this->adminStaticService = $adminStaticService;
        $this->logger = $logger;
        $this->studentRepository = $studentRepository;
        $this->studentReviewRepository = $studentReviewRepository;
        $this->resourceRecommendationExtension = $resourceRecommendationExtension;

    }


    // Show Cursus Details by id for /Admin/cursus/{id} API

    public function getCursusContent(Cursus $cursus)
    {
        $modules = $this->moduleRepository->findNonDeletedModule($cursus);
          //  $modules=$this->entityManager->getRepository(SessionModule::class)->findBy(['session'=>$session]);
            $listModules=[];
            foreach ($modules as $module){
                $listCourses=[];
                $courses=$this->dayCourseRepository->findBy(['module'=>$module]);
                foreach ($courses as $course){
                    $listRessources=[];
                    $ressources=$this->em->getRepository(Resources::class)->findBy(['day'=>$course]);
                    foreach ($ressources as $ressource){
                        array_push($listRessources,[
                            'id'=>$ressource->getId(),
                            'url'=>$ressource->getUrl(),
                            'title'=>$ressource->getTitle(),
                             'ownerFirstName' => $ressource->getResourceOwner()->getFirstName(),
                             'ownerLastName' => $ressource->getResourceOwner()->getLastName(),
                             'comment' => $ressource->getComment(),
                             'status' => $ressource->getStatus(),
                             'deleted' => $ressource->getDeleted(),
                             'likes' => $this->resourceRecommendationExtension->cursusResourceScore($ressource->getRef())

                        ]);
                    }
                    $listActivities=[];
                    $activities=$this->em->getRepository(ActivityCourses::class)->findBy(['day'=>$course]);
                    foreach ($activities as $activity){
                        array_push($listActivities,[
                            'id'=>$activity->getId(),
                            'title'=>$activity->getTitle(),
                            'content'=>$activity->getContent()]);
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
               
                array_push($listModules,[
                    'id'=>$module->getId(),
                    'order'=>$module->getOrderModule(),
                    'title'=>$module->getTitle(),
                    'description'=>$module->getDescription(),
                    'type'=>$module->getType(),
                    'DayCourses'=>$listCourses]);
            }

            return $listModules;
 

    }

     // Show Cursus Details by id for /Admin/cursus/{id}/pdf API

     public function getCursusContentPDF(Cursus $cursus)
     {    
         $modules = $this->moduleRepository->findNonDeletedModule($cursus);
           //  $modules=$this->entityManager->getRepository(SessionModule::class)->findBy(['session'=>$session]);
             $listModules=[];
             foreach ($modules as $module){
                 $listCourses=[];
                 $courses=$this->dayCourseRepository->findBy(['module'=>$module]);
                 foreach ($courses as $course){
                     $listRessources=[];
                     $ressources=$this->em->getRepository(Resources::class)->findBy(['day'=>$course]);
                     foreach ($ressources as $ressource){

                         array_push($listRessources,[
                             'id'=>$ressource->getId(),
                             'url'=>$ressource->getUrl(),
                             'title'=>$ressource->getTitle(),
                              'comment' => $ressource->getComment(),
                              'status' => $ressource->getStatus(),
 
                         ]);
                     }
                     $listActivities=[];
                     $activities=$this->em->getRepository(ActivityCourses::class)->findBy(['day'=>$course]);
                     foreach ($activities as $activity){
                         array_push($listActivities,[
                             'title'=>$activity->getTitle(),
                             'content'=>$activity->getContent()
                            ]);
                     }
     
                     array_push($listCourses,[
                         'order'=>$course->getOrdre(),
                         'description'=>$course->getDescription(),
                         'status'=>$course->getStatus(),
                         'synopsis'=>$course->getSynopsis(),
                         'ressources'=>$listRessources,
                         'activities'=>$listActivities
                    ]);
                 }
                
                 array_push($listModules,[
                     'order'=>$module->getOrderModule(),
                     'title'=>$module->getTitle(),
                     'description'=>$module->getDescription(),
                     'type'=>$module->getType(),
                     'DayCourses'=>$listCourses
                    ]);
             }
 


            $html = $this->renderView('activity_pdf.html.twig', [
                'modules' => $listModules,
                'day' =>'etst'
            //    'day' => $sessionDayCourse
            ]);

            $mpdf = new Mpdf();
            $mpdf->WriteHTML($html);
            $tmpFileName = (new Filesystem())->tempnam(sys_get_temp_dir(), 'ta_', '.pdf');

            $mpdf->Output($tmpFileName);
            $binaryFileResponse = new BinaryFileResponse($tmpFileName);

          //  $binaryFileResponse = new BinaryFileResponse($this->getParameter('cv_candidate_directory') . DIRECTORY_SEPARATOR . 'cursus.pdf');
            $binaryFileResponse->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE);
            $binaryFileResponse->headers->set('Content-Type', 'application/pdf');
            return  $binaryFileResponse;
  
 
     }
 

}
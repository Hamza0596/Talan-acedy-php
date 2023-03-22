<?php
/**
 * Created by PhpStorm.
 * User: sarfaoui
 * Date: 26/04/2019
 * Time: 17:51
 */

namespace App\Service;


use App\Entity\ActivityCourses;
use App\Entity\Cursus;
use App\Entity\DayCourse;
use App\Entity\Module;
use App\Entity\OrderCourse;
use App\Entity\ProjectSubject;
use App\Entity\Resources;
use App\Entity\Session;
use App\Entity\SessionActivityCourses;
use App\Entity\SessionDayCourse;
use App\Entity\SessionModule;
use App\Entity\SessionOrder;
use App\Entity\SessionProjectSubject;
use App\Entity\SessionResources;
use Doctrine\ORM\EntityManagerInterface;

class CopyCusrusToSessionService
{
    private $em;
    private $sessionService;

    public function __construct(EntityManagerInterface $em, SessionService $sessionService)
    {
        $this->em = $em;
        $this->sessionService = $sessionService;
    }

    public function copy(Cursus $cursus, Session $session)
    {
        $modules = $this->em->getRepository(Module::class)->findNonDeletedModule($cursus);
        $dayCounter = 0;
        foreach ($modules as $module) {

            $sessionModule = new SessionModule($module->serializer());
            if ($module->getType()==Module::PROJECT){
                $projectsubjects = $this->em->getRepository(ProjectSubject::class)->findNonDeletedProjectSubject($module);
                foreach ($projectsubjects as $project)
                {
                    $projectsubject = new SessionProjectSubject($project->serializer());
                    $projectsubject->setSessionProject($sessionModule);
                    $sessionModule->addSessionProjectSubject($projectsubject);
                    $this->em->persist($projectsubject);
                }

                $nbDay=$module->getDuration();
                $dayCounter += $nbDay;
                for($i=0;$i<$nbDay;$i++){
                    $sessionDay = new SessionDayCourse();
                    $sessionDay->setReference('day_' . time() . mt_rand());
                    $sessionDay->setDescription('Jour '.($i+1));
                    $sessionDay->setOrdre($i+1);
                    $sessionDay->setStatus(DayCourse::NORMAL_DAY);
                    $sessionDay->setModule($sessionModule);
                    $this->em->persist($sessionDay);
                }
            }
            $sessionModule->setSession($session);
            $this->em->persist($sessionModule);

            $days = $this->em->getRepository(DayCourse::class)->findBy(['module' => $module,'deleted'=>null]);

            foreach ($days as $day) {
                $dayCounter++;
                $sessionDay = new SessionDayCourse($day->serializer());
                $sessionDay->setModule($sessionModule);
                $this->em->persist($sessionDay);

                $activities = $this->em->getRepository(ActivityCourses::class)->findBy(['day' => $day,'deleted'=>null]);
                foreach ($activities as $activity) {
                    $sessionActivity = new SessionActivityCourses($activity->serializer());
                    $sessionActivity->setDay($sessionDay);
                    $this->em->persist($sessionActivity);

                }

                $resources = $this->em->getRepository(Resources::class)->findBy(['day' => $day, 'status' => Resources::APPROVED,'deleted'=>null]);
                foreach ($resources as $resource) {

                    $sessionResource = new SessionResources($resource->serializer());
                    $sessionResource->setDay($sessionDay);
                    $this->em->persist($sessionResource);
                }

                $orders = $this->em->getRepository(OrderCourse::class)->findBy(['dayCourse' => $day, 'deleted'=>null]);
                foreach ($orders as $order) {
                    $sessionOrder = new SessionOrder($order->serializer());
                    $sessionOrder->setDayCourse($sessionDay);
                    $this->em->persist($sessionOrder);

                }


            }

            $this->em->flush();
        }
        $this->sessionService->applaySessionEndDate($session, $dayCounter);

    }

}

<?php
/**
 * Created by PhpStorm.
 * User: jghada
 * Date: 25/02/2020
 * Time: 14:52
 */

namespace App\Service;


use App\Entity\ActivityCourses;
use App\Entity\Cursus;
use App\Entity\DayCourse;
use App\Entity\Module;
use App\Entity\OrderCourse;
use App\Entity\Resources;
use App\Entity\Session;
use App\Entity\SessionActivityCourses;
use App\Entity\SessionDayCourse;
use App\Entity\SessionModule;
use App\Entity\SessionOrder;
use App\Entity\SessionResources;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Environment;

class SessionVsCursusService
{
    /**
     * @var EntityManagerInterface
     */
    private $manager;
    /**
     * @var Environment
     */
    private $templating;
    /**
     * @var Mailer
     */
    private $mailer;

    public function __construct(EntityManagerInterface $manager, Environment $templating, Mailer $mailer)
    {

        $this->manager = $manager;
        $this->templating = $templating;
        $this->mailer = $mailer;
    }

    public function sessionVsCursusV2(Session $session)
    {
        $data = [];
        $cursus = $session->getCursus();
        $cursusModules = $cursus->getModules();

        //cursus => session

        foreach ($cursusModules as $cursusModule) {
            $cursusModuleReference = $cursusModule->getRef();
            if ($cursusModule->getDeleted() != 1) {
                $sessionModule = $this->manager->getRepository(SessionModule::class)->findOneBy(array('ref' => $cursusModuleReference, 'session' => $session));
                if ($sessionModule) {
                    $result = $this->compareModules($cursusModule, $sessionModule);
                    if (!empty($result)) {
                        $data['module'][] = $result;
                    }
                } else {
                    $data['module'][] = array('parent' => array('title' => array('attribut' => 'title', 'contenu_cursus' => $cursusModule->getTitle(), 'contenu_session' => 'null')));
                }
            }

        }

            //session => cursus
            foreach ($session->getModules() as $sessionModule) {
                $cursusModule = $this->manager->getRepository(Module::class)->findOneBy(array('ref' => $sessionModule->getRef(),'deleted'=>null));
                if (!$cursusModule) {
                    $data['module'][] = array('parent' => array('title' => array('attribut' => 'title', 'contenu_cursus' => 'null', 'contenu_session' => $sessionModule->getTitle())));
                }

        }


        $admins = $this->manager->getRepository(User::class)->findByRole('ROLE_ADMIN');

        foreach ($admins as $admin) {
            $body = $this->templating->render('dashboard/Mail/sessionVsCursus.html.twig', ['data' => $data['module'], 'cursus' => $cursus, 'session' => $session, 'admin' => $admin
            ]);
            $this->mailer->sendMail($admin->getEmail(), 'Différence entre cursus et session', $body);
        }

    }

    private function compareModules(Module $cursusModule, SessionModule $sessionModule)
    {


        $diff = $this->compareAttributes($cursusModule, $sessionModule);

        $dayCourses = $this->manager->getRepository(DayCourse::class)->findBy(['module' => $cursusModule, 'deleted' => null]);
        foreach ($dayCourses as $dayCourse) {
            $daySession = null;
            if($dayCourse->getDeleted()!=1){
                $daySession = $this->manager->getRepository(SessionDayCourse::class)->findOneBy(array('reference' => $dayCourse->getReference(), 'module' => $sessionModule));
                if ($daySession) {
                    $result = $this->compareDays($dayCourse, $daySession);
                    if (!empty($result)) {
                        $diff['children']['days'][] = $result;
                    }
                } else {
                    $diff['children']['days'][] = array('parent' => array('description' => array('attribut' => 'description', 'contenu_cursus' => $dayCourse->getDescription(), 'contenu_session' => 'null')));
                }
            }

        }
        foreach ($sessionModule->getDayCourses() as $course) {
            $cursusModule = $this->manager->getRepository(DayCourse::class)->findOneBy(['reference' => $course->getReference(),'deleted'=>null]);
            if (!$cursusModule) {
                $diff['children']['days'][] = array('parent' => array('description' => array('attribut' => 'description', 'contenu_cursus' => 'null', 'contenu_session' => $dayCourse->getDescription())));
            }
        }
        return $diff;

    }

    private function compareAttributes($cursusDetail, $sessionDetail)
    {
        $diff = [];
        if ($cursusDetail instanceof OrderCourse) {
            $cursusDetailArray = $cursusDetail->serializer();
            $sessionDetailArray = $sessionDetail->serializer();
        } elseif ($cursusDetail instanceof Module) {
            $cursusDetailArray = ['title' => $cursusDetail->getTitle(), 'description' => $cursusDetail->getDescription(), 'orderModule' => $cursusDetail->getOrderModule(), 'type' => $cursusDetail->getType(), 'duration' => $cursusDetail->getDuration()];
            $sessionDetailArray = ['title' => $sessionDetail->getTitle(), 'description' => $sessionDetail->getDescription(), 'orderModule' => $sessionDetail->getOrderModule(), 'type' => $sessionDetail->getType(), 'duration' => $sessionDetail->getDuration()];
            if ($cursusDetail->getTitle() == $sessionDetail->getTitle()) {
                $tmp['attribut'] = 'title';
                $tmp['contenu_cursus'] = $cursusDetail->getTitle();
                $tmp['contenu_session'] = $cursusDetail->getTitle();
                $diff['parent']['title'] = $tmp;
            }
        } else {
            if ($cursusDetail instanceof DayCourse) {
                if ($cursusDetail->getDescription() == $sessionDetail->getDescription()) {
                    $tmp['attribut'] = 'description';
                    $tmp['contenu_cursus'] = $cursusDetail->getDescription();
                    $tmp['contenu_session'] = $cursusDetail->getDescription();
                    $diff['parent']['description'] = $tmp;
                }
            }
            $cursusDetailArray = $cursusDetail->toArray();
            $sessionDetailArray = $sessionDetail->toArray();
        }
        $array_diff = array_diff_assoc($cursusDetailArray, $sessionDetailArray);
        if (!empty($array_diff)) {
            foreach ($array_diff as $key => $value) {
                $tmp['attribut'] = $key;
                $tmp['contenu_cursus'] = $value;
                $tmp['contenu_session'] = $sessionDetailArray[$key];
                $diff['parent'][$tmp['attribut']] = $tmp;
            }
        }
        return $diff;
    }

    private function compareDays(DayCourse $cursusDay, SessionDayCourse $sessionDay)
    {
        $diff = [];
        $diff = $this->compareAttributes($cursusDay, $sessionDay);

        //  cursus => session resource
        foreach ($cursusDay->getResources() as $resource) {
            $sessionRessource = null;
            if ($resource->getDeleted() != 1) {
                $sessionRessource = $this->manager->getRepository(SessionResources::class)->findOneBy(array('ref' => $resource->getRef(), 'day' => $sessionDay));
                if ($sessionRessource) {
                    $result = $this->compareRessources($resource, $sessionRessource);
                    if (!empty($result)) {
                        $diff['children']['ressources'][] = $result;
                    }
                } else {
                    $diff['children']['ressources'][] = array('parent' => array('title' => array('attribut' => 'title', 'contenu_cursus' => $resource->getTitle(), 'contenu_session' => 'null')));
                }
            }

        }

        //session => cursus resource

        foreach ($sessionDay->getResources() as $sessionResources) {
            $cursusResource = $this->manager->getRepository(Resources::class)->findOneBy(['ref' => $sessionResources->getRef(),'deleted'=>null]);
            if (!$cursusResource) {
                $diff['children']['ressources'][] = array('parent' => array('title' => array('attribut' => 'title', 'contenu_cursus' => 'null', 'contenu_session' => $sessionResources->getTitle())));
            }
        }
        //  cursus => session activities
        foreach ($cursusDay->getActivityCourses() as $activityCourses) {
            $sessionActivities = null;
            if ($activityCourses->getDeleted() != 1) {
                $sessionActivities = $this->manager->getRepository(SessionActivityCourses::class)->findOneBy(['reference' => $activityCourses->getReference(), 'day' => $sessionDay]);
                if ($sessionActivities) {
                    $result = $this->compareActivities($activityCourses, $sessionActivities);
                    if (!empty($result)) {
                        $diff['children']['activités'][] = $result;
                    }
                } else {
                    $diff['children']['activités'][] = array('parent' => array('title' => array('attribut' => 'title', 'contenu_cursus' => $activityCourses->getTitle(), 'contenu_session' => 'null')));
                }
            }

        }

        //session => cursus activities
        foreach ($sessionDay->getActivityCourses() as $sessionActivityCourses) {
            $cursuActivity = $this->manager->getRepository(ActivityCourses::class)->findOneBy(['reference' => $sessionActivityCourses->getReference(),'deleted'=>null]);
            if (!$cursuActivity) {
                $diff['children']['activités'][] = array('parent' => array('title' => array('attribut' => 'title', 'contenu_cursus' => 'null', 'contenu_session' => $sessionActivityCourses->getTitle())));
            }
        }

        //  cursus => session consignes
        foreach ($cursusDay->getOrders() as $order) {
            $sessionOrder = null;
            if ($order->getDeleted() != 1) {
                $sessionOrder = $this->manager->getRepository(SessionOrder::class)->findOneBy(['ref' => $order->getRef(), 'dayCourse' => $sessionDay]);
                if ($sessionOrder) {
                    $result = $this->compareOrders($order, $sessionOrder);
                    if (!empty($result)) {
                        $diff['children']['consignes'][] = $result;
                    }
                } else {
                    $diff['children']['consignes'][] = array('parent' => array('title' => array('attribut' => 'title', 'contenu_cursus' => $order->getDescription(), 'contenu_session' => 'null')));

                }
            }

        }
        //session => cursus consignes
        foreach ($sessionDay->getOrders() as $sessionOrder) {
            $cursusOrder = $this->manager->getRepository(OrderCourse::class)->findOneBy(['ref' => $sessionOrder->getRef(),'deleted'=>null]);
            if (!$cursusOrder) {
                $diff['children']['consignes'][] = array('parent' => array('title' => array('attribut' => 'title', 'contenu_cursus' => 'null', 'contenu_session' => $sessionOrder->getDescription())));
            }
        }
        return $diff;

    }

    private function compareRessources(Resources $resource, SessionResources $sessionResource)
    {
        $diff = [];
        $diff = $this->compareAttributes($resource, $sessionResource);
        return $diff;
    }

    private function compareActivities(ActivityCourses $activity, SessionActivityCourses $sessionActivity)
    {
        $diff = [];
        $diff = $this->compareAttributes($activity, $sessionActivity);
        return $diff;
    }

    private function compareOrders(OrderCourse $order, SessionOrder $sessionOrder)
    {
        $diff = [];
        $diff = $this->compareAttributes($order, $sessionOrder);
        return $diff;
    }

}

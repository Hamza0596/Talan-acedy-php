<?php
/**
 * Created by PhpStorm.
 * User: jghada
 * Date: 19/11/2019
 * Time: 10:06
 */

namespace App\Service;


use App\Entity\ActivityCourses;
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
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class CopySessionToCursusService
 * @package App\Service
 * @codeCoverageIgnore
 */
class CopySessionToCursusService
{
    /**
     * @var EntityManagerInterface
     */
    private $manager;


    /**
     * CopySessionToCursusService constructor.
     * @param EntityManagerInterface $manager
     */
    public function __construct(EntityManagerInterface $manager)
    {

        $this->manager = $manager;
    }

    //STEP 1 from session to cursus
    public function copyModules(Session $session, User $user)
    {
        $cursus = $session->getCursus();
        $sessionModules = $session->getModules();
        foreach ($sessionModules as $sessionModule) {
            $moduleCursus = $this->manager->getRepository(Module::class)->findOneBy(['ref' => $sessionModule->getRef(), 'courses' => $cursus->getId()]);
            $sessionModuleArray = ['title' => $sessionModule->getTitle(), 'ref' => $sessionModule->getRef(), 'description' => $sessionModule->getDescription(), 'orderModule' => $sessionModule->getOrderModule(), 'type' => $sessionModule->getType(), 'duration' => $sessionModule->getDuration()];
            if ($moduleCursus) {
                //compare attributes
                $moduleCursusArray = ['title' => $moduleCursus->getTitle(), 'ref' => $moduleCursus->getRef(), 'description' => $moduleCursus->getDescription(), 'orderModule' => $moduleCursus->getOrderModule(), 'type' => $moduleCursus->getType(), 'duration' => $moduleCursus->getDuration()];
                $this->updateAttributes($moduleCursus, $sessionModuleArray, $moduleCursusArray);
                $this->manager->persist($moduleCursus);

                //compare relations (dayCourses,projectSubjects)
                //1.dayCourses
                $sessionDayCourses = $sessionModule->getDayCourses();
                foreach ($sessionDayCourses as $sessionDayCourse) {
                    $dayCourse = $this->manager->getRepository(DayCourse::class)->findOneBy(['reference' => $sessionDayCourse->getReference(), 'module' => $moduleCursus->getId()]);
                    $sessionDayCourseArray = $sessionDayCourse->toArray();
                    if ($dayCourse) {
                        //compare attributes
                        $dayCourseArray = $dayCourse->toArray();
                        $this->updateAttributes($dayCourse, $sessionDayCourseArray, $dayCourseArray);

                        //compare relations (activities,resources,orders)
                        //1.activities
                        $sessionActivities = $sessionDayCourse->getActivityCourses();
                        foreach ($sessionActivities as $sessionActivity) {
                            $activity = $this->manager->getRepository(ActivityCourses::class)->findOneBy(['reference' => $sessionActivity->getReference(), 'day' => $dayCourse->getId()]);
                            $sessionActivityArray = $sessionActivity->toArray();
                            if ($activity) {
                                //compare attributes
                                $activityArray = $activity->toArray();
                                $this->updateAttributes($activity, $sessionActivityArray, $activityArray);


                            } //create new activity
                            else {
                                $activity = new ActivityCourses();
                                $this->setAttributes($sessionActivityArray, $activity);
                                $activity->setDay($dayCourse);
                                $dayCourse->addActivityCourses($activity);


                            }
                            $this->manager->persist($activity);

                        }
                        //2.resources
                        $sessionResources = $sessionDayCourse->getResources();
                        foreach ($sessionResources as $sessionResource) {
                            $resource = $this->manager->getRepository(Resources::class)->findOneBy(['ref' => $sessionResource->getRef(), 'day' => $dayCourse]);
                            $sessionResourceArray = $sessionResource->toArray();
                            if ($resource) {
                                //compare attributes
                                $ressourceArray = $resource->toArray();
                                $this->updateAttributes($resource, $sessionResourceArray, $ressourceArray);
                                $resource->setStatus(Resources::APPROVED);

                            } //create new resource
                            else {
                                $resource = new Resources();
                                $this->setAttributes($sessionResourceArray, $resource);
                                $resource->setResourceOwner($user);
                                $resource->setDay($dayCourse);
                                $resource->setStatus(Resources::APPROVED);
                                $dayCourse->addResource($resource);
                            }
                            $this->manager->persist($resource);

                        }
                        //3.orders
                        $sessionOrders = $sessionDayCourse->getOrders();
                        foreach ($sessionOrders as $sessionOrder) {
                            $order = $this->manager->getRepository(OrderCourse::class)->findOneBy(['ref' => $sessionOrder->getRef(), 'dayCourse' => $dayCourse]);
                            $sessionOrderArray = $sessionOrder->serializer();
                            if ($order) {
                                //compare attributes
                                $orderArray = $order->serializer();
                                $this->updateAttributes($order, $sessionOrderArray, $orderArray);
                            } //create new order
                            else {
                                $order = new OrderCourse();
                                $this->setAttributes($sessionOrderArray, $order);
                                $order->setDayCourse($dayCourse);
                                $dayCourse->addOrder($order);
                            }
                            $this->manager->persist($order);

                        }
                        $this->manager->persist($dayCourse);

                    } //create new dayCourse
                    else {
                        $dayCourse = new DayCourse();
                        $this->setAttributes($sessionDayCourseArray, $dayCourse);
                        $dayCourse->setModule($moduleCursus);

                        //create resources
                        $sessionResources = $sessionDayCourse->getResources();
                        foreach ($sessionResources as $sessionResource) {
                            $sessionResourceArray = $sessionResource->toArray();
                            $resource = new Resources();
                            $resource->setDay($dayCourse);
                            $this->setAttributes($sessionResourceArray, $resource);
                            $resource->setResourceOwner($user);
                            $resource->setStatus(Resources::APPROVED);
                            $dayCourse->addResource($resource);
                            $this->manager->persist($resource);
                        }

                        //create activities
                        $sessionAcitivities = $sessionDayCourse->getActivityCourses();
                        foreach ($sessionAcitivities as $sessionActivity) {
                            $sessionActivityArray = $sessionActivity->toArray();
                            $activity = new ActivityCourses();
                            $this->setAttributes($sessionActivityArray, $activity);
                            $activity->setDay($dayCourse);
                            $this->manager->persist($activity);
                            $dayCourse->addActivityCourses($activity);
                        }

                        //create Orders
                        $sessionOrders = $sessionDayCourse->getOrders();
                        foreach ($sessionOrders as $sessionOrder) {
                            $sessionOrderArray = $sessionOrder->serializer();
                            $order = new OrderCourse();
                            $order->setDayCourse($dayCourse);
                            $this->setAttributes($sessionOrderArray, $order);
                            $this->manager->persist($order);
                            $dayCourse->addOrder($order);
                        }


                    }
                    $this->manager->persist($dayCourse);
                    $moduleCursus->addDayCourse($dayCourse);


                }
                //2.projectSubject
                $sessionProjectSubjects = $sessionModule->getSessionProjectSubjects();
                foreach ($sessionProjectSubjects as $sessionProjectSubject) {
                    $projectSubject = $this->manager->getRepository(ProjectSubject::class)->findOneBy(['ref' => $sessionProjectSubject->getRef(), 'project' => $moduleCursus]);
                    $sessionProjectSubjectArray = $sessionProjectSubject->serializer();
                    if ($projectSubject) {
                        //compare attributes
                        $projectSubjectArray = $projectSubject->serializer();
                        $this->updateAttributes($projectSubject, $sessionProjectSubjectArray, $projectSubjectArray);
                    } //create new projectSubject
                    else {
                        $projectSubject = new ProjectSubject();
                        $projectSubject->setProject($moduleCursus);
                        $this->setAttributes($sessionProjectSubjectArray, $projectSubject);
                        $moduleCursus->addProjectSubject($projectSubject);


                    }
                    $this->manager->persist($projectSubject);

                }
                $this->manager->persist($moduleCursus);

            } //create new module
            else {

                $module = new Module();
                $this->setAttributes($sessionModuleArray, $module);
                $this->manager->persist($module);
                $this->manager->flush();


                //create relations (dayCourse , projectSubject)
                //1.dayCourse
                $sessionDayCourses = $sessionModule->getDayCourses();

                foreach ($sessionDayCourses as $sessionDayCourse) {
                    $sessionDayCourseArray = $sessionDayCourse->toArray();
                    $dayCourse = new DayCourse();
                    $this->setAttributes($sessionDayCourseArray, $dayCourse);
                    $dayCourse->setModule($module);

                    //create resources
                    $sessionResources = $sessionDayCourse->getResources();
                    foreach ($sessionResources as $sessionResource) {
                        $sessionResourceArray = $sessionResource->toArray();
                        $resource = new Resources();
                        $resource->setDay($dayCourse);
                        $this->setAttributes($sessionResourceArray, $resource);
                        $resource->setStatus(Resources::APPROVED);
                        $resource->setResourceOwner($user);
                        $this->manager->persist($resource);
                        $dayCourse->addResource($resource);
                    }

                    //create activities
                    $sessionAcitivities = $sessionDayCourse->getActivityCourses();
                    foreach ($sessionAcitivities as $sessionActivity) {
                        $sessionActivityArray = $sessionActivity->toArray();
                        $activity = new ActivityCourses();
                        $this->setAttributes($sessionActivityArray, $activity);
                        $activity->setDay($dayCourse);
                        $this->manager->persist($activity);
                        $dayCourse->addActivityCourses($activity);
                    }

                    //create Orders
                    $sessionOrders = $sessionDayCourse->getOrders();
                    foreach ($sessionOrders as $sessionOrder) {
                        $sessionOrderArray = $sessionOrder->serializer();
                        $order = new OrderCourse();
                        $order->setDayCourse($dayCourse);
                        $this->setAttributes($sessionOrderArray, $order);
                        $this->manager->persist($order);
                        $dayCourse->addOrder($order);
                    }
                    $this->manager->persist($dayCourse);


                    $module->addDayCourse($dayCourse);
                }
                //2.projectSubject
                $sessionProjectSubjects = $sessionModule->getSessionProjectSubjects();
                foreach ($sessionProjectSubjects as $sessionProjectSubject) {
                    $sessionProjectSubjectArray = $sessionProjectSubject->serializer();
                    $projectSubject = new ProjectSubject();
                    $projectSubject->setProject($module);
                    $this->setAttributes($sessionProjectSubjectArray, $projectSubject);
                    $this->manager->persist($projectSubject);
                    $module->addProjectSubject($projectSubject);

                }

                $this->manager->persist($module);
                $cursus->addModule($module);
                $this->manager->persist($cursus);
            }

        }
        $this->manager->flush();


    }

    //STEP 2 from cursus To session

    public function updateAttributes($object, $sessionArrayAttribute, $cursusArrayAttribute)
    {
        $diffAtributeArray = array_diff_assoc($sessionArrayAttribute, $cursusArrayAttribute);
        if (!empty($diffAtributeArray)) {
            foreach ($diffAtributeArray as $key => $diffAttribute) {
                $method = 'set' . ucfirst($key);
                call_user_func(array($object, $method), $diffAttribute);
            }
        }
    }

    public function setAttributes($sessionArray, $object)
    {

        foreach ($sessionArray as $key => $attribute) {
            $method = 'set' . ucfirst($key);
            call_user_func(array($object, $method), $attribute);

        }

    }

    public function verifyCursus(Session $session)
    {
        $cursus = $session->getCursus();
        $modules = $cursus->getModules();
        foreach ($modules as $module) {
            $sessionModule = $this->manager->getRepository(SessionModule::class)->findOneBy(['ref' => $module->getRef(), 'session' => $session]);
            if ($sessionModule) {
                $dayCourses = $module->getDayCourses();
                foreach ($dayCourses as $dayCourse) {
                    $sessionDayCourse = $this->manager->getRepository(SessionDayCourse::class)->findOneBy(['reference' => $dayCourse->getReference(), 'module' => $sessionModule]);
                    if ($sessionDayCourse) {
                        $resources = $dayCourse->getResources();
                        foreach ($resources as $resource) {
                            $sessionResource = $this->manager->getRepository(SessionResources::class)->findOneBy(['ref' => $resource->getRef(), 'day' => $sessionDayCourse]);
                            if (!$sessionResource) {
                                $resourceOwner = $resource->getResourceOwner();
                                if ($resourceOwner && in_array(User::ROLE_ADMIN,$resourceOwner->getRoles())) {
                                        $this->manager->remove($resource);
                                }
                            }
                        }


                        $activities = $dayCourse->getActivityCourses();
                        foreach ($activities as $activity) {
                            $sessionActivity = $this->manager->getRepository(SessionActivityCourses::class)->findOneBy(['reference' => $activity->getReference(), 'day' => $sessionDayCourse]);
                            if (!$sessionActivity) {
                                $activity->setDeleted(true);
                                $this->manager->persist($activity);

                            }
                        }
                        $orders = $dayCourse->getOrders();
                        foreach ($orders as $order) {
                            $sessionOrder = $this->manager->getRepository(SessionOrder::class)->findOneBy(['ref' => $order->getRef(), 'dayCourse' => $sessionDayCourse]);
                            if (!$sessionOrder) {
                                $order->setDeleted(true);
                                $this->manager->persist($order);
                            }
                        }
                    } else {
                        $dayCourse->setDeleted(true);
                        $activities = $dayCourse->getActivityCourses();
                        foreach ($activities as $activity) {
                            $activity->setDeleted(true);
                            $this->manager->persist($activity);
                        }
                        $resources = $dayCourse->getResources();
                        foreach ($resources as $resource) {
                            $resource->setDeleted(true);
                            $this->manager->persist($resource);
                        }
                        $orders = $dayCourse->getOrders();
                        foreach ($orders as $order) {
                            $order->setDeleted(true);
                            $this->manager->persist($order);
                        }
                        $this->manager->persist($dayCourse);
                    }

                }
                $projectSubjects = $module->getProjectSubjects();
                foreach ($projectSubjects as $projectSubject) {
                    $sessionProjectSubject = $this->manager->getRepository(SessionProjectSubject::class)->findOneBy(['ref' => $projectSubject->getRef(), 'SessionProject' => $sessionModule]);
                    if (!$sessionProjectSubject) {
                        $projectSubject->setDeleted(true);
                        $this->manager->persist($projectSubject);
                    }

                }
            } else {
                $module->setDeleted(true);
                $dayCourses = $module->getDayCourses();
                foreach ($dayCourses as $dayCourse) {
                    $dayCourse->setDeleted(true);
                    $activities = $dayCourse->getActivityCourses();
                    foreach ($activities as $activity) {
                        $activity->setDeleted(true);
                        $this->manager->persist($activity);
                    }
                    $resources = $dayCourse->getResources();
                    foreach ($resources as $resource) {
                        $resource->setDeleted(true);
                        $this->manager->persist($resource);
                    }
                    $orders = $dayCourse->getOrders();
                    foreach ($orders as $order) {
                        $order->setDeleted(true);
                        $this->manager->persist($order);
                    }
                    $this->manager->persist($dayCourse);
                }
                $projectSubjects = $module->getProjectSubjects();
                foreach ($projectSubjects as $projectSubject) {
                    $projectSubject->setDeleted(true);
                    $this->manager->persist($projectSubject);
                }
                $this->manager->persist($module);

            }
        }
        $this->manager->flush();


    }
}

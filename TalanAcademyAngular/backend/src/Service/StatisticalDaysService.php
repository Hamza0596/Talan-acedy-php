<?php


namespace App\Service;


use App\Entity\DayCourse;
use App\Entity\SessionActivityCourses;
use App\Entity\SessionDayCourse;
use App\Entity\SessionOrder;
use App\Entity\SessionResources;
use App\Repository\ActivityCoursesRepository;
use App\Repository\OrderCourseRepository;
use App\Repository\ResourcesRepository;
use Doctrine\ORM\EntityManagerInterface;

class StatisticalDaysService
{

    /**
     * @var ResourcesRepository
     */
    private $resourcesRepository;
    /**
     * @var ActivityCoursesRepository
     */
    private $activityCoursesRepository;
    /**
     * @var OrderCourseRepository
     */
    private $orderCourseRepository;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(ResourcesRepository $resourcesRepository,
                                ActivityCoursesRepository $activityCoursesRepository,
                                OrderCourseRepository $orderCourseRepository,
                                EntityManagerInterface $em)
    {
        $this->resourcesRepository = $resourcesRepository;
        $this->activityCoursesRepository = $activityCoursesRepository;
        $this->orderCourseRepository = $orderCourseRepository;
        $this->em = $em;
    }


    public function StaticDay($dayCourse)
    {
        $resources = 0;
        $activities = 0;
        $order = 0;
        $suggestion = 0;
        $suggestionSessionDay = 0;
        $totalSuggestion=0;
        $suggestionApproved=0;
        if ($dayCourse instanceof DayCourse) {
            $resources = $this->resourcesRepository->countResourceByDay($dayCourse);
            $activities = $this->activityCoursesRepository->countActivitiesByDay($dayCourse);
            $order = $this->orderCourseRepository->countOrderByDay($dayCourse);
            $suggestion = $this->resourcesRepository->countSuggestionByDay($dayCourse);
            $totalSuggestion=$this->resourcesRepository->countAllSuggestionDay($dayCourse);
            $suggestionApproved=$this->resourcesRepository->countSuggestionApproved($dayCourse);
        } elseif ($dayCourse instanceof SessionDayCourse) {
            $resources = $this->em->getRepository(SessionResources::class)->countResourceByDay($dayCourse);
            $activities = $this->em->getRepository(SessionActivityCourses::class)->countActivitiesByDay($dayCourse);
            $order = $this->em->getRepository(SessionOrder::class)->countOrderByDay($dayCourse);
            $dayCourseCursus = $this->em->getRepository(DayCourse::class)->findOneBy(['reference' => $dayCourse->getReference(),'deleted'=>null ]);
            if (!is_null($dayCourseCursus)) {
                $suggestionSessionDay = $this->resourcesRepository->countAllSuggestionDay($dayCourseCursus);
            }
        }
        return ['resources' => $resources,
            'activities' => $activities,
            'Suggestion' => $suggestion,
            'suggetionSessionDay' => $suggestionSessionDay,
            'totalSuggestion'=> $totalSuggestion,
            'suggestionApproved'=>$suggestionApproved,
            'order' => $order];
    }
}

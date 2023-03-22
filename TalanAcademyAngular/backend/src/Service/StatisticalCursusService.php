<?php


namespace App\Service;


use App\Entity\Cursus;
use App\Entity\Module;
use App\Entity\Staff;
use App\Repository\ActivityCoursesRepository;
use App\Repository\CandidatureRepository;
use App\Repository\CursusRepository;
use App\Repository\DayCourseRepository;
use App\Repository\ModuleRepository;
use App\Repository\ResourcesRepository;
use Doctrine\DBAL\Types\TextType;

class StatisticalCursusService
{
    private $cursusRepositiry;
    private $moduleRepository;
    private $dayRepository;
    private $resourcesRepository;
    private $activityRepository;
    private $candidatureRepository;

    public function __construct(CursusRepository $cursusRepository,
                                ModuleRepository $moduleRepository,
                                DayCourseRepository $dayCourseRepository,
                                ResourcesRepository $resourcesRepository,
                                ActivityCoursesRepository $activityCoursesRepository,
                                CandidatureRepository $candidatureRepository)
    {
        $this->activityRepository = $activityCoursesRepository;
        $this->cursusRepositiry = $cursusRepository;
        $this->dayRepository = $dayCourseRepository;
        $this->moduleRepository = $moduleRepository;
        $this->resourcesRepository = $resourcesRepository;
        $this->candidatureRepository = $candidatureRepository;
    }

    public function statisticPageCursus()
    {
        $static = [];
        $activities = $this->activityRepository->countAll();
        $static['activities'] = $activities;
        $resources = $this->resourcesRepository->countAll();
        $static['resources'] = $resources;
        $modules = $this->moduleRepository->countAll();
        $static['modules'] = $modules;
        $days = $this->dayRepository->countAll();
        $static['days'] = $days;
        return $static;
    }

    public function staticCursusBySession()
    {
        return $this->cursusRepositiry->CountSessionFromCursus();
    }

    public function countAllCursus()
    {
        return $this->cursusRepositiry->countAll();
    }
    public function daysCount(Cursus $cursus,$leçonOnly=true)
    {
        $countDays= $this->cursusRepositiry->countDays($cursus);
        $countDaysProject = $this->countDaysProject($cursus);
        if ($leçonOnly)
        {
            return $countDays;
        }
        return $countDays+$countDaysProject;
    }
    public function daysValidateCount(Cursus $cursus)
    {
        return $this->cursusRepositiry->countDaysValidate($cursus);
    }

    public function countCursusApplications(Cursus $cursus){
        return $this->candidatureRepository->getCursusApplicationsCount($cursus);
    }

    public function countDaysProject(Cursus $cursus){
        $modules= $cursus->getModules();
        $countDaysProject =0;
        foreach ($modules as $module){
            if ($module->getType()==Module::PROJECT && $module->getDeleted()==null){
                $countDaysProject+=$module->getDuration();
            }
        }
        return $countDaysProject;
    }


}

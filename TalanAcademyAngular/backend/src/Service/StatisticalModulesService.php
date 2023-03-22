<?php


namespace App\Service;


use App\Entity\Cursus;
use App\Entity\Module;
use App\Entity\ModuleInterface;
use App\Entity\Resources;
use App\Repository\DayCourseRepository;
use App\Repository\ModuleRepository;
use App\Repository\ResourcesRepository;

class StatisticalModulesService
{
    /**
     * @var ModuleRepository
     */
    private $moduleRepository;
    /**
     * @var DayCourseRepository
     */
    private $dayCourseRepository;
    /**
     * @var StatisticalCursusService
     */
    private $statisticalCoursesService;
    /**
     * @var $ressourcesRepository
     */
    private $ressourcesRepository;

    public function __construct(ModuleRepository $moduleRepository, DayCourseRepository $dayCourseRepository, ResourcesRepository $resourcesRepository, StatisticalCursusService $statisticalCoursesService)
    {
        $this->moduleRepository = $moduleRepository;
        $this->dayCourseRepository = $dayCourseRepository;
        $this->statisticalCoursesService = $statisticalCoursesService;
        $this->ressourcesRepository = $resourcesRepository;

    }

    public function countModules(Cursus $cursus)
    {
        return $this->moduleRepository->countByCursus($cursus);
    }

    public function staticForPageModules(Cursus $cursus)
    {
        $daysCursus = $this->statisticalCoursesService->daysCount($cursus);
        $daysValidateCursus = $this->statisticalCoursesService->daysValidateCount($cursus);
        $module = $this->countModules($cursus);
        return ['dayscursus' => $daysCursus,
            'daysValidateCursus' => $daysValidateCursus,
            'module' => $module,
            'resourcesCursusToApprove' => $this->countCursusResourcesToApprove($cursus)
        ];
    }

    public function staticModule(ModuleInterface $module)
    {
        $daysModule = $this->moduleRepository->countDays($module);
        $daysValidateModule = $this->moduleRepository->countDaysValidate($module);
        $countDaysValidateWithoutInstruction = $this->moduleRepository->countDaysValidateWithoutInstruction($module);
        return [
            'daysModule' => $daysModule,
            'daysValidateModule' => $daysValidateModule,
            'countDaysValidateWithoutInstruction' => $countDaysValidateWithoutInstruction,
            'countModuleResourcesToApprove'=>$this->countModuleResourcesToApprove($module)

        ];
    }

    public function countCursusResourcesToApprove(Cursus $cursus)
    {
        $count = 0;
        $modules = $this->moduleRepository->findBy(['courses' => $cursus]);
        foreach ($modules as $module) {
            $days = $this->dayCourseRepository->findBy(['module' => $module,'deleted'=>null]);
            foreach ($days as $day) {
                $resourcesToApprove = $this->ressourcesRepository->findBy(['day' => $day, 'status' => Resources::TOAPPROVE,'deleted'=>null]);
                $count += count($resourcesToApprove);
            }
        }
        return $count;
    }

    public function countModuleResourcesToApprove(ModuleInterface $module)
    {
        $count=0;
        $days=$this->dayCourseRepository->findBy(['module' => $module,'deleted'=>null]);
        foreach ($days as $day) {
            $resourcesToApprove = $this->ressourcesRepository->findBy(['day' => $day, 'status' => Resources::TOAPPROVE,'deleted'=>null]);
            $count += count($resourcesToApprove);
        }
        return $count;
    }
}

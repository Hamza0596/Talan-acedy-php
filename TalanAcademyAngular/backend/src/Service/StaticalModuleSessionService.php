<?php
/**
 * Created by PhpStorm.
 * User: jghada
 * Date: 10/05/2019
 * Time: 17:56
 */

namespace App\Service;


use App\Entity\ModuleInterface;
use App\Entity\Session;
use App\Entity\SessionModule;
use App\Repository\SessionDayCourseRepository;
use App\Repository\SessionModuleRepository;
use App\Repository\SessionOrderRepository;

class StaticalModuleSessionService
{
    /**
     * @var SessionModuleRepository
     */
    private $moduleRepository;
    /**
     * @var SessionDayCourseRepository
     */
    private $dayCourseRepository;
    /**
     * @var StatisticalCursusService
     */
    private $statisticalCursusService;
    /**
     * @var SessionOrderRepository
     */
    private $orderCourseRepository;
    /**
     * @var SessionModuleRepository
     */
    private $sessionModuleRepository;
    /**
     * @var StaticalSessionService
     */
    private $staticalSessionService;

    public function __construct(SessionModuleRepository $moduleRepository,
                                SessionOrderRepository $orderCourseRepository,
                                SessionModuleRepository $sessionModuleRepository,
                                StaticalSessionService $staticalSessionService,
                                SessionDayCourseRepository $dayCourseRepository,
                                StatisticalCursusService $statisticalCursusService)
    {
        $this->moduleRepository = $moduleRepository;
        $this->dayCourseRepository = $dayCourseRepository;
        $this->statisticalCursusService = $statisticalCursusService;
        $this->orderCourseRepository = $orderCourseRepository;
        $this->sessionModuleRepository = $sessionModuleRepository;
        $this->staticalSessionService = $staticalSessionService;
    }

    public function staticModule(ModuleInterface $module)
    {
        $daysModule = $this->moduleRepository->countDays($module);
        $daysValidateModule = $this->moduleRepository->countDaysValidate($module);
        $countDaysValidateWithoutInstruction = $this->moduleRepository->countDaysValidateWithoutInstruction($module);
        return ['daysModule' => $daysModule,
            'daysValidateModule' => $daysValidateModule,
            'countDaysValidateWithoutInstruction' => $countDaysValidateWithoutInstruction
        ];
    }

    public function countModules(Session $session)
    {
        return $this->moduleRepository->countBySession($session);
    }

    public function staticForPageModules(Session $session)
    {
        $daysCursus = $this->staticalSessionService->daysCount($session);
        $daysValidateCursus = $this->staticalSessionService->daysValidateCount($session);
        $module = $this->countModules($session);
        return [
            'dayscursus' => $daysCursus,
            'daysValidateCursus' => $daysValidateCursus,
            'module' => $module,
        ];
    }


}



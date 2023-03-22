<?php
/**
 * Created by PhpStorm.
 * User: jghada
 * Date: 28/05/2019
 * Time: 14:56
 */

namespace App\Service;


use App\Entity\Session;
use App\Entity\SessionDayCourse;
use App\Entity\SessionModule;
use App\Repository\SessionDayCourseRepository;
use App\Repository\SessionModuleRepository;
use App\Repository\SessionRepository;
use phpDocumentor\Reflection\Types\This;

class AssociateDateService
{
    const LABEL = 'label';
    const TODAY ='today';
    /**
     * @var SessionRepository
     */
    private $sessionRepository;
    /**
     * @var HolidaysService
     */
    private $holidaysService;
    /**
     * @var SessionDayCourseRepository
     */
    private $sessionDayCourseRepository;

    private $sessionModuleRepository;

    public function __construct(SessionRepository $sessionRepository, HolidaysService $holidaysService, SessionDayCourseRepository $sessionDayCourseRepository, SessionModuleRepository $sessionModuleRepository)
    {

        $this->sessionRepository = $sessionRepository;
        $this->holidaysService = $holidaysService;
        $this->sessionDayCourseRepository = $sessionDayCourseRepository;
        $this->sessionModuleRepository = $sessionModuleRepository;
    }

    public function getPassedDateDayArrayId($session)
    {
        $dateDayArrayId = [];
        $dayDateArray = $this->getDayDateArray($session, new \DateTime());
        for ($i = 0; $i < count($dayDateArray); $i++) {
            $dateDayArrayId[] = $dayDateArray[$i]['id'];
        }
        return $dateDayArrayId;

    }

    public function
    getDayDateArray(Session $session, $today = null)
    {
        $dayDateSession = [];
        $date_array = $this->holidaysService->calculateEndDate($session->getStartDate(), $this->sessionRepository->countSessionsDays($session), false);
        $session_days_array = $this->sessionRepository->getSessionsDays($session);
        if (is_array($date_array)) {
            for ($i = 0; $i < count($date_array); $i++) {
                if ($today && $today < $date_array[$i]) {
                        break;
                }
                $sessionDay = $this->sessionDayCourseRepository->find($session_days_array[$i]['id']);
                $dayDateSession[$i]['id'] = $session_days_array[$i]['id'];
                $dayDateSession[$i]['date'] = $date_array[$i]->setTime(23, 59, 0);
                $dayDateSession[$i]['day'] = $sessionDay;
                $dayDateSession[$i][self::LABEL] = $sessionDay->getDescription();
            }
            //associate date to day

        }
        return $dayDateSession;
    }

    public function getModuleDateArray(Session $session)
    {
        $dayDateSession = [];

        $date_array = $this->holidaysService->calculateEndDate($session->getStartDate(), $this->sessionRepository->countSessionsDays($session), false);
        $session_days_array = $this->sessionRepository->getSessionsDays($session);

        //associate date to day
        for ($i = 0; $i < count($date_array); $i++) {
            $dayDateSession[$i]['id'] = $session_days_array[$i]['id'];
            $dayDateSession[$i]['date'] = $date_array[$i];
            $dayDateSession[$i][self::LABEL] = $this->sessionDayCourseRepository->find($session_days_array[$i]['id'])->getDescription();
        }
        $arrayCount = count($dayDateSession);
        $j = 0;
        $k = 0;
        $moduleDateSession = [];
        $module = $this->sessionDayCourseRepository->find($session_days_array[0]['id'])->getModule();
        $moduleDateSession[$k]['id'] = $module->getId();
        $moduleDateSession[$k]['startModule'] = $date_array[0];
        $moduleDateSession[$k][self::LABEL] = $module->getTitle();
        $k++;
        while ($arrayCount != 0) {
            $currentDayModule = $this->sessionDayCourseRepository->find($session_days_array[$j]['id'])->getModule();

            if ($module !== $currentDayModule) {

                $moduleDateSession[$k]['id'] = $currentDayModule->getId();
                $moduleDateSession[$k][self::LABEL] = $currentDayModule->getTitle();
                $moduleDateSession[$k]['startModule'] = $date_array[$j];
                $moduleDateSession[$k - 1]['endModule'] = $date_array[$j - 1]->setTime(23, 0, 0);
                $k++;
            }
            $module = $currentDayModule;
            $j++;
            $arrayCount--;
        }
        $moduleDateSession[$k - 1]['endModule'] = $date_array[$j - 1]->setTime(23, 0, 0);
        return $moduleDateSession;
    }

    public function pendingSessionModules(Session $session,$source)
    {
        $modules = $session->getModules();
        $stateModules = [];

        foreach ($modules as $module) {
            $stateModules[$module->getId()] = $this->pendingModule($module, $source);
        }
        return $stateModules;

    }

    //pending module take module and source as parameters
    //source can be 'Day' or 'Module'
    //if source= 'Day' ==>if the module in progress the function return true;we can add days if the module is in progress
    //else if source= 'Module' ==>if the module in progress the function return false;we can't change the module in progress
    public function pendingModule(SessionModule $sessionModule,$source)
    {
        $moduleDays = [];
        //recuperate day list of the given module
        $moduleDays = $this->sessionDayCourseRepository->findDaysOrdred($sessionModule);
        //if session is pending return true
        //else if session finished return false

        $sessionStart = $sessionModule->getSession()->getStartDate();
        $sessionEnd = $sessionModule->getSession()->getEndDate();
        if ($sessionStart > new \DateTime() || $sessionEnd->format('Y-m-d') < (new \DateTime())->format('Y-m-d'))
            return $sessionStart > new \DateTime();


        //if session is in progress
        else {

            //if the module contains days
            //check if the last day is passed -->the whole module is passed-->return false
            //if the first day is pending -->the module didn't start yet-->return true
            //else check day by day
            $moduleDaysNumber = count($moduleDays);
            if (isset($moduleDays) and $moduleDaysNumber != 0) {
                $lastDay = end($moduleDays);
                $firstDay = $moduleDays[0];
                $pendingLastDay = $this->pendingDay($lastDay);
                $pendingFirstDay = $this->pendingDay($firstDay);
                if ($pendingLastDay == false) {

                    return false;
                } elseif ($pendingFirstDay == true ||$pendingFirstDay ==self::TODAY) {
                    return true;
                } else {
                    if ($source=='Module')
                    {
                        return false;
                    }
                    elseif ($source=='Day')
                    {
                        return true;
                    }
                }

            } else {

                $currentModuleId = $this->getCurrentModuleId($sessionModule->getSession());

                $moduleInProgress = $this->sessionModuleRepository->find($currentModuleId);

                $moduleSessionDays = $this->sessionDayCourseRepository->getOrdredDayCoursesByModule($moduleInProgress);

                foreach ($moduleSessionDays as $moduleSessionDay){
                    if ($this->pendingDay($moduleSessionDay)){
                        $currentDay = $moduleSessionDay;
                        break;
                    }
                }

                $currentModule = $currentDay->getModule();
                $orderCurrentModule = $currentModule->getOrderModule();
                $orderModule = $sessionModule->getOrderModule();
                return $orderCurrentModule < $orderModule;


            }

        }

    }

    public function pendingDay(SessionDayCourse $sessionDayCourse)
    {
        $date = $this->getPlanifiedDateFromSessionDay($sessionDayCourse);
        $today = new \DateTime();
        if ($date->format('Y-m-d') < $today->format('Y-m-d'))
        {
            return false;
        }
        elseif($date->format('Y-m-d') > $today->format('Y-m-d')){
            return true;
        }
        elseif ($date->format('Y-m-d') == $today->format('Y-m-d')){
            return self::TODAY;
        }
    }

    public function getPlanifiedDateFromSessionDay(SessionDayCourse $sessionDayCourse)
    {
        $session = $sessionDayCourse->getModule()->getSession();
        $sessionData = $this->getDayDateArray($session);
        $planifiedDate = null;
        foreach ($sessionData as $index => $data) {
            if ($sessionData[$index]['day']->getId() == $sessionDayCourse->getId()) {
                $planifiedDate = $sessionData[$index]['date'];
                break;
            }
        }
        return $planifiedDate;
    }

    public function getCurrentDayAndPreviousDay(Session $session)
    {
        $today = new \DateTime();
        $days = $this->getDayDateArray($session);
        $currentDay = null;
        $previoursDay = null;
        $date = [];
        //récupération du cours d'aujourd'hui et cours précedent
        foreach ($days as $index => $day) {
            if ($day['date']->format('Y-m-d') == $today->format('Y-m-d')) {
                $currentDay = $day['day'];
                if ($index > 0) {
                    $previoursDay = $days[$index - 1];
                }
                break;
            }
        }
        $date['currentDay'] = $currentDay;
        $date['previousDay'] = $previoursDay;
        return $date;

    }

    public function pendingSessionDayCourse(SessionModule $sessionModule)
    {
        $days = $sessionModule->getDayCourses();
        $stateDays = [];
        foreach ($days as $day) {
            $stateDays[$day->getId()] = $this->pendingDay($day);
        }
        return $stateDays;


    }

    public function getCurrentModuleId(Session $session)
    {
        $today = new \DateTime();
        $module= null;
        $moduleDateSession = $this->getModuleDateArray($session);
        foreach ($moduleDateSession as $module) {
            if ((strtotime($module['startModule']->format('Y-m-d')) <= strtotime($today->format('Y-m-d'))) && (strtotime($module['endModule']->format('Y-m-d')) >= strtotime($today->format('Y-m-d')))){
                return $module['id'];
            }
        }
        return $module;
    }



}

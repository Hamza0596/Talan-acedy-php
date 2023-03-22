<?php


namespace App\Service;


use App\Entity\DayInterface;
use App\Entity\ModuleInterface;
use App\Repository\DayCourseRepository;
use App\Repository\OrderCourseRepository;
use App\Repository\SessionDayCourseRepository;
use Doctrine\Common\Persistence\ObjectManager;

class DayCourseService
{
    const SHOWED = 'showed';
    const HIDDEN = 'hidden';
    const ORDER = 'ordre';
    const MODULE = 'module';

    /**
     * @var DayCourseRepository
     */
    private $dayCourseRepository;
    /**
     * @var ObjectManager
     */
    private $em;

    private $sessionDayCourseRepository;

    /**
     * @var OrderCourseRepository
     */
    private $orderCourseRepository;

    /**
     * DayCourseService constructor.
     * @param DayCourseRepository $dayCourseRepository
     * @param ObjectManager $em
     * @param SessionDayCourseRepository $sessionDayCourseRepository
     * @param OrderCourseRepository $orderCourseRepository
     */
    public function __construct(DayCourseRepository $dayCourseRepository, ObjectManager $em, SessionDayCourseRepository $sessionDayCourseRepository, OrderCourseRepository $orderCourseRepository)
    {
        $this->dayCourseRepository = $dayCourseRepository;
        $this->sessionDayCourseRepository = $sessionDayCourseRepository;
        $this->em = $em;
        $this->orderCourseRepository = $orderCourseRepository;
    }

    public function changeDayByActionType(ModuleInterface $module, string $action, DayInterface $dayCourse = null)
    {
        $dayRepository = $this->dayCourseRepository;
        return $this->genericChangeDayByActionType($module, $action, $dayRepository, $dayCourse);
    }

    public function genericChangeDayByActionType(ModuleInterface $module, String $action, $dayRepository, DayInterface $dayCourse = null)
    {
        $output = [];
        switch ($action) {
            case 'adding':
                $output = $this->buildDaysOutput($module, $dayRepository);
                break;
            case 'deletion':
                $deletedDayOrder = $dayCourse->getOrdre();
                if ($dayRepository == $this->sessionDayCourseRepository) {
                    $nextDay = $dayRepository->findOneBy([self::ORDER => $dayCourse->getOrdre() + 1, self::MODULE => $module]);
                    $previousDay = $dayRepository->findOneBy([self::ORDER => $dayCourse->getOrdre() - 1, self::MODULE => $module]);

                } elseif ($dayRepository == $this->dayCourseRepository) {

                    $nextDay = $dayRepository->findOneBy([self::ORDER => $dayCourse->getOrdre() + 1, self::MODULE => $module, 'deleted' => null]);
                    $previousDay = $dayRepository->findOneBy([self::ORDER => $dayCourse->getOrdre() - 1, self::MODULE => $module, 'deleted' => null]);

                }
                $daysCount = $dayRepository->nbDay($module);
                //pas d'impact sur un jour normal
                if ($dayCourse->getStatus() == DayInterface::CORRECTION_DAY) {
                    //impact sur jour suivant devient correction
                    //faut voir si il devient dernier jour ou nn
                    if (isset($nextDay)) {
                        if ($nextDay->getStatus() == DayInterface::NORMAL_DAY) {
                            $nextDay->setStatus(DayInterface::CORRECTION_DAY);
                            $this->em->persist($nextDay);
                        } elseif ($nextDay->getStatus() == DayInterface::VALIDATING_DAY) {
                            $previousDay->setStatus(DayInterface::NORMAL_DAY);
                            $this->em->persist($previousDay);
                        }
                    } else {
                        $previousDay->setStatus(DayInterface::NORMAL_DAY);
                        $this->em->persist($previousDay);
                    }
                } elseif ($dayCourse->getStatus() == DayInterface::VALIDATING_DAY) {
                    //jour validant
                    //le jour correction revient normal
                    $nextDay->setStatus(DayInterface::NORMAL_DAY);
                    $this->em->persist($nextDay);
                }
                if ($deletedDayOrder < $daysCount) {
                    for ($i = $deletedDayOrder + 1; $i <= $daysCount; $i++) {
                        if ($dayRepository == $this->sessionDayCourseRepository) {
                            $day = $dayRepository->findOneBy([self::ORDER => $i, self::MODULE => $module]);

                        } elseif ($dayRepository == $this->dayCourseRepository) {
                            $day = $dayRepository->findOneBy([self::ORDER => $i, self::MODULE => $module, 'deleted' => null]);

                        }
                        $day->setOrdre($i - 1);
                        $this->em->persist($day);
                    }
                }
                $this->em->remove($dayCourse);
                $module->removeDayCourse($dayCourse);
                $this->em->flush();
                $output = $this->buildDaysOutput($module, $dayRepository);
                break;
            //case cochée
            case 'change-status-on':
                $daysCount = $dayRepository->nbDay($module);
                if ($dayRepository == $this->sessionDayCourseRepository) {
                    $nextDay = $dayRepository->findOneBy([self::ORDER => $dayCourse->getOrdre() + 1, self::MODULE => $module]);
                    $previousDay = $dayRepository->findOneBy([self::ORDER => $dayCourse->getOrdre() - 1, self::MODULE => $module]);
                } elseif ($dayRepository == $this->dayCourseRepository) {
                    $nextDay = $dayRepository->findOneBy([self::ORDER => $dayCourse->getOrdre() + 1, self::MODULE => $module, 'deleted' => null]);
                    $previousDay = $dayRepository->findOneBy([self::ORDER => $dayCourse->getOrdre() - 1, self::MODULE => $module, 'deleted' => null]);
                }


                if (($dayCourse->getOrdre() == $daysCount) || ($nextDay->getStatus() == DayInterface::VALIDATING_DAY) || (isset($previousDay) && $previousDay->getStatus() == DayInterface::VALIDATING_DAY)) {
                    $output[] = ['otherSituation on check on' => 'otherSituation on check on'];
                } else {
                    $dayCourse->setStatus(DayInterface::VALIDATING_DAY);
                    $nextDay->setStatus(DayInterface::CORRECTION_DAY);
                    $this->em->persist($nextDay);
                    $this->em->persist($dayCourse);
                    $output = $this->buildDaysOutput($module, $dayRepository);
                }
                break;
            //case decochée pour un jour valid pour le rendre normale
            case 'change-status-off':
                $daysCount = $dayRepository->nbDay($module);
                if ($dayRepository == $this->sessionDayCourseRepository) {
                    $nextDay = $dayRepository->findOneBy([self::ORDER => $dayCourse->getOrdre() + 1, self::MODULE => $module]);

                } elseif ($dayRepository == $this->dayCourseRepository) {
                    $nextDay = $dayRepository->findOneBy([self::ORDER => $dayCourse->getOrdre() + 1, self::MODULE => $module, 'deleted' => null]);

                }

                if (($dayCourse->getOrdre() == $daysCount) || (isset($nextDay) && $nextDay->getStatus() == DayInterface::VALIDATING_DAY) || (isset($previousDay) && $previousDay->getStatus() == DayInterface::VALIDATING_DAY)) {
                    $output[] = ['otherSituation on check off' => 'otherSituation on check off'];
                } else {
                    $dayCourse->setStatus(DayInterface::NORMAL_DAY);
                    $nextDay->setStatus(DayInterface::NORMAL_DAY);
                    $this->em->persist($nextDay);
                    $this->em->persist($dayCourse);
                    $output = $this->buildDaysOutput($module, $dayRepository);
                }
                break;
            default:
                $output[] = ['otherSituation on check off' => 'otherSituation on check off'];
                break;
        }
        return $output;
    }

    private function buildDaysOutput($module, $dayRepository)
    {
        $output = [];
        $processingDays = $dayRepository->getOrdredDayCoursesByModule($module);
        $dayWithInstruction = null;
        foreach ($processingDays as $day) {
            if ($day->getStatus() == DayInterface::VALIDATING_DAY) {
                $checkboxStatus = self::SHOWED;
                $checkboxValue = 'on';
                $status = DayInterface::VALIDATING_DAY;
                $countWithInstruction = $this->orderCourseRepository->checkDayValidateWithInstruction($day);
                if ($countWithInstruction == 0) {
                    $dayWithInstruction = false;
                } else {
                    $dayWithInstruction = true;
                }

            } elseif ($day->getStatus() == DayInterface::CORRECTION_DAY) {
                $dayWithInstruction = null;
                $checkboxStatus = self::HIDDEN;
                $checkboxValue = 'off';
                $status = DayInterface::CORRECTION_DAY;
            } elseif ($day->getOrdre() == count($processingDays)) {
                $dayWithInstruction = null;

                $checkboxStatus = self::HIDDEN;
                $checkboxValue = 'off';
                $status = $day->getStatus();
            } else {
                $dayWithInstruction = null;
                if ($dayRepository == $this->sessionDayCourseRepository) {
                    $localNextDay = $dayRepository->findOneBy([self::ORDER => $day->getOrdre() + 1, self::MODULE => $module]);

                } elseif ($dayRepository == $this->dayCourseRepository) {
                    $localNextDay = $dayRepository->findOneBy([self::ORDER => $day->getOrdre() + 1, self::MODULE => $module, 'deleted' => null]);

                }
                if ($localNextDay->getStatus() == DayInterface::VALIDATING_DAY) {
                    $checkboxStatus = self::HIDDEN;
                    $checkboxValue = 'off';
                    $status = DayInterface::NORMAL_DAY;
                } else {
                    $checkboxStatus = self::SHOWED;
                    $checkboxValue = 'off';
                    $status = DayInterface::NORMAL_DAY;
                }
            }
            $tmp = ['id' => $day->getId(), 'checkboxStatus' => $checkboxStatus, 'order' => $day->getOrdre(), 'checkboxValue' => $checkboxValue, 'status' => $status, 'dayWithInstruction' => $dayWithInstruction];
            $output[] = $tmp;
        }
        return $output;
    }

    public function changeSessionDayByActionType(ModuleInterface $module, string $action, DayInterface $dayCourse = null)
    {
        $dayRepository = $this->sessionDayCourseRepository;
        return $this->genericChangeDayByActionType($module, $action, $dayRepository, $dayCourse);
    }

    public function changeOrderDays(DayInterface $dayCourse, $newOrder, $dayRepository)
    {
        $this->checkAndChangeDaysStatusWhenOrderChange($dayCourse, $newOrder, $dayRepository);
        $oldOrder = $dayCourse->getOrdre();
        if ($oldOrder > $newOrder) {
            $daysBeforeAffected = $dayRepository->findDaysWithModuleBetweenTwoOrders($dayCourse->getModule(), $newOrder, $oldOrder - 1);
            foreach ($daysBeforeAffected as $dayBeforeAffected) {
                $dayBeforeAffected->setOrdre($dayBeforeAffected->getOrdre() + 1);
                $this->em->persist($dayBeforeAffected);
            }
        } elseif ($oldOrder < $newOrder) {
            $daysAfterAffected = $dayRepository->findDaysWithModuleBetweenTwoOrders($dayCourse->getModule(), $oldOrder + 1, $newOrder);
            foreach ($daysAfterAffected as $dayAfterAffected) {
                $dayAfterAffected->setOrdre($dayAfterAffected->getOrdre() - 1);
                $this->em->persist($dayAfterAffected);
            }
        }
        $dayCourse->setOrdre($newOrder);
        $this->em->flush();
        $output = $this->buildDaysOutput($dayCourse->getModule(), $dayRepository);

        return ['dayCourse' => $dayCourse, 'oldOrder' => $oldOrder, 'newOrder' => intval($newOrder), 'check' => $output];
    }

    public function checkAndChangeDaysStatusWhenOrderChange(DayInterface $dayCourse, $newOrder, $dayRepository)
    {
        // oldOrder c la position de départ et oldDay c le jour à déplacer
        $oldOrder = $dayCourse->getOrdre();
        $countDays = $dayRepository->nbDay($dayCourse->getModule());
        //newDay c la position cible
        if ($dayRepository == $this->sessionDayCourseRepository) {
            $newDay = $dayRepository->findOneBy(['module' => $dayCourse->getModule(), 'ordre' => $newOrder]);
            // oldPreviousDayCourse c le jour qui précède le oldDay avant le déplacement
            $oldPreviousDayCourse = $dayRepository->findOneBy(['module' => $dayCourse->getModule(), 'ordre' => $oldOrder - 1]);
            // oldNextDayCourse c le jour qui suit le oldDay avant le déplacement
            $oldNextDayCourse = $dayRepository->findOneBy(['module' => $dayCourse->getModule(), 'ordre' => $oldOrder + 1]);
            // oldPreviousNewDay c le jour qui précède le newDay avant le déplacement
            $oldPreviousNewDay = $dayRepository->findOneBy(['module' => $dayCourse->getModule(), 'ordre' => $newOrder - 1]);
            // oldNextDayCourse c le jour qui suit le newDay avant le déplacement
            $oldNextNewDay = $dayRepository->findOneBy(['module' => $dayCourse->getModule(), 'ordre' => $newOrder + 1]);

        }elseif ($dayRepository == $this->dayCourseRepository){
            $newDay = $dayRepository->findOneBy(['module' => $dayCourse->getModule(), 'ordre' => $newOrder, 'deleted' => null]);
            // oldPreviousDayCourse c le jour qui précède le oldDay avant le déplacement
            $oldPreviousDayCourse = $dayRepository->findOneBy(['module' => $dayCourse->getModule(), 'ordre' => $oldOrder - 1, 'deleted' => null]);
            // oldNextDayCourse c le jour qui suit le oldDay avant le déplacement
            $oldNextDayCourse = $dayRepository->findOneBy(['module' => $dayCourse->getModule(), 'ordre' => $oldOrder + 1, 'deleted' => null]);
            // oldPreviousNewDay c le jour qui précède le newDay avant le déplacement
            $oldPreviousNewDay = $dayRepository->findOneBy(['module' => $dayCourse->getModule(), 'ordre' => $newOrder - 1, 'deleted' => null]);
            // oldNextDayCourse c le jour qui suit le newDay avant le déplacement
            $oldNextNewDay = $dayRepository->findOneBy(['module' => $dayCourse->getModule(), 'ordre' => $newOrder + 1, 'deleted' => null]);
        }

        // 1er sens (ordre de la position cible < ordre du jour à déplacer)
        if ($oldOrder > $newOrder) {
            switch ($dayCourse->getStatus()) {
                case DayInterface::NORMAL_DAY :
                    // si newDay est un jour normal ou bien jour validant: aucun impact
                    if ($newDay->getStatus() === DayInterface::CORRECTION_DAY) {
                        $dayCourse->setStatus(DayInterface::CORRECTION_DAY);
                        $newDay->setStatus(DayInterface::NORMAL_DAY);
                    }
                    $this->em->flush();
                    break;
                case DayInterface::CORRECTION_DAY :
                    if ($newDay->getStatus() === DayInterface::NORMAL_DAY) {
                        if ($oldOrder === $countDays) {
                            $dayCourse->setStatus(DayInterface::NORMAL_DAY);
                            $oldPreviousDayCourse->setStatus(DayInterface::NORMAL_DAY);
                        } elseif ($oldNextDayCourse->getStatus() === DayInterface::VALIDATING_DAY) {
                            $dayCourse->setStatus(DayInterface::NORMAL_DAY);
                            $oldNextDayCourse->setStatus(DayInterface::CORRECTION_DAY);
                            if ($dayRepository == $this->sessionDayCourseRepository) {
                                $oldNextNextDayCourse = $dayRepository->findOneBy(['module' => $dayCourse->getModule(), 'ordre' => $oldOrder + 2]);

                            }
                            elseif ($dayRepository == $this->dayCourseRepository){
                                $oldNextNextDayCourse = $dayRepository->findOneBy(['module' => $dayCourse->getModule(), 'ordre' => $oldOrder + 2, 'deleted' => null]);

                            }

                            $oldNextNextDayCourse->setStatus(DayInterface::NORMAL_DAY);
                        } else {
                            $dayCourse->setStatus(DayInterface::NORMAL_DAY);
                            $oldNextDayCourse->setStatus(DayInterface::CORRECTION_DAY);
                        }
                    } elseif ($newDay->getStatus() === DayInterface::VALIDATING_DAY) {
                        if ($oldOrder === $countDays) {
                            $dayCourse->setStatus(DayInterface::NORMAL_DAY);
                            $oldPreviousDayCourse->setStatus(DayInterface::NORMAL_DAY);
                        }
                    elseif ($oldNextDayCourse->getStatus() === DayInterface::VALIDATING_DAY) {
                        $dayCourse->setStatus(DayInterface::NORMAL_DAY);
                        $oldNextDayCourse->setStatus(DayInterface::CORRECTION_DAY);
                        $oldNextNextDayCourse = $dayRepository->findOneBy(['module' => $dayCourse->getModule(), 'ordre' => $oldOrder + 2]);
                        $oldNextNextDayCourse->setStatus(DayInterface::NORMAL_DAY);
                    }
                    else {
                            $dayCourse->setStatus(DayInterface::NORMAL_DAY);
                            $oldNextDayCourse->setStatus(DayInterface::CORRECTION_DAY);
                        }

                    } elseif ($newDay->getStatus() === DayInterface::CORRECTION_DAY) {

                        if ($oldOrder === $countDays) {
                            $newDay->setStatus(DayInterface::NORMAL_DAY);
                            $oldPreviousDayCourse->setStatus(DayInterface::NORMAL_DAY);
                        } elseif ($oldNextDayCourse->getStatus() === DayInterface::VALIDATING_DAY) {
                            $newDay->setStatus(DayInterface::NORMAL_DAY);
                            $oldNextDayCourse->setStatus(DayInterface::CORRECTION_DAY);
                            if ($dayRepository == $this->sessionDayCourseRepository) {
                                $oldNextNextDayCourse = $dayRepository->findOneBy(['module' => $dayCourse->getModule(), 'ordre' => $oldOrder + 2]);

                            }
                            elseif ($dayRepository == $this->dayCourseRepository){
                                $oldNextNextDayCourse = $dayRepository->findOneBy(['module' => $dayCourse->getModule(), 'ordre' => $oldOrder + 2, 'deleted' => null]);

                            }
                            $oldNextNextDayCourse->setStatus(DayInterface::NORMAL_DAY);
                        } else {
                            $newDay->setStatus(DayInterface::NORMAL_DAY);
                            $oldNextDayCourse->setStatus(DayInterface::CORRECTION_DAY);
                        }

                    }
                    $this->em->flush();
                    break;
                case DayInterface::VALIDATING_DAY :
                    if ($newDay->getStatus() === DayInterface::NORMAL_DAY) {
                        $newDay->setStatus(DayInterface::CORRECTION_DAY);
                        $oldNextDayCourse->setStatus(DayInterface::NORMAL_DAY);
                    } elseif ($newDay->getStatus() === DayInterface::VALIDATING_DAY) {
                        $dayCourse->setStatus(DayInterface::NORMAL_DAY);
                        $oldNextDayCourse->setStatus(DayInterface::NORMAL_DAY);
                    } elseif ($newDay->getStatus() === DayInterface::CORRECTION_DAY) {
                        $oldPreviousNewDay->setStatus(DayInterface::NORMAL_DAY);
                        $oldNextDayCourse->setStatus(DayInterface::NORMAL_DAY);
                    }
                    $this->em->flush();
                    break;
            }

        } else {

            switch ($dayCourse->getStatus()) {
                case DayInterface::NORMAL_DAY :
                    // si newDay est un jour normal ou bien un jour de correction: aucun impact
                    if ($newDay->getStatus() === DayInterface::VALIDATING_DAY) {
                        $dayCourse->setStatus(DayInterface::CORRECTION_DAY);
                        $oldNextNewDay->setStatus(DayInterface::NORMAL_DAY);
                    }
                    $this->em->flush();
                    break;
                case DayInterface::CORRECTION_DAY :
                    if ($newDay->getStatus() === DayInterface::NORMAL_DAY) {
                        if ($oldNextDayCourse->getStatus() === DayInterface::VALIDATING_DAY) {
                            $oldPreviousDayCourse->setStatus(DayInterface::NORMAL_DAY);
                            $dayCourse->setStatus(DayInterface::NORMAL_DAY);
                        } else {
                            $oldNextDayCourse->setStatus(DayInterface::CORRECTION_DAY);
                            $dayCourse->setStatus(DayInterface::NORMAL_DAY);
                        }
                    } elseif ($newDay->getStatus() === DayInterface::VALIDATING_DAY) {
                        $oldPreviousDayCourse->setStatus(DayInterface::NORMAL_DAY);
                        $oldNextNewDay->setStatus(DayInterface::NORMAL_DAY);

                    } elseif ($newDay->getStatus() === DayInterface::CORRECTION_DAY) {

                        $dayCourse->setStatus(DayInterface::NORMAL_DAY);
                        $oldPreviousDayCourse->setStatus(DayInterface::NORMAL_DAY);
                    }
                    $this->em->flush();
                    break;
                case DayInterface::VALIDATING_DAY :
                    if ($newDay->getStatus() === DayInterface::NORMAL_DAY) {
                        if ((!is_null($oldNextNewDay) && $oldNextNewDay->getStatus() == DayInterface::VALIDATING_DAY) || $newOrder == $countDays) {
                            $dayCourse->setStatus(DayInterface::NORMAL_DAY);
                            $oldNextDayCourse->setStatus(DayInterface::NORMAL_DAY);
                        } else {
                            $oldNextDayCourse->setStatus(DayInterface::NORMAL_DAY);
                            $oldNextNewDay->setStatus(DayInterface::CORRECTION_DAY);
                        }

                    } elseif ($newDay->getStatus() === DayInterface::VALIDATING_DAY) {
                        $dayCourse->setStatus(DayInterface::CORRECTION_DAY);
                        $oldNextDayCourse->setStatus(DayInterface::NORMAL_DAY);
                        $oldNextNewDay->setStatus(DayInterface::NORMAL_DAY);

                    } elseif ($newDay->getStatus() === DayInterface::CORRECTION_DAY) {

                        if ($newOrder == $oldOrder + 1 && !is_null($oldNextNewDay)) {
                            // newOrder = le jour de correction de old order et le jour de correction suivi d'un jour validant (1v2c3v4c5n)(1 devient 2)
                            if ($oldNextNewDay->getStatus() === DayInterface::VALIDATING_DAY) {
                                $newDay->setStatus(DayInterface::NORMAL_DAY);
                                $dayCourse->setStatus(DayInterface::NORMAL_DAY);
                            } // newOrder = le jour de correction de old order et le jour de correction suivi d'un jour normal (vcnvc)(1 devient 2)
                            elseif ($oldNextNewDay->getStatus() === DayInterface::NORMAL_DAY) {
                                $newDay->setStatus(DayInterface::NORMAL_DAY);
                                $oldNextNewDay->setStatus(DayInterface::CORRECTION_DAY);
                            }
                        } elseif (!is_null($oldNextNewDay) && $oldNextNewDay->getStatus() === DayInterface::NORMAL_DAY) {
                            $oldNextDayCourse->setStatus(DayInterface::NORMAL_DAY);
                            $oldNextNewDay->setStatus(DayInterface::CORRECTION_DAY);
                        } else {
                            $dayCourse->setStatus(DayInterface::NORMAL_DAY);
                            $oldNextDayCourse->setStatus(DayInterface::NORMAL_DAY);
                        }

                    }
                    $this->em->flush();
                    break;
            }

        }

    }

}

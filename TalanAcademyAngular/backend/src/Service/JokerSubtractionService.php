<?php


namespace App\Service;


use App\Entity\Correction;
use App\Entity\CorrectionResult;
use App\Entity\Session;
use App\Entity\SessionDayCourse;
use App\Entity\SessionJokerCheck;
use App\Entity\SessionUserData;
use App\Entity\SubmissionWorks;
use App\Event\JokerRetraitEvent;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Zend\EventManager\Event;

class JokerSubtractionService
{
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var AssociateDateService
     */
    private $associateDateService;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var SessionService
     */
    private $sessionService;
    /**
     * @var CalculateAverageService
     */
    private $averageService;

    private $dispatcher;

    public function __construct(EntityManagerInterface $em, SessionService $sessionService, CalculateAverageService $averageService, AssociateDateService $associateDateService, LoggerInterface $logger, EventDispatcherInterface $dispatcher)
    {
        $this->em = $em;
        $this->associateDateService = $associateDateService;
        $this->logger = $logger;
        $this->sessionService = $sessionService;
        $this->averageService = $averageService;
        $this->dispatcher = $dispatcher;
    }

    public function noSubmittedWork()
    {
        $this->logger->info('Execution de ' . __METHOD__);
        $today = new \DateTime();
        $sessions = $this->em->getRepository(Session::class)->findSessionsInProgress();
        $this->logger->info('Nombre de sessions encours trouvees: ' . count($sessions));
        foreach ($sessions as $session) {
            $this->logger->info('Session en cours de traitement : ' . $session->getId());
            $nbrDays = $this->em->getRepository(Session::class)->countSessionsDays($session);
            $this->logger->info('Nombre de jours pour cette session : ' . $nbrDays);

            /* condition session sans jour */
            if ($nbrDays) {
                $currentDay = null;
                $previoursDay = null;
                $date=$this->associateDateService->getCurrentDayAndPreviousDay($session);
                $currentDay=$date['currentDay'];
                $previoursDay=$date['previousDay'];
                $this->logger->info('currentDay pour cette session: '.$currentDay->getStatus());
                if ($currentDay->getStatus() == SessionDayCourse::VALIDATING_DAY) {
                    $maxSubmitTime = $session->getHMaxSubmit();
                    $checkingTime = clone $today;
                    $checkingTime->setTime($maxSubmitTime, 0, 0);

                        if ($today > $checkingTime) {
                        $sessionJokerCheck = $this->em->getRepository(SessionJokerCheck::class)->findBySessionAndDate($session, $today->format('Y-m-d'));
                        if (!$sessionJokerCheck) {
                            $sessionJokerCheck = new SessionJokerCheck();
                            $sessionJokerCheck->setSessionJokerCheck($session);
                            $sessionJokerCheck->setSubmittedWork(''.$today->format('Y-m-d'));
                            $this->em->persist($sessionJokerCheck);
                            $this->em->flush();
                            $this->logger->info('Traitement jour validant: ' . $currentDay->getId());
                            $this->processJokerRemoveForNonSubmittedWork($session, $currentDay,$today);

                        }
                        elseif ($sessionJokerCheck && is_null($sessionJokerCheck[0]->getSubmittedWork())) {
                            $sessionJokerCheck->setSubmittedWork($today);
                            $this->em->persist($sessionJokerCheck);
                            $this->em->flush();
                            $this->logger->info('Traitement jour validant: ' . $currentDay->getId());
                            $this->processJokerRemoveForNonSubmittedWork($session, $currentDay,$today);
                        }
                    } else {
                        $this->logger->info('Limte non atteinte');
                    }

                } elseif ($currentDay->getStatus() == SessionDayCourse::CORRECTION_DAY) {
                    $checkingTime = clone $previoursDay['date'];
                    $maxSubmitTime = $session->getHMaxSubmit();
                    $checkingTime->setTime($maxSubmitTime, 0, 0);
                    if ($today > $checkingTime) {
                        $sessionJokerCheck = $this->em->getRepository(SessionJokerCheck::class)->findBySessionAndDate($session, $previoursDay['date']->format('Y-m-d'));
                        if (!$sessionJokerCheck) {
                            $sessionJokerCheck = new SessionJokerCheck();
                            $sessionJokerCheck->setSessionJokerCheck($session);
                            $sessionJokerCheck->setSubmittedWork(''.$previoursDay['date']->format('Y-m-d'));
                            $this->em->persist($sessionJokerCheck);
                            $this->em->flush();
                            $this->logger->info('Traitement jour correction: ' . $previoursDay['day']->getId());
                            $this->processJokerRemoveForNonSubmittedWork($session, $previoursDay['day'],$previoursDay['date']);
                        } elseif ($sessionJokerCheck && is_null($sessionJokerCheck[0]->getSubmittedWork())) {
                            $sessionJokerCheck[0]->setSubmittedWork(''.$previoursDay['date']->format('Y-m-d'));
                            $this->em->persist($sessionJokerCheck[0]);
                            $this->em->flush();
                            $this->logger->info('Traitement jour correction: ' . $previoursDay['day']->getId());
                            $this->processJokerRemoveForNonSubmittedWork($session, $previoursDay['day'],$previoursDay['date']);
                        }
                    } else {
                        $this->logger->info('Limite non atteinte');
                    }
                } else {
                    $this->logger->info('Jour normal, rien à faire');
                }
            } else {
                $this->logger->info('Session vide sans aucun jour');
            }
        }
    }

    private function processJokerRemoveForNonSubmittedWork(Session $session, SessionDayCourse $day, \DateTime $dateValidationDay)
    {
        $students=[];
        $this->logger->info('Execution de ' . __METHOD__);
        $sessionUsers = $this->em->getRepository(SessionUserData::class)->findBy(['session' => $session]);
        $this->logger->info('Nombre d\'apprentis : ' . count($sessionUsers));
        foreach ($sessionUsers as $sessionUser) {
            if($sessionUser->getSubscriptionDate() < $dateValidationDay ) {
                $this->logger->info('vérification de la soumission pour sessionUserData: '. $sessionUser->getId());
                $workSubmission = $this->em->getRepository(SubmissionWorks::class)->findBy(['course' => $day, 'student' => $sessionUser->getUser()]);
                if (!$workSubmission) {
                    $this->logger->info('L\'apprenti:'.$sessionUser->getUser()->getId().' n\'a pas soumis son travail');
                    $this->sessionService->retraitJokerFromUser($sessionUser->getUser()->getId());
                    $this->logger->info('Retrait joker pour apprenti :'.$sessionUser->getUser()->getId());
                    array_push($students, $sessionUser->getUser()->getFirstName().' '.$sessionUser->getUser()->getLastName());
                }
           }else{
                $this->logger->info('Moussaillon ajouté à la session après le jour validant');
            }
       }
        if (count($students)!= 0){
            $event = new JokerRetraitEvent($students, 'NonSubmittedWork', $session, $day);
            $this->dispatcher->dispatch( JokerRetraitEvent::NAME, $event);
        }

    }


    public function noCorrectionMade()
    {
        $this->logger->info('Execution de ' . __METHOD__);
        $today = new \DateTime();
        $checkingTime = clone $today;
        $sessions = $this->em->getRepository(Session::class)->findSessionsInProgress();
        $this->logger->info('Nombre de sessions encours trouvées: ' . count($sessions));
        foreach ($sessions as $session) {
            $this->logger->info('Session en cours de traitement : ' . $session->getId());
            $nbrDays = $this->em->getRepository(Session::class)->countSessionsDays($session);
            $this->logger->info('Nombre de jours pour cette session : ' . $nbrDays);
            if ($nbrDays) {
                $currentDay = null;
                $date=$this->associateDateService->getCurrentDayAndPreviousDay($session);
                $currentDay=$date['currentDay'];
                if (!is_null($currentDay)) {
                    if ($currentDay->getStatus() == SessionDayCourse::CORRECTION_DAY) {
                        $maxCorrectionTime = $session->getHMaxCorection();
                        $checkingTime->setTime($maxCorrectionTime, 0, 0);
                        if ($today > $checkingTime) {
                            $sessionJokerCheck = $this->em->getRepository(SessionJokerCheck::class)->findBySessionAndDate($session, $today->format('Y-m-d'));
                            if (!$sessionJokerCheck) {
                                $sessionJokerCheck = new SessionJokerCheck();
                                $sessionJokerCheck->setSessionJokerCheck($session);
                                $sessionJokerCheck->setCorrection($today->format('Y-m-d'));
                                $this->em->persist($sessionJokerCheck);
                                $this->em->flush();
                                $this->logger->info('Traitement jour correction: ' . $currentDay->getId());
                                $this->processJokerRemoveForNonCorrectionMission($session, $currentDay);
                            } elseif ($sessionJokerCheck && is_null($sessionJokerCheck[0]->getCorrection())) {
                                $sessionJokerCheck[0]->setCorrection($today->format('Y-m-d'));
                                $this->em->persist($sessionJokerCheck[0]);
                                $this->em->flush();
                                $this->logger->info('Traitement jour correction: ' . $currentDay->getId());
                                $this->processJokerRemoveForNonCorrectionMission($session, $currentDay);
                            } else {
                                $this->logger->info('Traitement déjà fait');
                            }
                        } else {
                            $this->logger->info('Limite non atteinte');
                        }
                    } else {
                        $this->logger->info('Ce n\'est pas un jour de correction');
                    }
                } else {
                    $this->logger->info('Rien à faire aujourd\'hui');
                }
            } else {
                $this->logger->info('Session vide sans aucun jour');
            }
        }
    }

    private function processJokerRemoveForNonCorrectionMission(Session $session, SessionDayCourse $day)
    {
        $students=[];
        $this->logger->info('Execution de ' . __METHOD__);
        $sessionDatas = $session->getSessionUserDatas();
        foreach ($sessionDatas as $sessionData) {
            $this->logger->info('Vérification de la correction pour sessionUserData: '. $sessionData->getId());
            $student = $sessionData->getUser();
            $corrections = $this->em->getRepository(Correction::class)->findBy(array('day' => $day, 'corrector' => $student));
            $hasMadeCorrection = true;
            foreach ($corrections as $correction) {
                /** @var Correction $correction */
                foreach ($correction->getCorrectionResults() as $correctionResult)
                    $this->logger->info('Apprenti :'.$sessionData->getId().' => correction result : '.$correctionResult->getResult());
                    if (($correctionResult->getResult())=="") {
                        $hasMadeCorrection = false;
                        break;
                    }
            }
            $this->logger->info('has made correction '.$hasMadeCorrection);
            $this->logger->info('corrections '.count($corrections));
            if (!$hasMadeCorrection && $corrections!=null) {
                $this->sessionService->retraitJokerFromUser($student->getId());
                $this->logger->info('Retrait joker pour l\'apprenti '. $student->getId());
                array_push($students, $student->getFirstName().' '.$student->getLastName());
            }
        }
        if (count($students) != 0) {
            $event = new JokerRetraitEvent($students, 'NonCorrectionMission',$session, $day);
            $this->dispatcher->dispatch( JokerRetraitEvent::NAME, $event);
        }
    }


    public function lessThanAverage()
    {
        $this->logger->info('Execution de ' . __METHOD__);
        $today = new \DateTime();
        $checkingTime = clone $today;
        $sessions = $this->em->getRepository(Session::class)->findSessionsInProgress();
        $this->logger->info('Nombre de sessions encours trouvées: ' . count($sessions));
        foreach ($sessions as $session) {
            $this->logger->info('Session en cours de traitement : ' . $session->getId());
            $nbrDays = $this->em->getRepository(Session::class)->countSessionsDays($session);
            $this->logger->info('Nombre de jours pour cette session : ' . $nbrDays);
            if ($nbrDays) {
                $currentDay = null;
                $date=$this->associateDateService->getCurrentDayAndPreviousDay($session);
                $currentDay=$date['currentDay'];
                if (!is_null($currentDay)) {
                    if ($currentDay->getStatus() == SessionDayCourse::CORRECTION_DAY) {
                        $maxCorrectionTime = $session->getHMaxCorection();
                        $checkingTime->setTime($maxCorrectionTime, 0, 0);
                        if ($today > $checkingTime) {
                            $sessionJokerCheck = $this->em->getRepository(SessionJokerCheck::class)->findBySessionAndDate($session, $today->format('Y-m-d'));
                            if (!$sessionJokerCheck) {
                                $sessionJokerCheck = new SessionJokerCheck();
                                $sessionJokerCheck->setSessionJokerCheck($session);
                                $sessionJokerCheck->setAverage($today->format('Y-m-d'));
                                $this->em->persist($sessionJokerCheck);
                                $this->em->flush();
                                $this->logger->info('Traitement jour correction: ' . $currentDay->getId());
                                $this->processJokerRemoveForLessThanAverage($session, $currentDay);
                            } elseif ($sessionJokerCheck && is_null($sessionJokerCheck[0]->getAverage())) {
                                $sessionJokerCheck[0]->setAverage($today->format('Y-m-d'));
                                $this->em->persist($sessionJokerCheck[0]);
                                $this->em->flush();
                                $this->logger->info('Traitement jour correction: ' . $currentDay->getId());
                                $this->processJokerRemoveForLessThanAverage($session, $currentDay);
                            } else {
                                $this->logger->info('Traitement déjà fait');
                            }
                        } else {
                            $this->logger->info('Limte non atteinte');
                        }
                    } else {
                        $this->logger->info('Ce n\'est pas un jour de correction');
                    }
                } else {
                    $this->logger->info('Rien à faire aujourd\'hui');
                }
            } else {
                $this->logger->info('Session vide sans aucun jour');
            }
        }
    }

    private function processJokerRemoveForLessThanAverage(Session $session, SessionDayCourse $day)
    {
        $students = [];
        $this->logger->info('Execution de ' . __METHOD__);
        $sessionDatas = $session->getSessionUserDatas();
        foreach ($sessionDatas as $sessionData) {
            $student = $sessionData->getUser();
            $this->logger->info('Student ='.$student->getId().' '.$student->getFullName());
            $average = $this->averageService->calculateDayScore($day, $student);
            $this->logger->info('average =>'.json_encode($average));
            if ($average) {
                $calculated = $average['note'];
                $total = $average['total'];
                $this->logger->info('note = '.$calculated.' sur '.$total);
                if ($total) {
                    $percentage = ($calculated * 100) / $total;
                    $this->logger->info('pourcentage=>'.$percentage);
                    if ($percentage < $session->getPercentageOrder()) {
                        $this->sessionService->retraitJokerFromUser($student->getId());
                        $this->logger->info('Un joker sera retiré !');
                        array_push($students, $student->getFirstName().' '.$student->getLastName());
                    }
                }
            }
        }
        if (count($students) != 0) {
            $event = new JokerRetraitEvent($students, 'LessThanAverage', $session, $day);
            $this->dispatcher->dispatch( JokerRetraitEvent::NAME, $event);
        }
    }

}

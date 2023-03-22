<?php
/**
 * Created by PhpStorm.
 * User: sarfaoui
 * Date: 11/06/2019
 * Time: 09:17
 */

namespace App\Service;


use App\Entity\Correction;
use App\Entity\CorrectionResult;
use App\Entity\Session;
use App\Entity\SessionDayCourse;
use App\Entity\SessionOrder;
use App\Entity\SessionUserData;
use App\Entity\SubmissionWorks;
use App\Entity\User;
use App\Event\CorrectionReportEvent;
use App\Event\NoInstructionsEvent;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class CronService
 * @package App\Service
 */
class CronService
{


    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var AssociateDateService
     */
    private $associateDateService;
    private $logger;
    private $dispatcher;

    public function __construct(EntityManagerInterface $em, AssociateDateService $associateDateService, LoggerInterface $logger, EventDispatcherInterface $dispatcher)
    {
        $this->em = $em;
        $this->associateDateService = $associateDateService;
        $this->logger = $logger;
        $this->dispatcher = $dispatcher;
    }

    public function launchCrossCorrections()
    {
        $today = new \DateTime();
        $sessions = $this->em->getRepository(Session::class)->findSessionsInProgress();
        $this->logger->info('Nombre de sessions encours trouvees: ' . count($sessions));
        foreach ($sessions as $session) {

            $this->logger->info('Session en cours de traitement : ' . $session->getId());
            $nbrDays = $this->em->getRepository(Session::class)->countSessionsDays($session);
            $this->logger->info('Nombre de jours pour cette session : ' . $nbrDays);

            /* condition session sans jour */
            if ($nbrDays) {

                $days = $this->associateDateService->getDayDateArray($session);
                $previousDay = null;

                foreach ($days as $daySession) {

                    $day = $this->em->getRepository(SessionDayCourse::class)->find($daySession['id']);
                    $this->logger->info('Jour en cours de traitement : ' . $day->getId() . ', Date :' . $daySession['date']->format('d-m-Y') . ', status : ' . $day->getStatus());
                    $correctionExist = $this->em->getRepository(Correction::class)->findBy(['day' => $day]);

                    if ($correctionExist) {
                        $this->logger->info('La repartition de corrections pour ce jour deja existe');
                    }

                    /*  condition jour est égal jour de correction et la répartition des correction n'est pas encore fait */
                    if ($today->setTime(0, 0) == $daySession['date']->setTime(0, 0)
                        && $day->getStatus() == SessionDayCourse::CORRECTION_DAY
                        && !$correctionExist) {
                        $lastValidationDay = $this->em->getRepository(SessionDayCourse::class)->findBy(['module' => $day->getModule(), 'ordre' => $day->getOrdre() - 1]);
                        $this->logger->info('Jour en cours de traitement convient les condition (jour actuel et un jour de correction et il n y a pas des corrections qui sont deja cree)');
                        $optionNbrCorrections = $session->getNbrValidation();
                        $this->logger->info('Nombre de correction pour cette session est : ' . $optionNbrCorrections);
                        $sessionUsers = $this->em->getRepository(SessionUserData::class)->findBy(['session' => $session]);
                        $this->logger->info('Nombre d\'apprentis : ' . count($sessionUsers));

                        $students = [];

                        foreach ($sessionUsers as $sessionUser) {
                            $student = $sessionUser->getUser();
                            $submittedWork = $this->em->getRepository(SubmissionWorks::class)->findOneBy(['student' => $student, 'course' => $lastValidationDay]);
                            if ($submittedWork != null) {
                                array_push($students, $sessionUser->getUser()->getId());
                                $this->logger->info('l\'apprenti ' . $student->getId() . ' a soumis son travail');
                            } else {
                                $this->logger->info('l\'apprenti ' . $student->getId() . ' n\'a pas soumis son travail');
                            }
                        }
                        $optionNbrStudents = count($students);
                        $this->logger->info('Nombre des étudiants qui ont soumis leurs travaux: ' . $optionNbrStudents);

                        if ($optionNbrCorrections >= $optionNbrStudents) {
                            $optionNbrCorrections = $optionNbrStudents - 1;
                        }

                        if ($optionNbrStudents && $optionNbrCorrections) {
                            $outputCorrections = $this->crossCorrection($students, $optionNbrCorrections);
                            if (key_exists('error', $outputCorrections)) {
                                $this->logger->error($outputCorrections['error']);
                            } else {
                                $this->logger->info('Nombre de corrections cree : ' . count($outputCorrections));
                                $this->persistCorrections($previousDay, $outputCorrections, $day, $session);
                            }
                        }
                        break;
                    } else {
                        $this->logger->info('Ce jour ne convient pas un de ces condition (jour actuel ou un jour de correction)');
                    }
                    $previousDay = $daySession;
                }
            }

        }
    }

    private function persistCorrections($previousDay, $outputCorrections, $day, $session)
    {
        $corrections = [];
        $validationDay = false;
        $this->logger->info('Sauvegarde de corrections :');
        foreach ($outputCorrections as $outputCorrection) {
            $this->logger->info('Correction en cours de traitement : apprentit ' . key($outputCorrection) . ' corrige ' . ' apprentit ' . $outputCorrection[key($outputCorrection)]);
            $corrections[key($outputCorrection)]=$outputCorrection[key($outputCorrection)];
            $orders = $this->em->getRepository(SessionOrder::class)->findBy(['dayCourse' => $previousDay['id']]);
            $this->logger->info('Nombre de consignes : ' . count($orders));
            if ($orders) {
                $corrector = $this->em->getRepository(User::class)->find(key($outputCorrection));
                $corrected = $this->em->getRepository(User::class)->find($outputCorrection[key($outputCorrection)]);
                $correction = new Correction();
                $correction->setDay($day);
                $correction->setCorrector($corrector);
                $correction->setCorrected($corrected);

                $this->em->persist($correction);
                $this->logger->info('Preparation de sauvegarde de corrections');
                $this->logger->info('Nombre de consignes : ' . count($orders));

                $this->logger->info('Sauvegarde de consignes pour cette correction :');
                foreach ($orders as $order) {
                    $this->logger->info('Consigne en cours de traitement : ' . $order->getId());
                    $correctionResult = new CorrectionResult();
                    $correctionResult->setCorrection($correction);
                    $correctionResult->setOrderCourse($order);
                    $this->em->persist($correctionResult);
                    $this->logger->info('Préparation de sauvegarde de consigne ' . $order->getId());
                }

                $this->em->flush();

                $this->logger->info('Sauvegarde termine');

            } else {
                $this->logger->info('Il n\'y a pas de consignes pour cette correction dans le jour validant : ' . $previousDay['id']);
                $validationDay = $previousDay['id'];
            }
        }
        if ($validationDay) {
            $this->logger->info('Envoie du mail n\'y a pas de consignes pour cette correction dans le jour validant '. $previousDay['id']);
            $event = new NoInstructionsEvent($validationDay, $session);
            $this->dispatcher->dispatch(NoInstructionsEvent::NAME, $event);
        }
        if (count($corrections) != 0) {
            $this->logger->info('Envoie du mail contenant le rapport de correction de '.$day->getId());
            $eventReport = new CorrectionReportEvent($corrections, $session);
            $this->dispatcher->dispatch(CorrectionReportEvent::NAME, $eventReport);
        }
    }

    public function crossCorrection($students, $optionNbrCorrections)
    {

        shuffle($students);
        $arrayLength = count($students);
        if ($optionNbrCorrections >= $arrayLength) {
            $optionNbrCorrections = $arrayLength - 1;
        }

        $corrections = array();

        for ($x = 0; $x < $optionNbrCorrections; $x++) {
            $otherChoices = $students;
            for ($i = 0; $i <= $x; $i++) {
                array_unshift($otherChoices, $students[count($students) - $i - 1]);
            }

            foreach ($students as $key => $classMember) {
                $corrections[][$classMember] = $otherChoices[$key];
            }

        }

        return $corrections;

    }
}

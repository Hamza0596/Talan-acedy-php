<?php
/**
 * Created by PhpStorm.
 * User: jghada
 * Date: 11/06/2019
 * Time: 10:56
 */

namespace App\Service;

use App\Repository\CorrectionRepository;
use App\Repository\SessionDayCourseRepository;

class CalculateAverageService
{
    /**
     * @var CorrectionRepository
     */
    private $correctionRepository;
    /**
     * @var SessionService
     */
    private $sessionService;
    private $sessionDayCourseRepository;
    private $associateDateService;

    /**
     * CalculateAverageService constructor.
     * @param CorrectionRepository $correctionRepository
     * @param SessionService $sessionService
     * @param SessionDayCourseRepository $sessionDayCourseRepository
     * @param AssociateDateService $associateDateService
     */
    public function __construct(CorrectionRepository $correctionRepository, SessionService $sessionService, SessionDayCourseRepository $sessionDayCourseRepository, AssociateDateService $associateDateService)
    {
        $this->correctionRepository = $correctionRepository;
        $this->sessionService = $sessionService;
        $this->sessionDayCourseRepository = $sessionDayCourseRepository;
        $this->associateDateService = $associateDateService;
    }

    public function calculateMinMaxScore($session, $user)
    {
        $avgDays = [];
        $sessionAverage = [];
        $sumNote = 0;
        $average = 0;
        $min = 0;
        $max = 0;
        $validatingDay = $this->sessionService->countPastValidatingDay($session)['pastValidatingDays'];
        if (isset($validatingDay) && count($validatingDay) != 0) {
            $currentDay = new \DateTime();
            $totalCorrectedDays = 0;
            for ($i = 0; $i < count($validatingDay); $i++) {
                $correctionDay = $this->sessionDayCourseRepository->findOneBy(array('module' => $validatingDay[$i]->getModule(), 'ordre' => $validatingDay[$i]->getOrdre() + 1));
                $correctionDayDate = $this->associateDateService->getPlanifiedDateFromSessionDay($correctionDay);
                $checkingTime = clone $currentDay;
                $checkingTime->setTime($session->getHMaxCorection(), 0, 0);
                if ($correctionDayDate < $currentDay || ($correctionDayDate->format('Y-m-d') == $currentDay->format('Y-m-d') && $currentDay > $checkingTime)) {
                    /**check if submitted work or corrected**/
                    $corrections = $this->correctionRepository->findBy(['day' => $correctionDay, 'corrected' => $user]);

                    if ($corrections) {
                        $hasCorrection = false;
                        foreach ($corrections as $correction) {
                            foreach ($correction->getCorrectionResults() as $result) {
                                if ($result->getResult() == 't' || $result->getResult() == 'f') {
                                    $hasCorrection = true;
                                    break;
                                }
                            }
                            if ($hasCorrection) {
                                break;
                            }
                        }

                        if ($hasCorrection) {
                            $totalCorrectedDays++;
                            $correctionResult = $this->calculateDayScore($correctionDay, $user);
                            if ($correctionResult != false) {
                                $note = $correctionResult['note'];
                                $total = $correctionResult['total'];
                                $avgDay = 100 * $note / $total;
                                $avgDays[] = $avgDay;
                                $sumNote += $avgDay;
                            }
                        }
                    }


                }//if jour dans condition
            }//end for

            if (count($avgDays)) {
                $min = min($avgDays);
                $max = max($avgDays);
                $average = $sumNote / $totalCorrectedDays;
            }


        }
        $sessionAverage['min'] = round($min);
        $sessionAverage['max'] = round($max);
        $sessionAverage['average'] = round($average);
        $sessionAverage['averageDays'] = $avgDays;
        return $sessionAverage;

    }

    public function calculateDayScore($sessionDayCourse, $user)
    {
        $score = [];
        $results = $this->correctionRepository->findByDayUser($sessionDayCourse, $user);
        $note = 0;
        $total = 0;

        if (!empty($results)) {
            for ($i = 0; $i < count($results); $i++) {
                if ($results[$i]['result'] == 't') {
                    $note += $results[$i]['scale'];
                }
                if ($results[$i]['result'] != null) {
                    $total += $results[$i]['scale'];
                }
            }
            //correction number
            $corrections = $this->correctionRepository->findBy(array('day' => $sessionDayCourse, 'corrected' => $user));
            $numberOfCorrections = 0;
            foreach ($corrections as $correction) {
                $numberOfCorrections++;
                $correctionResults = $correction->getCorrectionResults();
                $correctionDone = true;
                if ($correctionResults[0]->getResult() == null) {
                    $correctionDone = false;
                }
                if (!$correctionDone) {
                    $numberOfCorrections--;
                }
            }
            //calculate note +total
            if ($numberOfCorrections != 0) {
                $score['note'] = $note / $numberOfCorrections;
                $score['total'] = $total / $numberOfCorrections;
            }
            return $score;
        } else {
            return false;
        }

    }

    /**
     * calculate the average of day for all apprentice in a given session
     * @param $sessionDayCourse
     * @param $session
     * @return array|string
     */
    public function calculateAverageDayForAllUsers($sessionDayCourse, $session)
    {

        $sessionApprentice = [];
        $apprenticeAverage = 0;
        $apprenticeAverageArray = [];
        $sumApprenticeAverage = 0;
        $apprenticesAverage = [];
        $apprenticesAverage['min'] = 0;
        $apprenticesAverage['max'] = 0;
        $apprenticesAverage['average'] = 0;

        $sessionUserDatas = $session->getSessionUserDatas($session)->toArray();


        if ($sessionUserDatas) {
            //get all sessionApprentice
            for ($i = 0; $i < count($sessionUserDatas); $i++) {
                $sessionApprentice [] = $sessionUserDatas[$i]->getUser();
            }

            $nbSessionApprentice = count($sessionApprentice);
            //calculate average of sessionAprentice
            $nb = 0;
            for ($i = 0; $i < $nbSessionApprentice; $i++) {
                if ($averageDay = $this->calculateDayScore($sessionDayCourse, $sessionApprentice[$i])) {
                    $note = $averageDay['note'];
                    $total = $averageDay['total'];
                    if ($total != 0) {
                        $apprenticeAverage = 100 * $note / $total;
                        $apprenticeAverageArray[] = $apprenticeAverage;
                    }
                    $sumApprenticeAverage += $apprenticeAverage;
                    $nb++;
                }
            }
            if ($nb != 0) {

                $apprenticesAverage['average'] = round($sumApprenticeAverage / $nb);
            }
            if ($apprenticesAverage['average']) {
                $apprenticesAverage['min'] = round(min($apprenticeAverageArray));
                $apprenticesAverage['max'] = round(max($apprenticeAverageArray));
            }
            return $apprenticesAverage;
        } else
            return 'sessionUser doesn\'t exist';

    }

    public function getAverageApprenticePerValidatingDay($session, $apprentice)
    {
        $results = [];
        $pastValidatingDays = [];
        $pastValidatingDateDays = [];
        $moy = 0;
        $pastValidatingDays = $this->sessionService->countPastValidatingDay($session)['pastValidatingDays'];
        $results[] = 'Moyenne';
        foreach ($pastValidatingDays as $pastValidatingDay) {
            $pastValidatingDateDays = [];
            $moy=0;

            $title = $pastValidatingDay->getDescription();
            $correctionDay = $this->sessionDayCourseRepository->findOneBy(array('module' => $pastValidatingDay->getModule(), 'ordre' => $pastValidatingDay->getOrdre() + 1));
            $score = $this->calculateDayScore($correctionDay, $apprentice);

            $pastValidatingDateDays[] = $title;

            if ($score && $score['total'] != 0) {
                    $moy = $score['note'] * 100 / $score['total'];
                    $moy = round($moy);
            }
            $pastValidatingDateDays[] = $moy;

            $results[] = $moy;
        }
        $tab = [];
        $tab[] = $results;
        return $tab;

    }

    public function getPassedValidatingDay($session)
    {
        $pastValidatingDaysTitle = [];
        $pastValidatingDays = $this->sessionService->countPastValidatingDay($session)['pastValidatingDaysDate'];
        foreach ($pastValidatingDays as $pastValidatingDay) {
            $pastValidatingDaysTitle[] = $pastValidatingDay;
        }
        return $pastValidatingDaysTitle;
    }

    public function calculateAverageAfterAdminCorrection($initialNote,$finalNote,$sessionDay,$correctedId)
    {
        $initialAverage = $this->calculateAverage($initialNote);
        $finalAverage = $this->calculateAverage($finalNote);
        $session =$sessionDay->getModule()->getSession();
        $averageSession = $session->getPercentageOrder();
        if ($initialAverage<$averageSession && $finalAverage<$averageSession){
            $decision = 'La note initial était '.$initialAverage.'% ,la note devient '.$finalAverage.'% .Un joker était retiré, la note aprés la correction reste encore inférieur au seuil.';
        }
        elseif ($initialAverage<$averageSession && $finalAverage>=$averageSession){
            $decision = 'La note initial était '.$initialAverage.'% ,la note devient '.$finalAverage.'% .Un joker était retiré, la note aprés la correction devient superieur au seuil. Le joker doit etre ajouté';
        }
        elseif ($initialAverage>=$averageSession && $finalAverage<$averageSession){
            $decision = 'La note initial était '.$initialAverage.'% ,la note devient '.$finalAverage.'% .Un joker était ajouté, la note aprés la correction devient inférieur au seuil. Le joker doit etre retiré';

        }
        else{
            $decision = 'La note initial était '.$initialAverage.'% ,la note devient '.$finalAverage.'% .Pas de modification de joker';

        }
        $session=$sessionDay->getModule()->getSession();
        $average = $this->calculateMinMaxScore($session, $correctedId);
        return ['msg' => $decision,'finalNote'=>$finalAverage,'average'=>$average['average']];



    }


    public function calculateAverage($score)
    {

        if ($score) {
            $moy = 0;
            if ($score['total'] != 0) {
                $moy = $score['note'] * 100 / $score['total'];
                $moy = round($moy);
            }
            return $moy;
        }
    }
}

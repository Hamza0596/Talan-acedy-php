<?php
/**
 * Created by PhpStorm.
 * User: jghada
 * Date: 14/06/2019
 * Time: 09:50
 */

namespace App\Service;


use App\Entity\SessionUserData;
use App\Repository\CursusRepository;
use App\Repository\SessionRepository;
use App\Repository\SessionUserDataRepository;

class AdminStaticService
{
    /**
     * @var SessionRepository
     */
    private $sessionRepository;
    /**
     * @var CursusRepository
     */
    private $cursusRepository;
    /**
     * @var SessionUserDataRepository
     */
    private $sessionUserRepository;


    /**
     * AdminStaticService constructor.
     * @param SessionRepository $sessionRepository
     * @param CursusRepository $cursusRepository
     * @param SessionUserDataRepository $sessionUserRepository
     */
    public function __construct(SessionRepository $sessionRepository, CursusRepository $cursusRepository, SessionUserDataRepository $sessionUserRepository)
    {
        $this->sessionRepository = $sessionRepository;
        $this->cursusRepository = $cursusRepository;
        $this->sessionUserRepository = $sessionUserRepository;
    }


    public function getSuccessRateCursus()
    {
        $result = [];
        $finished_session = [];
        $cursus_finished_session = $this->cursusRepository->findCursusSessionFinished();

        foreach ($cursus_finished_session as $cursus) {
            $success_rate = 0;
            $corsaire = [];
            $corsaire[] = $cursus->getName();
            $finished_session[$cursus->getName()] = $this->sessionRepository->findSessionFinishedArray($cursus);
            foreach ($this->sessionRepository->findSessionFinishedArray($cursus) as $fs) {
                $success_rate = 0;

                $nb_apprentice = $this->sessionUserRepository->countApprentice($fs);
                $nb_cofirmed = $this->sessionUserRepository->countConfirmed($fs);

                if ($nb_apprentice != 0) {
                    $success_rate = 100 * ($nb_cofirmed / $nb_apprentice);
                }
                $corsaire[] = round($success_rate);
            }
            $result[] = $corsaire;
        }

        return $result;


    }


}
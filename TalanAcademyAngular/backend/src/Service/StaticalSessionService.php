<?php


namespace App\Service;


use App\Entity\Session;
use App\Repository\SessionRepository;

class StaticalSessionService
{
    /**
     * @var SessionRepository
     */
    private $sessionRepository;
    /**
     * @var HolidaysService
     */
    private $holidaysService;

    /**
     * StaticalSessionService constructor.
     */
    public function __construct(SessionRepository $sessionRepository, HolidaysService $holidaysService)
    {
        $this->sessionRepository = $sessionRepository;
        $this->holidaysService = $holidaysService;
    }

    public function countAllSession()
    {
        return $this->sessionRepository->countAll();
    }

    public function sessionCompleted()
    {
        return $this->sessionRepository->findBy(['status' => Session::TERMINE]);
    }

    public function daysCount(Session $session)
    {
        return $this->sessionRepository->countSessionsDays($session);
    }

    public function daysValidateCount(Session $session)
    {
        return $this->sessionRepository->countDaysValidate($session);
    }
}

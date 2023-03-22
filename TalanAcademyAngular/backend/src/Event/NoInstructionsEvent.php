<?php


namespace App\Event;


use App\Entity\Session;
use App\Entity\SessionDayCourse;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class NoInstructionsEvent
 * @package App\Event
 * @codeCoverageIgnore
 */
class NoInstructionsEvent extends Event
{

    public const NAME = 'instructions.missed';
    protected $validationDay;
    protected $session;

    public function __construct($validationDay, $session)
    {
        $this->validationDay = $validationDay;
        $this->session = $session;
    }


    /**
     * @return integer
     */
    public function getValidationDay()
    {
        return $this->validationDay;
    }

    /**
     * @return Session
     */
    public function getSession()
    {
        return $this->session;
    }


}

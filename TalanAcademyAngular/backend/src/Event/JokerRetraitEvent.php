<?php


namespace App\Event;


use App\Entity\Session;
use App\Entity\SessionDayCourse;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class JokerRetraitEvent
 * @package App\Event
 * @codeCoverageIgnore
 */
class JokerRetraitEvent extends Event
{
    public const NAME = 'joker.removed';
    protected $students;
    protected $reasonForJokerRemove;
    protected $session;
    protected $day;

    public function __construct($students, $reasonForJokerRemove, $session, $day)
    {
        $this->students = $students;
        $this->reasonForJokerRemove = $reasonForJokerRemove;
        $this->session = $session;
        $this->day = $day;
    }

    /**
     * @return array
     */
    public function getStudents()
    {
        return $this->students;
    }

    /**
     * @return string
     */
    public function getReasonForJokerRemove()
    {
        return $this->reasonForJokerRemove;
    }

    /**
     * @return Session
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * @return SessionDayCourse
     */
    public function getDay()
    {
        return $this->day;
    }
}

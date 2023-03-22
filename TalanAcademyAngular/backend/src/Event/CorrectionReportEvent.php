<?php


namespace App\Event;


use App\Entity\Session;
use Symfony\Component\EventDispatcher\Event;

class CorrectionReportEvent extends Event
{
    public const NAME = 'correction_report.made';
    protected $corrections;
    protected $session;

    public function __construct($corrections, $session)
    {
        $this->corrections = $corrections;
        $this->session = $session;
    }

    /**
     * @return array
     */
    public function getCorrections()
    {
        return $this->corrections;
    }

    /**
     * @return Session
     */
    public function getSession()
    {
        return $this->session;
    }


}

<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SessionJokerCheckRepository")
 */
class SessionJokerCheck
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Session", inversedBy="sessionJokerChecks")
     */
    private $sessionJokerCheck;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $average;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $submittedWork;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $correction;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSessionJokerCheck(): ?Session
    {
        return $this->sessionJokerCheck;
    }

    public function setSessionJokerCheck(?Session $sessionJokerCheck): self
    {
        $this->sessionJokerCheck = $sessionJokerCheck;

        return $this;
    }

    public function getAverage(): ?String
    {
        return $this->average;
    }

    public function setAverage(?String $average): self
    {
        $this->average = $average;

        return $this;
    }

    public function getSubmittedWork(): ?String
    {
        return $this->submittedWork;
    }

    public function setSubmittedWork(?String $submittedWork): self
    {
        $this->submittedWork = $submittedWork;

        return $this;
    }

    public function getCorrection(): ?String
    {
        return $this->correction;
    }

    public function setCorrection(?String $correction): self
    {
        $this->correction = $correction;

        return $this;
    }
}
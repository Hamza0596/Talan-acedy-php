<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SessionMentorRepository")
 */
class SessionMentor
{
    const ACTIVE = 'ACTIVE';
    const INACTIVE = 'INACTIVE';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Session", inversedBy="sessionMentors")
     */
    private $session;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Staff", inversedBy="sessionMentors")
     */
    private $mentor;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getSession(): ?Session
    {
        return $this->session;
    }

    public function setSession(?Session $session): self
    {
        $this->session = $session;

        return $this;
    }

    public function getMentor(): ?Staff
    {
        return $this->mentor;
    }

    public function setMentor(?Staff $mentor): self
    {
        $this->mentor = $mentor;

        return $this;
    }
}

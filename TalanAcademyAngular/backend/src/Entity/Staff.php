<?php
/**
 * Created by PhpStorm.
 * User: sarfaoui
 * Date: 27/03/2019
 * Time: 15:01
 */

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StaffRepository")
 */
class Staff extends User
{
    const ADMIN = "administrateur";
    const MENTOR = "mentor";

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $function;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Cursus", cascade={"persist", "remove"}, inversedBy="staff")
     * @ORM\JoinColumn(nullable=true)
     */
    private $cursus;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $status;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SessionMentor", mappedBy="mentor")
     */
    private $sessionMentors;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\MentorsAppreciation", mappedBy="staff")
     */
    private $mentorsAppreciations;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\SessionProjectSubject", inversedBy="mentor")
     */
    private $sujet;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Student", cascade={"persist", "remove"})
     */
    private $student;



    public function __construct()
    {
        $this->sessionMentors = new ArrayCollection();
        $this->mentorsAppreciations = new ArrayCollection();
        $this->sujet = new ArrayCollection();
    }
    
    public function getFunction(): ?string
    {
        return $this->function;
    }

    public function setFunction(string $function): self
    {
        $this->function = $function;

        return $this;
    }

    public function getCursus(): ?Cursus
    {
        return $this->cursus;
    }

    public function setCursus(?Cursus $cursus): self
    {
        $this->cursus = $cursus;

        return $this;
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


    /**
     * @return Collection|SessionMentor[]
     */
    public function getSessionMentors(): Collection
    {
        return $this->sessionMentors;
    }

    public function addSessionMentor(SessionMentor $sessionMentor): self
    {
        if (!$this->sessionMentors->contains($sessionMentor)) {
            $this->sessionMentors[] = $sessionMentor;
            $sessionMentor->setMentor($this);
        }

        return $this;
    }

    public function removeSessionMentor(SessionMentor $sessionMentor): self
    {
        if ($this->sessionMentors->contains($sessionMentor)) {
            $this->sessionMentors->removeElement($sessionMentor);
            // set the owning side to null (unless already changed)
            if ($sessionMentor->getMentor() === $this) {
                $sessionMentor->setMentor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|MentorsAppreciation[]
     */
    public function getMentorsAppreciations(): Collection
    {
        return $this->mentorsAppreciations;
    }

    public function addMentorsAppreciation(MentorsAppreciation $mentorsAppreciation): self
    {
        if (!$this->mentorsAppreciations->contains($mentorsAppreciation)) {
            $this->mentorsAppreciations[] = $mentorsAppreciation;
            $mentorsAppreciation->setStaff($this);
        }

        return $this;
    }

    public function removeMentorsAppreciation(MentorsAppreciation $mentorsAppreciation): self
    {
        if ($this->mentorsAppreciations->contains($mentorsAppreciation)) {
            $this->mentorsAppreciations->removeElement($mentorsAppreciation);
            // set the owning side to null (unless already changed)
            if ($mentorsAppreciation->getStaff() === $this) {
                $mentorsAppreciation->setStaff(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|SessionProjectSubject[]
     */
    public function getSujet(): Collection
    {
        return $this->sujet;
    }

    public function addSujet(SessionProjectSubject $sujet): self
    {
        if (!$this->sujet->contains($sujet)) {
            $this->sujet[] = $sujet;
        }

        return $this;
    }

    public function removeSujet(SessionProjectSubject $sujet): self
    {
        if ($this->sujet->contains($sujet)) {
            $this->sujet->removeElement($sujet);
        }

        return $this;
    }

    public function getStudent(): ?Student
    {
        return $this->student;
    }

    public function setStudent(?Student $student): self
    {
        $this->student = $student;

        return $this;
    }
}

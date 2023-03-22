<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use PhpParser\Node\Expr\BinaryOp\Mod;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SessionRepository")
 */
class Session implements CurriculumInterface
{
    const EN_ATTENTE = 'en attente';
    const EN_COURS = 'en cours';
    const TERMINE = 'finished';

    const JOKER_NBR = 3;
    const H_MAX_CORRECTION = 12;
    const H_MAX_SUBMIT = 24;
    const PERCENTAGE_ORDER = 50;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $nbrCandidats;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Serializer\Groups({"current_session"})
     */
    private $startDate;

    /**
     * @return mixed
     */
    public function getName()
    {
        return '' .$this->startDate->format('d-m-Y');
    }

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Serializer\Groups({"current_session"})
     */
    private $endDate;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $status;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SessionModule", mappedBy="session")
     */
    private $modules;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Cursus", inversedBy="sessions")
     */
    private $cursus;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $daysNumber;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $moy;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SessionMentor", mappedBy="session")
     */
    private $sessionMentors;

    /**
     * @ORM\Column(type="integer")
     * @Assert\Type(type="integer")
     */
    private $jokerNbr;

    /**
     * @ORM\Column(type="integer")
     * @Assert\Type(type="integer")
     */
    private $hMaxCorection;

    /**
     * @ORM\Column(type="integer")
     * @Assert\Type(type="integer")
     */
    private $hMaxSubmit;

    /**
     * @ORM\Column(type="integer")
     * @Assert\Type(type="integer")
     */
    private $percentageOrder;

    /**
     * @ORM\OneToMany(targetEntity="SessionUserData", mappedBy="session")
     */
    private $sessionUserDatas;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $NbrValidation;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $ordre;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SessionJokerCheck", mappedBy="sessionJokerCheck")
     */
    private $sessionJokerChecks;



    public function __construct()
    {
        $this->hMaxCorection = self::H_MAX_CORRECTION;
        $this->hMaxSubmit = self::H_MAX_SUBMIT;
        $this->jokerNbr = self::JOKER_NBR;
        $this->percentageOrder = self::PERCENTAGE_ORDER;

        $this->modules = new ArrayCollection();
        $this->sessionMentors = new ArrayCollection();
        $this->sessionUserDatas = new ArrayCollection();
        $this->sessionJokerChecks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNbrCandidats(): ?int
    {
        return $this->nbrCandidats;
    }

    public function setNbrCandidats(?int $nbrCandidats): self
    {
        $this->nbrCandidats = $nbrCandidats;

        return $this;
    }

    public function getStartDate()
    {
        return $this->startDate;
    }

    public function setStartDate($startDate=null): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

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

    /**
     * @return Collection|SessionModule[]
     */
    public function getModules(): Collection
    {
        return $this->modules;
    }

    public function addModule(ModuleInterface $sessionModules): self
    {
        if (!$this->modules->contains($sessionModules)) {
            $this->modules[] = $sessionModules;
            $sessionModules->setSession($this);
        }

        return $this;
    }

    public function getDaysNumber(): ?int
    {
        return $this->daysNumber;
    }

    public function setDaysNumber(?int $daysNumber): self
    {
        $this->daysNumber = $daysNumber;

        return $this;
    }

    public function getMoy(): ?float
    {
        return $this->moy;
    }

    public function setMoy(?float $moy): self
    {
        $this->moy = $moy;

        return $this;
    }


    public function removeModule(ModuleInterface $sessionModule): self
    {
        if ($this->modules->contains($sessionModule)) {
            $this->modules->removeElement($sessionModule);
            // set the owning side to null (unless already changed)
            if ($sessionModule->getSession() === $this) {
                $sessionModule->setSession(null);
            }
        }

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
            $sessionMentor->setSession($this);
        }

        return $this;
    }

    public function removeSessionMentor(SessionMentor $sessionMentor): self
    {
        if ($this->sessionMentors->contains($sessionMentor)) {
            $this->sessionMentors->removeElement($sessionMentor);
            // set the owning side to null (unless already changed)
            if ($sessionMentor->getSession() === $this) {
                $sessionMentor->setSession(null);
            }
        }

        return $this;
    }

    public function getJokerNbr(): ?int
    {
        return $this->jokerNbr;
    }

    public function setJokerNbr(int $jokerNbr): self
    {
        $this->jokerNbr = $jokerNbr;

        return $this;
    }

    public function getHMaxCorection(): ?int
    {
        return $this->hMaxCorection;
    }

    public function setHMaxCorection(int $hMaxCorection): self
    {
        $this->hMaxCorection = $hMaxCorection;

        return $this;
    }

    public function getHMaxSubmit(): ?int
    {
        return $this->hMaxSubmit;
    }

    public function setHMaxSubmit(int $hMaxSubmit): self
    {
        $this->hMaxSubmit = $hMaxSubmit;

        return $this;
    }

    public function getPercentageOrder(): ?int
    {
        return $this->percentageOrder;
    }

    public function setPercentageOrder(int $percentageOrder): self
    {
        $this->percentageOrder = $percentageOrder;

        return $this;
    }

    /**
     * @return Collection|SessionUserData[]
     */
    public function getSessionUserDatas(): Collection
    {
        return $this->sessionUserDatas;
    }

    public function addSessionUserData(SessionUserData $sessionUser): self
    {
        if (!$this->sessionUserDatas->contains($sessionUser)) {
            $this->sessionUserDatas[] = $sessionUser;
            $sessionUser->setSession($this);
        }

        return $this;
    }

    public function removeSessionUserData(SessionUserData $sessionUser): self
    {
        if ($this->sessionUserDatas->contains($sessionUser)) {
            $this->sessionUserDatas->removeElement($sessionUser);
            // set the owning side to null (unless already changed)
            if ($sessionUser->getSession() === $this) {
                $sessionUser->setSession(null);
            }
        }

        return $this;
    }

    public function getNbrValidation(): ?int
    {
        return $this->NbrValidation;
    }

    public function setNbrValidation(?int $NbrValidation): self
    {
        $this->NbrValidation = $NbrValidation;

        return $this;
    }

    public function getOrdre(): ?int
    {
        return $this->ordre;
    }

    public function setOrdre(int $ordre): self
    {
        $this->ordre = $ordre;

        return $this;
    }

    /**
     * @return Collection|SessionJokerCheck[]
     */
    public function getSessionJokerChecks(): Collection
    {
        return $this->sessionJokerChecks;
    }

    public function addSessionJokerCheck(SessionJokerCheck $sessionJokerCheck): self
    {
        if (!$this->sessionJokerChecks->contains($sessionJokerCheck)) {
            $this->sessionJokerChecks[] = $sessionJokerCheck;
            $sessionJokerCheck->setSessionJokerCheck($this);
        }

        return $this;
    }

    public function removeSessionJokerCheck(SessionJokerCheck $sessionJokerCheck): self
    {
        if ($this->sessionJokerChecks->contains($sessionJokerCheck)) {
            $this->sessionJokerChecks->removeElement($sessionJokerCheck);
            // set the owning side to null (unless already changed)
            if ($sessionJokerCheck->getSessionJokerCheck() === $this) {
                $sessionJokerCheck->setSessionJokerCheck(null);
            }
        }

        return $this;
    }


}

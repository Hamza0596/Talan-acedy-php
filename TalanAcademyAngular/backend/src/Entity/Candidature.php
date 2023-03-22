<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CandidatureRepository")
 */
class Candidature
{
    const NOUVEAU = 'nouveau';
    const EN_COURS_ETUDE = 'progress';
    const ACCEPTE = 'accepted';
    const REFUSE = 'refused';
    const INVITE_ENTRETIEN = 'interview';
    const ABANDONMENT = 'abandonment';
    const DRAFT = 'draft';
    const STATUS_NOT_ALLOW = [self::DRAFT, self::EN_COURS_ETUDE, self::INVITE_ENTRETIEN, self::NOUVEAU];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Serializer\Groups({"candidature"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Serializer\Groups({"candidature"})
     */
    private $status;

    /**
     * @ORM\Column(type="datetime")
     * @Serializer\Groups({"candidature"})
     */
    private $datePostule;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Serializer\Groups({"candidature"})
     */
    private $cv;

    /**
     * @ORM\Column(type="string", length=255 ,nullable=true)
     * @Serializer\Groups({"candidature"})
     */
    private $degree;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Serializer\Groups({"candidature"})
     */
    private $linkLinkedin;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     * @Serializer\Groups({"candidature"})
     */
    private $grades;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Serializer\Groups({"candidature"})
     */
    private $currentSituation;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Serializer\Groups({"candidature"})
     */
    private $itExperience;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Cursus", inversedBy="candidatures")
     * @ORM\JoinColumn(nullable=false)
     * @Serializer\Groups({"candidature"})
     */
    private $cursus;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Student", inversedBy="candidatures")
     * @ORM\JoinColumn(nullable=false)
     * @Serializer\Groups({"candidature"})
     */
    private $candidat;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CandidatureState", mappedBy="candidature")
     */
    private $candidatureStates;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\SessionUserData", mappedBy="candidature", cascade={"persist", "remove"})
     */
    private $sessionUserData;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\PreparcoursCandidate", mappedBy="candidature", cascade={"persist", "remove"})
     */
    private $preparcoursCandidate;

    public function __construct()
    {
        $this->candidatureStates = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDatePostule(): ?\DateTimeInterface
    {
        return $this->datePostule;
    }

    public function setDatePostule(\DateTimeInterface $datePostule): self
    {
        $this->datePostule = $datePostule;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCv()
    {
        return $this->cv;
    }

    /**
     * @param mixed $cv
     */
    public function setCv($cv): void
    {
        $this->cv = $cv;
    }

    /**
     * @return mixed
     */
    public function getDegree()
    {
        return $this->degree;
    }

    /**
     * @param mixed $degree
     */
    public function setDegree($degree): void
    {
        $this->degree = $degree;
    }

    /**
     * @return mixed
     */
    public function getLinkLinkedin()
    {
        return $this->linkLinkedin;
    }

    /**
     * @param mixed $linkLinkedin
     */
    public function setLinkLinkedin($linkLinkedin): void
    {
        $this->linkLinkedin = $linkLinkedin;
    }

    /**
     * @return mixed
     */
    public function getGrades()
    {
        return $this->grades;
    }

    /**
     * @param mixed $grades
     */
    public function setGrades($grades): void
    {
        $this->grades = $grades;
    }

    /**
     * @return mixed
     */
    public function getCurrentSituation()
    {
        return $this->currentSituation;
    }

    /**
     * @param mixed $currentSituation
     */
    public function setCurrentSituation($currentSituation): void
    {
        $this->currentSituation = $currentSituation;
    }

    /**
     * @return mixed
     */
    public function getItExperience()
    {
        return $this->itExperience;
    }

    /**
     * @param mixed $itExperience
     */
    public function setItExperience($itExperience): void
    {
        $this->itExperience = $itExperience;
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

    public function getCandidat(): ?Student
    {
        return $this->candidat;
    }

    public function setCandidat(?Student $candidat): self
    {
        $this->candidat = $candidat;

        return $this;
    }

    /**
     * @return Collection|CandidatureState[]
     */
    public function getCandidatureStates(): Collection
    {
        return $this->candidatureStates;
    }

    public function addCandidatureState(CandidatureState $candidatureState): self
    {
        if (!$this->candidatureStates->contains($candidatureState)) {
            $this->candidatureStates[] = $candidatureState;
            $candidatureState->setCandidature($this);
        }

        return $this;
    }

    public function removeCandidatureState(CandidatureState $candidatureState): self
    {
        if ($this->candidatureStates->contains($candidatureState)) {
            $this->candidatureStates->removeElement($candidatureState);
            // set the owning side to null (unless already changed)
            if ($candidatureState->getCandidature() === $this) {
                $candidatureState->setCandidature(null);
            }
        }

        return $this;
    }

    public function getSessionUserData(): ?SessionUserData
    {
        return $this->sessionUserData;
    }

    public function setSessionUserData(?SessionUserData $sessionUserData): self
    {
        $this->sessionUserData = $sessionUserData;

        // set (or unset) the owning side of the relation if necessary
        $newCandidature = $sessionUserData === null ? null : $this;
        if ($newCandidature !== $sessionUserData->getCandidature()) {
            $sessionUserData->setCandidature($newCandidature);
        }

        return $this;
    }

    public function getPreparcoursCandidate(): ?PreparcoursCandidate
    {
        return $this->preparcoursCandidate;
    }

    public function setPreparcoursCandidate(?PreparcoursCandidate $preparcoursCandidate): self
    {
        $this->preparcoursCandidate = $preparcoursCandidate;

        // set (or unset) the owning side of the relation if necessary
        $newCandidature = $preparcoursCandidate === null ? null : $this;
        if ($newCandidature !== $preparcoursCandidate->getCandidature()) {
            $preparcoursCandidate->setCandidature($newCandidature);
        }

        return $this;
    }

}

<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CorrectionRepository")
 */
class Correction
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     */
    private $corrector;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     */
    private $corrected;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\SessionDayCourse")
     */
    private $day;


    /**
     * @ORM\Column(type="text", nullable=true)
     * @Assert\NotBlank(message="Veuillez fournir un commentaire")
     */
    private $comment;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CorrectionResult", mappedBy="correction",cascade={"remove"})
     */
    private $correctionResults;

    public function __construct()
    {
        $this->correctionResults = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCorrector(): ?User
    {
        return $this->corrector;
    }

    public function setCorrector(?User $corrector): self
    {
        $this->corrector = $corrector;

        return $this;
    }

    public function getCorrected(): ?User
    {
        return $this->corrected;
    }

    public function setCorrected(?User $corrected): self
    {
        $this->corrected = $corrected;

        return $this;
    }

    public function getDay(): ?SessionDayCourse
    {
        return $this->day;
    }

    public function setDay(?SessionDayCourse $day): self
    {
        $this->day = $day;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @return Collection|CorrectionResult[]
     */
    public function getCorrectionResults(): Collection
    {
        return $this->correctionResults;
    }

    public function addCorrectionResult(CorrectionResult $correctionResult): self
    {
        if (!$this->correctionResults->contains($correctionResult)) {
            $this->correctionResults[] = $correctionResult;
            $correctionResult->setCorrection($this);
        }

        return $this;
    }

    public function removeCorrectionResult(CorrectionResult $correctionResult): self
    {
        if ($this->correctionResults->contains($correctionResult)) {
            $this->correctionResults->removeElement($correctionResult);
            // set the owning side to null (unless already changed)
            if ($correctionResult->getCorrection() === $this) {
                $correctionResult->setCorrection(null);
            }
        }

        return $this;
    }
}

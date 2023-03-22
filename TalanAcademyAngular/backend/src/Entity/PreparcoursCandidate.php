<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PreparcoursCandidateRepository")
 */
class PreparcoursCandidate
{
    const EN_COURS = 'en cours';
    const SOUMIS = 'soumis';
    const DEBORDE = 'deborde';
    const VALIDATED = 'validated';
    const REJECTED = 'rejected';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Serializer\Groups({"preparcoursCandidate"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Serializer\Groups({"preparcoursCandidate"})
     */
    private $preparcoursPdf;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Serializer\Groups({"preparcoursCandidate"})
     */
    private $status;

    /**
     * @ORM\Column(type="datetime")
     * @Serializer\Groups({"preparcoursCandidate"})
     */
    private $startDate;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $repoGit;



    /**
     * @ORM\ManyToOne(targetEntity="Preparcours",inversedBy="preparcoursCandidate")
     * @ORM\JoinColumn(nullable=false)
     */
    private $preparcours;

    /**
     * @ORM\ManyToOne(targetEntity="User",inversedBy="Candidatepreparcours")
     * @ORM\JoinColumn(nullable=false)
     */
    private $candidate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $submissionDate;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $decision;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Candidature", inversedBy="preparcoursCandidate", cascade={"persist", "remove"})
     */
    private $candidature;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPreparcours(): ?Preparcours
    {
        return $this->preparcours;
    }

    public function setPreparcours(?Preparcours $preparcours): self
    {
        $this->preparcours = $preparcours;

        return $this;
    }

    public function getCandidate(): ?User
    {
        return $this->candidate;
    }

    public function setCandidate(?User $candidate): self
    {
        $this->candidate = $candidate;

        return $this;
    }


    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

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

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getPreparcoursPdf(): ?string
    {
        return $this->preparcoursPdf;
    }

    public function setPreparcoursPdf(string $preparcoursPdf): self
    {
        $this->preparcoursPdf = $preparcoursPdf;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRepoGit()
    {
        return $this->repoGit;
    }

    /**
     * @param mixed $repoGit
     */
    public function setRepoGit($repoGit): void
    {
        $this->repoGit = $repoGit;
    }

    public function getSubmissionDate(): ?\DateTimeInterface
    {
        return $this->submissionDate;
    }

    public function setSubmissionDate(?\DateTimeInterface $submissionDate): self
    {
        $this->submissionDate = $submissionDate;

        return $this;
    }

    public function getDecision(): ?string
    {
        return $this->decision;
    }

    public function setDecision(?string $decision): self
    {
        $this->decision = $decision;

        return $this;
    }

    public function getCandidature(): ?Candidature
    {
        return $this->candidature;
    }

    public function setCandidature(?Candidature $candidature): self
    {
        $this->candidature = $candidature;

        return $this;
    }

}

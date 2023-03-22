<?php

namespace App\Entity;

use  Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PreparcoursRepository")
 */
class Preparcours
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateCreation;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isActivated;


    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Serializer\Groups({"preparcours"})
     */
    private $pdf;


    /**
     * @ORM\OneToMany(targetEntity="PreparcoursCandidate", mappedBy="preparcours")
     */
    private $preparcoursCandidate;

    public function __construct()
    {
        $this->preparcoursCandidate = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getIsActivated()
    {
        return $this->isActivated;
    }

    /**
     * @param mixed $isActivated
     */
    public function setIsActivated($isActivated): void
    {
        $this->isActivated = $isActivated;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDateCreation()
    {
        return $this->dateCreation;
    }

    /**
     * @param mixed $dateCreation
     */
    public function setDateCreation($dateCreation): void
    {
        $this->dateCreation = $dateCreation;
    }

    /**
     * @return mixed
     */
    public function getPdf()
    {
        return $this->pdf;
    }

    /**
     * @param mixed $pdf
     */
    public function setPdf($pdf): void
    {
        $this->pdf = $pdf;
    }

    /**
     * @return Collection|PreparcoursCandidate[]
     */
    public function getPreparcoursCandidate(): Collection
    {
        return $this->preparcoursCandidate;
    }

    public function addPreparcoursCandidate(PreparcoursCandidate $preparcoursCandidate): self
    {
        if (!$this->preparcoursCandidate->contains($preparcoursCandidate)) {
            $this->preparcoursCandidate[] = $preparcoursCandidate;
            $preparcoursCandidate->setPreparcours($this);
        }

        return $this;
    }

    public function removePreparcoursCandidate(PreparcoursCandidate $preparcoursCandidate): self
    {
        if ($this->preparcoursCandidate->contains($preparcoursCandidate)) {
            $this->preparcoursCandidate->removeElement($preparcoursCandidate);
            // set the owning side to null (unless already changed)
            if ($preparcoursCandidate->getPreparcours() === $this) {
                $preparcoursCandidate->setPreparcours(null);
            }
        }

        return $this;
    }


}

<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ResourceRecommendationRepository")
 */
class ResourceRecommendation
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $score;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Student", inversedBy="resource")
     * @ORM\JoinColumn(nullable=false)
     */
    private $apprentice;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\SessionResources", inversedBy="resourceRecommendations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $resource;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(?int $score): self
    {
        $this->score = $score;

        return $this;
    }

    public function getApprentice(): ?Student
    {
        return $this->apprentice;
    }

    public function setApprentice(?Student $apprentice): self
    {
        $this->apprentice = $apprentice;

        return $this;
    }

    public function getResource(): ?SessionResources
    {
        return $this->resource;
    }

    public function setResource(?SessionResources $resource): self
    {
        $this->resource = $resource;

        return $this;
    }
}

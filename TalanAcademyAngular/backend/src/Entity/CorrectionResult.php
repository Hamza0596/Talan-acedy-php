<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CorrectionResultRepository")
 */
class CorrectionResult
{

    const TRUE = 't';
    const FALSE = 'f';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Correction", inversedBy="correctionResults")
     */
    private $correction;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\SessionOrder", inversedBy="correctionResults")
     */
    private $orderCourse;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $result;

    public function __construct()
    {
        $this->result = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCorrection(): ?Correction
    {
        return $this->correction;
    }

    public function setCorrection(?Correction $correction): self
    {
        $this->correction = $correction;

        return $this;
    }

    public function getOrderCourse(): ?SessionOrder
    {
        return $this->orderCourse;
    }

    public function setOrderCourse(?SessionOrder $orderCourse): self
    {
        $this->orderCourse = $orderCourse;

        return $this;
    }

    public function getResult(): ?string
    {
        return $this->result;
    }

    public function setResult(string $result): self
    {
        $this->result = $result;

        return $this;
    }
}

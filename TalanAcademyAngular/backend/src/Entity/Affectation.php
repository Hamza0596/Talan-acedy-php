<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AffectationRepository")
 */
class Affectation
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\SessionProjectSubject", inversedBy="affectations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $subject;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Student", inversedBy="affectations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $student;



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSubject(): ?SessionProjectSubject
    {
        return $this->subject;
    }

    public function setSubject(?SessionProjectSubject $subject): self
    {
        $this->subject = $subject;

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

<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StudentReviewRepository")
 */
class StudentReview
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\SessionDayCourse", inversedBy="apprentices")
     */
    private $course;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Student", inversedBy="studentReviews")
     */
    private $student;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $rating;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCourse(): ?SessionDayCourse
    {
        return $this->course;
    }

    public function setCourse(?SessionDayCourse $course): self
    {
        $this->course = $course;

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

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getRating(): ?int
    {
        return $this->rating;
    }

    public function setRating(?int $rating): self
    {
        $this->rating = $rating;

        return $this;
    }

}

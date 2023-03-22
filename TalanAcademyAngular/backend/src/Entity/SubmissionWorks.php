<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SubmissionWorksRepository")
 */
class SubmissionWorks
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\SessionDayCourse", inversedBy="submissionWorks")
     */
    private $course;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Student", inversedBy="submissionWorks")
     */
    private $student;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Url(
     *    relativeProtocol = true
     *     )
     */
    private $repoLink;

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

    public function getRepoLink(): ?string
    {
        return $this->repoLink;
    }

    public function setRepoLink(string $repoLink): self
    {
        if (base64_encode(base64_decode($repoLink, true)) === $repoLink) {
            $this->repoLink = base64_decode($repoLink);
        } else {
            $this->repoLink = $repoLink;
        }

        return $this;
    }
}

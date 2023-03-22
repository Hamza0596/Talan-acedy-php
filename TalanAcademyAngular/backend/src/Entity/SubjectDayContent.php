<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SubjectDayContentRepository")
 */
class SubjectDayContent
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\SessionProjectSubject", inversedBy="subjectDayContents")
     */
    private $subject;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\SessionDayCourse", inversedBy="subjectDayContents")
     */
    private $sessionDay;

    /**
     * @Assert\NotBlank(
     *     message="Vous devez entrer un contenu"
     * )
     * @ORM\Column(type="text", nullable=true)
     */
    private $content;

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

    public function getSessionDay(): ?SessionDayCourse
    {
        return $this->sessionDay;
    }

    public function setSessionDay(?SessionDayCourse $sessionDay): self
    {
        $this->sessionDay = $sessionDay;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }



}

<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ActivityCoursesRepository")
 */
class ActivityCourses implements ActivityInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     * @Assert\NotBlank(
     *     message="Le titre ne peut pas être null !!!"
     * )
     *
     * @Assert\Length(
     *      min = 3,
     *      max = 40,
     *      minMessage = "Le titre doit comporter au moins {{ limit }} caractères.",
     *      maxMessage = "Le titre ne peut pas contenir plus de {{ limit }} caractères"
     * )
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank
     */
    private $content;
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $reference;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\DayCourse", inversedBy="activityCursuses")
     * @ORM\JoinColumn(nullable=false)
     */
    private $day;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $deleted;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return ActivityCourses
     */
    public function setId($id): self
    {
        $this->id = $id;
        return $this;
    }


    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title = null): self
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getReference(): ?string
    {
        return $this->reference;
    }


    public function setReference(string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    public function getDay(): ?DayCourse
    {
        return $this->day;
    }

    public function setDay(?DayInterface  $day): self
    {
        $this->day = $day;

        return $this;
    }

    public function serializer()
    {
        return [
            'ref' => $this->getReference(),
            'title' => $this->getTitle(),
            'content' => $this->getContent(),
        ];
    }
    public function toArray()
    {
        return [
            'reference' => $this->getReference(),
            'title' => $this->getTitle(),
            'content' => $this->getContent(),
        ];
    }

    public function getDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeleted(?bool $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }

}

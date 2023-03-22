<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SessionModuleRepository")
 */
class SessionModule implements ModuleInterface
{
    const MODULE="MODULE";
    const PROJECT ="PROJECT";
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Serializer\Groups({"current_session"})
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    private $ref;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Session", inversedBy="modules")
     */
    private $session;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     */
    private $description;

    /**
     * @ORM\Column(type="integer")
     */
    private $orderModule;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SessionDayCourse", mappedBy="module",cascade={"remove"})

     */
    private $DayCourses;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $type;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $duration;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SessionProjectSubject", mappedBy="SessionProject",cascade={"remove"})
     */
    private $sessionProjectSubjects;

    public function __construct($copy = null)
    {
        if ($copy) {
            $this->setTitle($copy['title']);
            $this->setRef($copy['ref']);
            $this->setDescription($copy['description']);
            $this->setOrderModule($copy['order']);
            $this->setDuration($copy['duration']);
            $this->setType($copy['type']);
        }
        $this->DayCourses = new ArrayCollection();
        $this->sessionProjectSubjects = new ArrayCollection();

    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getRef(): ?string
    {
        return $this->ref;
    }

    public function setRef(string $ref): self
    {
        $this->ref = $ref;

        return $this;
    }

    public function getSession(): ?Session
    {
        return $this->session;
    }

    public function setSession(?Session $session): self
    {
        $this->session = $session;

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


    public function getOrderModule(): ?int
    {
        return $this->orderModule;
    }

    public function setOrderModule(int $orderModule): self
    {
        $this->orderModule = $orderModule;

        return $this;
    }

    /**
     * @return Collection|DayCourse[]
     */
    public function getDayCourses(): Collection
    {
        return $this->DayCourses;
    }

    public function addDayCourse(DayInterface $DayCourse): self
    {
        if (!$this->DayCourses->contains($DayCourse)) {
            $this->DayCourses[] = $DayCourse;
            $DayCourse->setModule($this);
        }

        return $this;
    }

    public function removeDayCourse(DayInterface $DayCourse): self
    {
        if ($this->DayCourses->contains($DayCourse)) {
            $this->DayCourses->removeElement($DayCourse);
            // set the owning side to null (unless already changed)
            if ($DayCourse->getModule() === $this) {
                $DayCourse->setModule(null);
            }
        }

        return $this;
    }
    public function serializer()
    {
        return [
            'ref' => $this->getRef(),
            'title' => $this->getTitle(),
            'description' => $this->getDescription(),
            'order' => $this->getOrderModule(),
        ];
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    /**
     * @return Collection|SessionProjectSubject[]
     */
    public function getSessionProjectSubjects(): Collection
    {
        return $this->sessionProjectSubjects;
    }

    public function addSessionProjectSubject(SessionProjectSubject $sessionProjectSubject): self
    {
        if (!$this->sessionProjectSubjects->contains($sessionProjectSubject)) {
            $this->sessionProjectSubjects[] = $sessionProjectSubject;
            $sessionProjectSubject->setSessionProject($this);
        }

        return $this;
    }

    public function removeSessionProjectSubject(SessionProjectSubject $sessionProjectSubject): self
    {
        if ($this->sessionProjectSubjects->contains($sessionProjectSubject)) {
            $this->sessionProjectSubjects->removeElement($sessionProjectSubject);
            // set the owning side to null (unless already changed)
            if ($sessionProjectSubject->getSessionProject() === $this) {
                $sessionProjectSubject->setSessionProject(null);
            }
        }

        return $this;
    }

}

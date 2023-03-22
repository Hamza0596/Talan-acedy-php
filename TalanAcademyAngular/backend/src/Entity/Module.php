<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ModuleRepository")
 */
class Module implements ModuleInterface
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
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    private $ref;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Cursus", inversedBy="modules")
     */
    private $courses;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     */
    private $description;
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\DayCourse", mappedBy="module", cascade={"remove"})
     */
    private $dayCourses;

    /**
     * @ORM\Column(type="integer")
     */
    private $orderModule;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $type;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Type(type="integer")
     */
    private $duration;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ProjectSubject", mappedBy="project",cascade={"remove"})
     */
    private $projectSubjects;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $deleted;

    public function serializer()
    {
        return [
            'ref' => $this->getRef(),
            'title' => $this->getTitle(),
            'description' => $this->getDescription(),
            'order' => $this->getOrderModule(),
            'duration'=>$this->getDuration(),
            'type'=>$this->getType()
        ];
    }

    public function __construct()
    {
        $this->dayCourses = new ArrayCollection();
        $this->projectSubjects = new ArrayCollection();
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

    public function getCourses(): ?Cursus
    {
        return $this->courses;
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

    /**
     * @return Collection|DayCourse[]
     */
    public function getDayCourses(): Collection
    {
        return $this->dayCourses;
    }

    public function addDayCourse(DayInterface $dayCourse): self
    {
        if (!$this->dayCourses->contains($dayCourse)) {
            $this->dayCourses[] = $dayCourse;
            $dayCourse->setModule($this);
        }

        return $this;
    }

    public function removeDayCourse(DayInterface $dayCourse): self
    {
        if ($this->dayCourses->contains($dayCourse)) {
            $this->dayCourses->removeElement($dayCourse);
            // set the owning side to null (unless already changed)
            if ($dayCourse->getModule() === $this) {
                $dayCourse->setModule(null);
            }
        }

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

    public function setCourses(?Cursus $courses): self
    {
        $this->courses = $courses;

        return $this;
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
     * @return Collection|ProjectSubject[]
     */
    public function getProjectSubjects(): Collection
    {
        return $this->projectSubjects;
    }

    public function addProjectSubject(ProjectSubject $projectSubject): self
    {
        if (!$this->projectSubjects->contains($projectSubject)) {
            $this->projectSubjects[] = $projectSubject;
            $projectSubject->setProject($this);
        }

        return $this;
    }

    public function removeProjectSubject(ProjectSubject $projectSubject): self
    {
        if ($this->projectSubjects->contains($projectSubject)) {
            $this->projectSubjects->removeElement($projectSubject);
            // set the owning side to null (unless already changed)
            if ($projectSubject->getProject() === $this) {
                $projectSubject->setProject(null);
            }
        }

        return $this;
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

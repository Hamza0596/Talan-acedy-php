<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
/**
 * @ORM\Entity(repositoryClass="App\Repository\SessionDayCourseRepository")
 */
class SessionDayCourse implements DayInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @ParamConverter("sessionDayCourse",options={"id"="dayId"})
     */
    private $id;
    /**
     * @ORM\Column(type="integer", length=255)
     */
    private $ordre;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Serializer\Groups({"current_session"})
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255)
     * @Serializer\Groups({"current_session"})
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $reference;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SessionResources", mappedBy="day", orphanRemoval=true)
     */
    private $Resources;

    /**
     * @ORM\OneToMany(targetEntity="SessionActivityCourses", mappedBy="day", orphanRemoval=true)
     * @Serializer\Groups({"current_session"})
     */
    private $ActivityCourses;


    /**
     * @ORM\Column(type="text",nullable=true)
     * @Serializer\Groups({"current_session"})
     *
     */
    private $synopsis;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\SessionModule", inversedBy="DayCourses")
     */
    private $module;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SessionOrder", mappedBy="dayCourse",orphanRemoval=true)
     */
    private $orders;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\StudentReview", mappedBy="course",cascade={"remove"})
     */
    private $apprentices;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SubmissionWorks", mappedBy="course",cascade={"remove"})
     */
    private $submissionWorks;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SubjectDayContent", mappedBy="sessionDay",cascade={"remove"})
     */
    private $subjectDayContents;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateDay;


    public function __construct($copy = null)
    {
        if ($copy) {
            $this->setReference($copy['ref']);
            $this->setOrdre($copy['order']);
            $this->setDescription($copy['description']);
            $this->setStatus($copy['status']);
            $this->setSynopsis($copy['synopsis']);
        }
        $this->Resources = new ArrayCollection();
        $this->ActivityCourses = new ArrayCollection();
        $this->orders = new ArrayCollection();
        $this->apprentices = new ArrayCollection();
        $this->submissionWorks = new ArrayCollection();
        $this->subjectDayContents = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection|SessionResources[]
     */
    public function getResources(): Collection
    {
        return $this->Resources;
    }

    public function addResource(ResourcesInterface $Resources): self
    {
        if (!$this->Resources->contains($Resources)) {
            $this->Resources[] = $Resources;
            $Resources->setDay($this);
        }

        return $this;
    }

    public function removeResource(ResourcesInterface $Resources): self
    {
        if ($this->Resources->contains($Resources)) {
            $this->Resources->removeElement($Resources);
            // set the owning side to null (unless already changed)
            if ($Resources->getDay() === $this) {
                $Resources->setDay(null);
            }
        }

        return $this;
    }

    public function addActivityCourses(ActivityInterface $ActivityCourses): self
    {
        if (!$this->ActivityCourses->contains($ActivityCourses)) {
            $this->ActivityCourses[] = $ActivityCourses;
            $ActivityCourses->setDay($this);
        }

        return $this;
    }

    public function removeActivityCourses(ActivityInterface $ActivityCourses): self
    {
        if ($this->ActivityCourses->contains($ActivityCourses)) {
            $this->ActivityCourses->removeElement($ActivityCourses);
            // set the owning side to null (unless already changed)
            if ($ActivityCourses->getDay() === $this) {
                $ActivityCourses->setDay(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|SessionActivityCourses[]
     */
    public function getActivityCourses(): Collection
    {
        return $this->ActivityCourses;
    }

    public function getModule(): ?ModuleInterface
    {
        return $this->module;
    }

    public function setModule(?ModuleInterface $Module): self
    {
        $this->module = $Module;

        return $this;
    }

    /**
     * @return Collection|SessionOrder[]
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(InstructionInterface $order): self
    {
        if (!$this->orders->contains($order)) {
            $this->orders[] = $order;
            $order->setDayCourse($this);
        }

        return $this;
    }

    public function removeOrder(InstructionInterface $order): self
    {
        if ($this->orders->contains($order)) {
            $this->orders->removeElement($order);
            // set the owning side to null (unless already changed)
            if ($order->getDayCourse() === $this) {
                $order->setDayCourse(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|StudentReview[]
     */
    public function getApprentices(): Collection
    {
        return $this->apprentices;
    }

    public function addApprentice(StudentReview $apprentice): self
    {
        if (!$this->apprentices->contains($apprentice)) {
            $this->apprentices[] = $apprentice;
            $apprentice->setCourse($this);
        }

        return $this;
    }

    public function removeApprentice(StudentReview $apprentice): self
    {
        if ($this->apprentices->contains($apprentice)) {
            $this->apprentices->removeElement($apprentice);
            // set the owning side to null (unless already changed)
            if ($apprentice->getCourse() === $this) {
                $apprentice->setCourse(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|SubmissionWorks[]
     */
    public function getSubmissionWorks(): Collection
    {
        return $this->submissionWorks;
    }

    public function addSubmissionWork(SubmissionWorks $submissionWork): self
    {
        if (!$this->submissionWorks->contains($submissionWork)) {
            $this->submissionWorks[] = $submissionWork;
            $submissionWork->setCourse($this);
        }

        return $this;
    }

    public function removeSubmissionWork(SubmissionWorks $submissionWork): self
    {
        if ($this->submissionWorks->contains($submissionWork)) {
            $this->submissionWorks->removeElement($submissionWork);
            // set the owning side to null (unless already changed)
            if ($submissionWork->getCourse() === $this) {
                $submissionWork->setCourse(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|SubjectDayContent[]
     */
    public function getSubjectDayContents(): Collection
    {
        return $this->subjectDayContents;
    }

    public function addSubjectDayContent(SubjectDayContent $subjectDayContent): self
    {
        if (!$this->subjectDayContents->contains($subjectDayContent)) {
            $this->subjectDayContents[] = $subjectDayContent;
            $subjectDayContent->setSessionDay($this);
        }

        return $this;
    }

    public function removeSubjectDayContent(SubjectDayContent $subjectDayContent): self
    {
        if ($this->subjectDayContents->contains($subjectDayContent)) {
            $this->subjectDayContents->removeElement($subjectDayContent);
            // set the owning side to null (unless already changed)
            if ($subjectDayContent->getSessionDay() === $this) {
                $subjectDayContent->setSessionDay(null);
            }
        }

        return $this;
    }

    public function getDateDay(): ?\DateTimeInterface
    {
        return $this->dateDay;
    }

    public function setDateDay(?\DateTimeInterface $dateDay): self
    {
        $this->dateDay = $dateDay;

        return $this;
    }

    public function toArray()
    {
        return [
            'reference' => $this->getReference(),
            'ordre' => $this->getOrdre(),
            'description' => $this->getDescription(),
            'status' => $this->getStatus(),
            'synopsis' => $this->getSynopsis(),
        ];
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getOrdre()
    {
        return $this->ordre;
    }

    /**
     * @param mixed $ordre
     */
    public function setOrdre($ordre): void
    {
        $this->ordre = $ordre;
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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getSynopsis(): ?string
    {
        return $this->synopsis;
    }

    public function setSynopsis(?string $synopsis): self
    {
        $this->synopsis = $synopsis;

        return $this;
    }
}


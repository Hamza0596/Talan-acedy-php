<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DayCourseRepository")
 *
 */
class DayCourse implements DayInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @Serializer\Groups({"day"})
     * @ORM\Column(type="integer")
     * @Serializer\Groups({"course"})
     */
    private $id;

    /**
     * @ORM\Column(type="integer", length=255)
     */
    private $ordre;

    /**
     * @ORM\Column(type="string", length=255)
     * @Serializer\Groups({"day"})
     * @Assert\NotBlank(
     *     message="L'intitulé ne peut pas être null !!!"
     * )
     *
     * @Assert\Length(
     *      min = 3,
     *      max = 40,
     *      minMessage = "L'intitulé doit comporter au moins {{ limit }} caractères.",
     *      maxMessage = "L'intitulé ne peut pas contenir plus de {{ limit }} caractères")
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $reference;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Module", inversedBy="dayCourses")
     * @ORM\JoinColumn(nullable=true)
     * @Serializer\Groups({"course"})
     */
    private $module;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Resources", mappedBy="day", orphanRemoval=true)
     */
    private $resources;

    /**
     * @ORM\OneToMany(targetEntity="ActivityCourses", mappedBy="day", orphanRemoval=true)
     */
    private $activityCourses;

    /**
     * @ORM\Column(type="text",nullable=true)
     */
    private $synopsis;

    /**
     * @ORM\OneToMany(targetEntity="OrderCourse", mappedBy="dayCourse",orphanRemoval=true)
     */
    private $orders;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $deleted;

    public function serializer()
    {
        return [
            'ref' => $this->getReference(),
            'order' => $this->getOrdre(),
            'description' => $this->getDescription(),
            'status' => $this->getStatus(),
            'synopsis' => $this->getSynopsis(),
        ];
    }

    public function __construct()
    {
        $this->resources = new ArrayCollection();
        $this->activityCourses = new ArrayCollection();
        $this->orders = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    public function getModule(): ?ModuleInterface
    {
        return $this->module;
    }

    public function setModule(?ModuleInterface $module): self
    {
        $this->module = $module;

        return $this;
    }

    /**
     * @return Collection|Resources[]
     */
    public function getResources(): Collection
    {
        return $this->resources;
    }

    public function addResource(ResourcesInterface $resource): self
    {
        if (!$this->resources->contains($resource)) {
            $this->resources[] = $resource;
            $resource->setDay($this);
        }

        return $this;
    }

    public function removeResource(ResourcesInterface $resource): self
    {
        if ($this->resources->contains($resource)) {
            $this->resources->removeElement($resource);
            // set the owning side to null (unless already changed)
            if ($resource->getDay() === $this) {
                $resource->setDay(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ActivityCourses[]
     */
    public function getActivityCourses(): Collection
    {
        return $this->activityCourses;
    }

    public function addActivityCourses(ActivityInterface $activityCursus): self
    {
        if (!$this->activityCourses->contains($activityCursus)) {
            $this->activityCourses[] = $activityCursus;
            $activityCursus->setDay($this);
        }

        return $this;
    }

    public function removeActivityCourses(ActivityInterface $activityCursus): self
    {
        if ($this->activityCourses->contains($activityCursus)) {
            $this->activityCourses->removeElement($activityCursus);
            // set the owning side to null (unless already changed)
            if ($activityCursus->getDay() === $this) {
                $activityCursus->setDay(null);
            }
        }

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

    /**
     * @return Collection|OrderCourse[]
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

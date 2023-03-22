<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CursusRepository")
 */
class Cursus implements CurriculumInterface
{
    const INVISIBLE = 'invisible';
    const VISIBLE = 'visible';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Serializer\Groups({"cursus"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     * @Serializer\Groups({"cursus","candidature"})
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     * @Serializer\Groups({"cursus"})
     */
    private $visibility = self::INVISIBLE;

    /**
     * @ORM\Column(type="text")
     * @Serializer\Groups({"course","cursus"})
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Module", mappedBy="courses")

     */
    private $modules;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Image()
     * @Serializer\Groups({"cursus"})
     */
    private $image;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Candidature", mappedBy="cursus")
     */
    private $candidatures;


    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Staff", mappedBy="cursus")
     */
    private $staff;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Session", mappedBy="cursus")
     */
    private $sessions;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $daysNumber;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Serializer\Groups({"cursus"})
     */
    private $tags;

    public function __construct()
    {
        $this->modules = new ArrayCollection();
        $this->candidatures = new ArrayCollection();
        $this->staff = new ArrayCollection();
        $this->sessions = new ArrayCollection();
    }

    public function getTagsArray()
    {
        return explode(',',$this->tags);


    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getVisibility(): ?string
    {
        return $this->visibility;
    }

    public function setVisibility(string $visibility): self
    {
        $this->visibility = $visibility;

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

    /**
     * @return Collection|Module[]
     */
    public function getModules(): Collection
    {
        return $this->modules;
    }

    public function addModule(ModuleInterface $module): self
    {
        if (!$this->modules->contains($module)) {
            $this->modules[] = $module;
            $module->setCourses($this);
        }

        return $this;
    }

    public function removeModule(ModuleInterface $module): self
    {
        if ($this->modules->contains($module)) {
            $this->modules->removeElement($module);
            if ($module->getCourses() === $this) {
                $module->setCourses(null);
            }
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param mixed $tags
     */
    public function setTags($tags): void
    {
        $this->tags = $tags;
    }



    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return Collection|Staff[]
     */
    public function getStaff(): Collection
    {
        return $this->staff;
    }

    public function addStaff(Staff $staff): self
    {
        if (!$this->staff->contains($staff)) {
            $this->staff[] = $staff;
            $staff->setCursus($this);
        }

        return $this;
    }

    public function removeStaff(Staff $staff): self
    {
        if ($this->staff->contains($staff)) {
            $this->staff->removeElement($staff);
        }

        return $this;
    }

    /**
     * @return Collection|Candidature[]
     */
    public function getCandidatures(): Collection
    {
        return $this->candidatures;
    }

    public function addCandidature(Candidature $candidature): self
    {
        if (!$this->candidatures->contains($candidature)) {
            $this->candidatures[] = $candidature;
            $candidature->setCursus($this);
        }

        return $this;
    }

    public function removeCandidature(Candidature $candidature): self
    {
        if ($this->candidatures->contains($candidature)) {
            $this->candidatures->removeElement($candidature);
            // set the owning side to null (unless already changed)
            if ($candidature->getCursus() === $this) {
                $candidature->setCursus(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Session[]
     */
    public function getSessions(): Collection
    {
        return $this->sessions;
    }

    public function addSession(Session $session): self
    {
        if (!$this->sessions->contains($session)) {
            $this->sessions[] = $session;
            $session->setCursus($this);
        }

        return $this;
    }

    public function removeSession(Session $session): self
    {
        if ($this->sessions->contains($session)) {
            $this->sessions->removeElement($session);
            // set the owning side to null (unless already changed)
            if ($session->getCursus() === $this) {
                $session->setCursus(null);
            }
        }

        return $this;
    }

    public function getDaysNumber(): ?int
    {
        return $this->daysNumber;
    }

    public function setDaysNumber(?int $daysNumber): self
    {
        $this->daysNumber = $daysNumber;

        return $this;
    }



}

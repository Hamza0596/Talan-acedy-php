<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SessionProjectSubjectRepository")
 */
class SessionProjectSubject
{

    const ACTIVATED = "activated";
    const DEACTIVATED = "deactivated";
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
    private $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $specification;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\SessionModule", inversedBy="sessionProjectSubjects")
     */
    private $SessionProject;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $ref;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Affectation", mappedBy="subject",cascade={"remove"})
     */
    private $affectations;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SubjectDayContent", mappedBy="subject",cascade={"remove"})
     */
    private $subjectDayContents;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $status;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Staff", mappedBy="sujet")
     */
    private $mentor;

    /**
     * SessionProjectSubject constructor.
     * @param null $copy
     */
    public function __construct($copy=null)
    {
       if($copy){
           $this->setName($copy['name']);
           $this->setRef($copy['ref']);
           $this->setSpecification($copy['specification']);
       }
       $this->affectations = new ArrayCollection();
       $this->subjectDayContents = new ArrayCollection();
       $this->mentor = new ArrayCollection();

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

    public function getSpecification(): ?string
    {
        return $this->specification;
    }

    public function setSpecification(?string $specification): self
    {
        $this->specification = $specification;

        return $this;
    }

    public function getSessionProject(): ?SessionModule
    {
        return $this->SessionProject;
    }

    public function setSessionProject(?SessionModule $SessionProject): self
    {
        $this->SessionProject = $SessionProject;

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

    /**
     * @return Collection|Affectation[]
     */
    public function getAffectations(): Collection
    {
        return $this->affectations;
    }

    public function addAffectation(Affectation $affectation): self
    {
        if (!$this->affectations->contains($affectation)) {
            $this->affectations[] = $affectation;
            $affectation->setSubject($this);
        }

        return $this;
    }

    public function removeAffectation(Affectation $affectation): self
    {
        if ($this->affectations->contains($affectation)) {
            $this->affectations->removeElement($affectation);
            // set the owning side to null (unless already changed)
            if ($affectation->getSubject() === $this) {
                $affectation->setSubject(null);
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
            $subjectDayContent->setSubject($this);
        }

        return $this;
    }

    public function removeSubjectDayContent(SubjectDayContent $subjectDayContent): self
    {
        if ($this->subjectDayContents->contains($subjectDayContent)) {
            $this->subjectDayContents->removeElement($subjectDayContent);
            // set the owning side to null (unless already changed)
            if ($subjectDayContent->getSubject() === $this) {
                $subjectDayContent->setSubject(null);
            }
        }

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection|Staff[]
     */
    public function getMentor(): Collection
    {
        return $this->mentor;
    }

    public function addMentor(Staff $mentor): self
    {
        if (!$this->mentor->contains($mentor)) {
            $this->mentor[] = $mentor;
            $mentor->addSujet($this);
        }

        return $this;
    }

    public function removeMentor(Staff $mentor): self
    {
        if ($this->mentor->contains($mentor)) {
            $this->mentor->removeElement($mentor);
            $mentor->removeSujet($this);
        }

        return $this;
    }
    public function serializer()
    {
        return [
            'name' => $this->getName(),
            'specification' => $this->getSpecification(),
            'ref' => $this->getRef(),
        ];
    }

}

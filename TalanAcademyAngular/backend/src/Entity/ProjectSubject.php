<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProjectSubjectRepository")
 */
class ProjectSubject implements ProjectSubjectInterface
{
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Module", inversedBy="projectSubjects", cascade={"persist"})
     */
    private $project;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $ref;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $deleted;

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

    public function getProject(): ?Module
    {
        return $this->project;
    }

    public function setProject(?ModuleInterface $project): self
    {
        $this->project = $project;

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
    public function serializer()
    {
        return [
            'name' => $this->getName(),
            'specification' => $this->getSpecification(),
            'ref' => $this->getRef(),
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

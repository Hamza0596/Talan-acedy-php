<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SessionOrderRepository")
 */
class SessionOrder implements InstructionInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank(
     *     message="La description ne peut pas être null !!!"
     * )
     *
     * @Assert\Length(
     *      min = 3,
     *      max = 40,
     *      minMessage = "La description doit comporter au moins {{ limit }} caractères.",
     *      maxMessage = "La description ne peut pas contenir plus de {{ limit }} caractères"
     * )
     */
    private $description;

    /**
     * @ORM\Column(type="string")
     * @Assert\Type(
     *     type="numeric",
     *     message="La valeur {{ value }} n'est pas un {{ type }} valide."
     * )
     */
    private $scale;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $ref;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\SessionDayCourse", inversedBy="orders")
     */
    private $dayCourse;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CorrectionResult", mappedBy="orderCourse",cascade={"remove"})
     */
    private $correctionResults;


    public function __construct($copy = null)
    {
        if ($copy) {
            $this->setRef($copy['ref']);
            $this->setDescription($copy['description']);
            $this->setScale($copy['scale']);
        }
        $this->correctionResults = new ArrayCollection();

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

    public function getScale(): ?int
    {
        return $this->scale;
    }

    public function setScale(int $scale): self
    {
        $this->scale = $scale;

        return $this;
    }


    public function getRef(): ?string
    {
        return $this->ref;
    }

    public function setRef(?string $ref): self
    {
        $this->ref = $ref;

        return $this;
    }

    public function getDayCourse(): ?DayInterface
    {
        return $this->dayCourse;
    }

    public function setDayCourse(?DayInterface $DayCourse): self
    {
        $this->dayCourse = $DayCourse;

        return $this;
    }

    /**
     * @return Collection|CorrectionResult[]
     */
    public function getCorrectionResults(): Collection
    {
        return $this->correctionResults;
    }

    public function addCorrectionResult(CorrectionResult $correctionResult): self
    {
        if (!$this->correctionResults->contains($correctionResult)) {
            $this->correctionResults[] = $correctionResult;
            $correctionResult->setOrderCourse($this);
        }

        return $this;
    }

    public function removeCorrectionResult(CorrectionResult $correctionResult): self
    {
        if ($this->correctionResults->contains($correctionResult)) {
            $this->correctionResults->removeElement($correctionResult);
            // set the owning side to null (unless already changed)
            if ($correctionResult->getOrderCourse() === $this) {
                $correctionResult->setOrderCourse(null);
            }
        }

        return $this;
    }

    public function serializer()
    {
        return [
            'description' => $this->getDescription(),
            'scale' => $this->getScale(),
            'ref' => $this->getRef()
        ];
    }
}

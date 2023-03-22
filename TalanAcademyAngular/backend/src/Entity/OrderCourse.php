<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\OrderCourseRepository")
 */
class OrderCourse implements InstructionInterface
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
     *      max = 500,
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
     * @ORM\ManyToOne(targetEntity="App\Entity\DayCourse", inversedBy="orders")
     */
    private $dayCourse;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
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

    public function getDayCourse(): ?DayCourse
    {
        return $this->dayCourse;
    }

    public function setDayCourse(?DayInterface $dayCourse): self
    {
        $this->dayCourse = $dayCourse;

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

    public function getRef(): ?string
    {
        return $this->ref;
    }

    public function setRef(?string $ref): self
    {
        $this->ref = $ref;

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

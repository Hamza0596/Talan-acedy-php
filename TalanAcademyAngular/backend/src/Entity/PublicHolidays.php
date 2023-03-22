<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PublicHolidaysRepository")
 */
class PublicHolidays
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $label;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $date;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\YearPublicHolidays", mappedBy="holidays")
     */
    private $yearPublicHolidays;

    public function __construct()
    {
        $this->yearPublicHolidays = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }


    /**
     * @return Collection|YearPublicHolidays[]
     */
    public function getYearPublicHolidays(): Collection
    {
        return $this->yearPublicHolidays;
    }

    public function addYearPublicHoliday(YearPublicHolidays $yearPublicHoliday): self
    {
        if (!$this->yearPublicHolidays->contains($yearPublicHoliday)) {
            $this->yearPublicHolidays[] = $yearPublicHoliday;
            $yearPublicHoliday->setHolidays($this);
        }

        return $this;
    }

    public function removeYearPublicHoliday(YearPublicHolidays $yearPublicHoliday): self
    {
        if ($this->yearPublicHolidays->contains($yearPublicHoliday)) {
            $this->yearPublicHolidays->removeElement($yearPublicHoliday);
            // set the owning side to null (unless already changed)
            if ($yearPublicHoliday->getHolidays() === $this) {
                $yearPublicHoliday->setHolidays(null);
            }
        }

        return $this;
    }

    public function getDate(): ?string
    {
        return $this->date;
    }

    public function setDate(?string $date): self
    {
        $this->date = $date;

        return $this;
    }
}

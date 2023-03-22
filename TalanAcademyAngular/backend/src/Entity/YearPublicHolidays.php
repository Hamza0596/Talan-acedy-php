<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\YearPublicHolidaysRepository")
 */
class YearPublicHolidays
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="date")
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\PublicHolidays", inversedBy="yearPublicHolidays", cascade={"persist"})
     */
    private $holidays;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getHolidays(): ?PublicHolidays
    {
        return $this->holidays;
    }

    public function setHolidays(?PublicHolidays $holidays): self
    {
        $this->holidays = $holidays;

        return $this;
    }
}

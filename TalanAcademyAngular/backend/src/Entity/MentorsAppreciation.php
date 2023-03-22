<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MentorsAppreciationRepository")
 */
class MentorsAppreciation
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Staff", inversedBy="mentorsAppreciations")
     */
    private $staff;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\SessionUserData", inversedBy="mentorsAppreciations")
     */
    private $sessionUser;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $createdBy;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $announcedBy;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStaff(): ?Staff
    {
        return $this->staff;
    }

    public function setStaff(?Staff $staff): self
    {
        $this->staff = $staff;

        return $this;
    }

    public function getSessionUser(): ?SessionUserData
    {
        return $this->sessionUser;
    }

    public function setSessionUser(?SessionUserData $sessionUser): self
    {
        $this->sessionUser = $sessionUser;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCreatedBy(): ?string
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?string $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getAnnouncedBy(): ?string
    {
        return $this->announcedBy;
    }

    public function setAnnouncedBy(?string $announcedBy): self
    {
        $this->announcedBy = $announcedBy;

        return $this;
    }
}

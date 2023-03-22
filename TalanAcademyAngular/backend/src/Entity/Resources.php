<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass="App\Repository\ResourcesRepository")
 */
class Resources implements ResourcesInterface
{
    const APPROVED = "approved";
    const TOAPPROVE = "toApprove";
    const NOTAPPROVED = "notApproved";
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     * @Assert\Url(
     *    message = "L'URL '{{ value }}' n'est pas une URL valide",
     * )
     */
    private $url;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $ref;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(
     *      min = 3,
     *      max = 50,
     *      minMessage = "le titre doit comporter au moins {{ limit }} caractères",
     *      maxMessage = "le titre doit comporter au plus {{ limit }} caractères"
     * )
     */
    private $title;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\DayCourse", inversedBy="resources", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $day;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="resources")
     */
    private $resourceOwner;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $deleted;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        if (base64_encode(base64_decode($url, true)) === $url) {
            $this->url = base64_decode($url);
        } else {
            $this->url = $url;
        }

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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDay(): ?DayCourse
    {
        return $this->day;
    }

    public function setDay(?DayInterface $day): self
    {
        $this->day = $day;

        return $this;
    }

    public function serializer()
    {
        return [
            'ref' => $this->getRef(),
            'title' => $this->getTitle(),
            'url' => $this->getUrl(),
            'owner' => $this->getResourceOwner(),
            'comment' => $this->getComment(),
        ];
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

    public function getResourceOwner(): ?User
    {
        return $this->resourceOwner;
    }

    public function setResourceOwner(?User $resourceOwner): self
    {
        $this->resourceOwner = $resourceOwner;

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
    public function toArray()
    {
        return [
            'ref' => $this->getRef(),
            'title' => $this->getTitle(),
            'url' => $this->getUrl(),
            'comment' => $this->getComment(),
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

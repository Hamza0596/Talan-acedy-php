<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SessionResourcesRepository")
 */
class SessionResources implements ResourcesInterface
{
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
     *    message = "L'URL '{{ value }}' n'est pas une URL valide",)
     * @Serializer\Groups({"current_session"})
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
     *      maxMessage = "le titre doit comporter au plus {{ limit }} caractères")
     * @Serializer\Groups({"current_session"})
     */
    private $title;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\SessionDayCourse", inversedBy="sessionResources", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $day;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Serializer\Groups({"current_session"})
     */
    private $comment;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ResourceRecommendation", mappedBy="resource", orphanRemoval=true)
     */
    private $resourceRecommendations;

    public function __construct($copy = null)
    {
        if ($copy) {
            $this->setTitle($copy['title']);
            $this->setRef($copy['ref']);
            $this->setUrl($copy['url']);
            $this->setComment($copy['comment']);
        }
        $this->resourceRecommendations = new ArrayCollection();
    }

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


    public function getDay(): ?SessionDayCourse
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
        ];
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

    /**
     * @return Collection|ResourceRecommendation[]
     */
    public function getResourceRecommendations(): Collection
    {
        return $this->resourceRecommendations;
    }

    public function addResourceRecommendation(ResourceRecommendation $resourceRecommendation): self
    {
        if (!$this->resourceRecommendations->contains($resourceRecommendation)) {
            $this->resourceRecommendations[] = $resourceRecommendation;
            $resourceRecommendation->setResource($this);
        }

        return $this;
    }

    public function removeResourceRecommendation(ResourceRecommendation $resourceRecommendation): self
    {
        if ($this->resourceRecommendations->contains($resourceRecommendation)) {
            $this->resourceRecommendations->removeElement($resourceRecommendation);
            // set the owning side to null (unless already changed)
            if ($resourceRecommendation->getResource() === $this) {
                $resourceRecommendation->setResource(null);
            }
        }

        return $this;
    }
}

<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass="App\Repository\SessionUserDataRepository")
 */
class SessionUserData
{
    const APPRENTI = 'apprenti';
    const ABANDONMENT = 'abandonment';
    const QUALIFIED = 'qualified';
    const ELIMINATED = 'eliminated';
    const CONFIRMED = 'confirmed';
    const NOTSELECTED = 'notSelected';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $nbrJoker;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Session", inversedBy="sessionUserDatas")
     */
    private $session;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="sessionUserDatas")
     */
    private $user;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Candidature", inversedBy="sessionUserData", cascade={"persist", "remove"})
     */
    private $candidature;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $status;


    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $repoGit;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank(
     *     message="Le pseudo slack ne peut pas être null !!!"
     * )
     *
     * @Assert\Length(
     *      min = 3,
     *      max = 40,
     *      minMessage = "Le pseudo slack doit comporter au moins {{ limit }} caractères.",
     *      maxMessage = "Le pseudo slack ne peut pas contenir plus de {{ limit }} caractères"
     * )
     */
    private $profilSlack;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $interactionSlack;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $mission;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\MentorsAppreciation", mappedBy="sessionUser")
     */
    private $mentorsAppreciations;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $subscriptionDate;


    public function __construct()
    {
        $this->mentorsAppreciations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNbrJoker(): ?int
    {
        return $this->nbrJoker;
    }

    public function setNbrJoker(?int $nbrJoker): self
    {
        $this->nbrJoker = $nbrJoker;

        return $this;
    }

    public function getSession(): ?Session
    {
        return $this->session;
    }

    public function setSession(?Session $session): self
    {
        $this->session = $session;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getCandidature(): ?Candidature
    {
        return $this->candidature;
    }

    public function setCandidature(?Candidature $candidature): self
    {
        $this->candidature = $candidature;

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

    public function getRepoGit(): ?string
    {
        return $this->repoGit;
    }

    public function setRepoGit(?string $repoGit): self
    {
        if (base64_encode(base64_decode($repoGit, true)) === $repoGit) {
            $this->repoGit = base64_decode($repoGit);
        } else {
            $this->repoGit = $repoGit;
        }

        return $this;
    }

    public function getProfilSlack(): ?string
    {
        return $this->profilSlack;
    }

    public function setProfilSlack(string $profilSlack): self
    {
        if (base64_encode(base64_decode($profilSlack, true)) === $profilSlack) {
            $this->profilSlack = base64_decode($profilSlack);
        } else {
            $this->profilSlack = $profilSlack;
        }

        return $this;
    }

    public function getInteractionSlack(): ?int
    {
        return $this->interactionSlack;
    }

    public function setInteractionSlack(?int $interactionSlack): self
    {
        $this->interactionSlack = $interactionSlack;

        return $this;
    }

    public function getMission(): ?bool
    {
        return $this->mission;
    }

    public function setMission(?bool $mission): self
    {
        $this->mission = $mission;

        return $this;
    }

    /**
     * @return Collection|MentorsAppreciation[]
     */
    public function getMentorsAppreciations(): Collection
    {
        return $this->mentorsAppreciations;
    }

    public function addMentorsAppreciation(MentorsAppreciation $mentorsAppreciation): self
    {
        if (!$this->mentorsAppreciations->contains($mentorsAppreciation)) {
            $this->mentorsAppreciations[] = $mentorsAppreciation;
            $mentorsAppreciation->setSessionUser($this);
        }

        return $this;
    }

    public function removeMentorsAppreciation(MentorsAppreciation $mentorsAppreciation): self
    {
        if ($this->mentorsAppreciations->contains($mentorsAppreciation)) {
            $this->mentorsAppreciations->removeElement($mentorsAppreciation);
            // set the owning side to null (unless already changed)
            if ($mentorsAppreciation->getSessionUser() === $this) {
                $mentorsAppreciation->setSessionUser(null);
            }
        }

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getSubscriptionDate(): ? \DateTime
    {
        return $this->subscriptionDate;
    }

    /**
     * @param \DateTime $subscriptionDate
     * @return SessionUserData
     */
    public function setSubscriptionDate(\DateTime $subscriptionDate): self
    {
        $this->subscriptionDate = $subscriptionDate;
        return $this;
    }

}

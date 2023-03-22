<?php
/**
 * Created by PhpStorm.
 * User: sarfaoui
 * Date: 26/03/2019
 * Time: 09:01
 */

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\DiscriminatorMap;
use Doctrine\ORM\Mapping\InheritanceType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;


/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 *
 * @InheritanceType("JOINED")
 * @DiscriminatorColumn(name="user_type", type="string")
 * @DiscriminatorMap({"staff" = "Staff", "student" = "Student", "user" = "User"})
 * @UniqueEntity(
 *     fields={"email"},
 *     message="Cet email est déja utilisé"
 * )
 */
class User implements UserInterface
{
    const LENGHT_TOKEN = 50;
    const ROLE_INSCRIT = 'ROLE_INSCRIT';
    const ROLE_CANDIDAT = 'ROLE_CANDIDAT';
    const ROLE_APPRENTI = 'ROLE_APPRENTI';
    const ROLE_CORSAIRE = 'ROLE_CORSAIRE';
    const ROLE_RENEGAT = 'ROLE_RENEGAT';
    const ROLE_ADMIN = 'ROLE_ADMIN';
    const ROLE_MENTOR = 'ROLE_MENTOR';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @ParamConverter("sessionDayCourse",options={"id"="dayId"})
     * @Serializer\Groups({"candidature","users"})
     */
    private $id;

    /**
     * @Assert\NotBlank(
     *     message="Vous devez entrer un email"
     * )
     * @Assert\Email(
     *     message = "L'email {{ value }} est invalide"
     * )
     * @ORM\Column(type="string", length=180, unique=true)
     * @Serializer\Groups({"user","users","candidature"})
     */
    private $email;

    /**
     * @ORM\Column(type="array")
     * @Serializer\Groups({"user","users"})
     */
    private $roles = [];

    /**
     * @Assert\NotBlank(
     *     message="Vous devez entrer un mot de passe"
     * )
       * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @Assert\NotBlank(
     *     message="Vous devez entrer un nom"
     * )
     * @ORM\Column(type="string", length=255)
     * @Serializer\Groups({"user","users","candidature"})
     */
    private $firstName;

    /**
     * @Assert\NotBlank(
     *     message="Vous devez entrer un prénom"
     * )
     * @ORM\Column(type="string", length=255)
     * @Serializer\Groups({"user","users","candidature"})
     */
    private $lastName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $token;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Serializer\Groups({"users"})
     */
    private $isActivated;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Serializer\Groups({"users"})
     */
    private $image;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $newEmail;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Serializer\Groups({"users","candidature"})
     */
    private $registrationDate;

    /**
     * @ORM\OneToMany(targetEntity="SessionUserData", mappedBy="user")
     */
    private $sessionUserDatas;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Resources", mappedBy="resourceOwner")
     */
    private $resources;

    /**
     * @ORM\OneToMany(targetEntity="PreparcoursCandidate", mappedBy="candidate")
     */
    private $Candidatepreparcours;

    public function __construct()
    {
        $this->sessionUserDatas = new ArrayCollection();
        $this->resources = new ArrayCollection();
        $this->Candidatepreparcours = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string)$this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        return array_unique($this->roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string)$this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;
        return $this;
    }

    public function getIsActivated(): ?bool
    {
        return $this->isActivated;
    }

    public function setIsActivated(?bool $isActivated): self
    {
        $this->isActivated = $isActivated;
        return $this;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function setImage($image = null)
    {
        $this->image = $image;
        return $this;
    }

    public function getNewEmail(): ?string
    {
        return $this->newEmail;
    }

    public function setNewEmail(?string $newEmail): self
    {
        $this->newEmail = $newEmail;
        return $this;
    }

    public function getRegistrationDate(): ?\DateTimeInterface
    {
        return $this->registrationDate;
    }

    public function setRegistrationDate(?\DateTimeInterface $registrationDate): self
    {
        $this->registrationDate = $registrationDate;

        return $this;
    }

    /**
     * @return Collection|SessionUserData[]
     */
    public function getSessionUserDatas(): Collection
    {
        return $this->sessionUserDatas;
    }

    public function getFullName()
    {
        return $this->lastName . ' ' . $this->firstName;
    }

    /**
     * @return Collection|Resources[]
     */
    public function getResources(): Collection
    {
        return $this->resources;
    }

    public function addResource(Resources $resource): self
    {
        if (!$this->resources->contains($resource)) {
            $this->resources[] = $resource;
            $resource->setResourceOwner($this);
        }

        return $this;
    }

    public function removeResource(Resources $resource): self
    {
        if ($this->resources->contains($resource)) {
            $this->resources->removeElement($resource);
            // set the owning side to null (unless already changed)
            if ($resource->getResourceOwner() === $this) {
                $resource->setResourceOwner(null);
            }
        }

        return $this;
    }

    public function addSessionUserData(SessionUserData $sessionUserData): self
    {
        if (!$this->sessionUserDatas->contains($sessionUserData)) {
            $this->sessionUserDatas[] = $sessionUserData;
            $sessionUserData->setUser($this);
        }

        return $this;
    }

    public function removeSessionUserData(SessionUserData $sessionUserData): self
    {
        if ($this->sessionUserDatas->contains($sessionUserData)) {
            $this->sessionUserDatas->removeElement($sessionUserData);
            // set the owning side to null (unless already changed)
            if ($sessionUserData->getUser() === $this) {
                $sessionUserData->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|PreparcoursCandidate[]
     */
    public function getCandidatepreparcours(): Collection
    {
        return $this->Candidatepreparcours;
    }

    public function addCandidatepreparcour(PreparcoursCandidate $candidatepreparcour): self
    {
        if (!$this->Candidatepreparcours->contains($candidatepreparcour)) {
            $this->Candidatepreparcours[] = $candidatepreparcour;
            $candidatepreparcour->setCandidate($this);
        }

        return $this;
    }

    public function removeCandidatepreparcour(PreparcoursCandidate $candidatepreparcour): self
    {
        if ($this->Candidatepreparcours->contains($candidatepreparcour)) {
            $this->Candidatepreparcours->removeElement($candidatepreparcour);
            // set the owning side to null (unless already changed)
            if ($candidatepreparcour->getCandidate() === $this) {
                $candidatepreparcour->setCandidate(null);
            }
        }

        return $this;
    }
}

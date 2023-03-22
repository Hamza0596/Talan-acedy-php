<?php
/**
 * Created by PhpStorm.
 * User: sarfaoui
 * Date: 27/03/2019
 * Time: 15:01
 */

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass="App\Repository\StudentRepository")
 */
class Student extends User
{
    const APPRENTI = "apprenti";
    const CORSAIRE = "corsaire";
    const RENEGAT = "renegat";

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Serializer\Groups({"user","users","candidature"})
     */
    private $tel;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Url(
     *      message = "L'URL '{{ value }}' n'est pas une URL valide"
     * )
     * @Serializer\Groups({"user"})
     */
    private $linkedin;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Candidature", mappedBy="candidat")
     */
    private $candidatures;


    /**
     * @ORM\OneToMany(targetEntity="App\Entity\StudentReview", mappedBy="student",cascade={"remove"})
     */
    private $studentReviews;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SubmissionWorks", mappedBy="student",cascade={"remove"})
     */
    private $submissionWorks;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Serializer\Groups({"users"})
     */
    private $status;

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status): void
    {
        $this->status = $status;
    }


    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Affectation", mappedBy="student")
     */
    private $affectations;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ResourceRecommendation", mappedBy="apprentice", orphanRemoval=true)
     */
    private $resourceRecommendation;

    public function __construct()
    {
        $this->candidatures = new ArrayCollection();
        $this->studentReviews = new ArrayCollection();
        $this->submissionWorks = new ArrayCollection();
        $this->affectations = new ArrayCollection();
        $this->resourceRecommendation = new ArrayCollection();
    }

    public static function getGradesValidation()
    {
        return [
            'Bac+5 (Ingénieur, Master2)' => 'Bac+5 (Ingénieur, Master2)',
            'Bac+3 (License)' => 'Bac+3 (License)',
            'Bac+4 (Master1, Maîtrise)' => 'Bac+4 (Master1, Maîtrise)',
            'Doctorat' => 'Doctorat',
            'Autre' => 'Autre'
        ];
    }

    public function getTel(): ?int
    {
        return $this->tel;
    }

    public function setTel(int $tel = null): self
    {
        $this->tel = $tel;

        return $this;
    }

    public function getLinkedin(): ?string
    {
        return $this->linkedin;
    }

    public function setLinkedin(string $linkedin = null): self
    {
        $this->linkedin = $linkedin;

        return $this;
    }

    /**
     * @return Collection|Candidature[]
     */
    public function getCandidatures(): Collection
    {
        return $this->candidatures;
    }

    public function addCandidature(Candidature $candidature): self
    {
        if (!$this->candidatures->contains($candidature)) {
            $this->candidatures[] = $candidature;
            $candidature->setCandidat($this);
        }

        return $this;
    }

    public function removeCandidature(Candidature $candidature): self
    {
        if ($this->candidatures->contains($candidature)) {
            $this->candidatures->removeElement($candidature);
            // set the owning side to null (unless already changed)
            if ($candidature->getCandidat() === $this) {
                $candidature->setCandidat(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|StudentReview[]
     */
    public function getStudentReviews(): Collection
    {
        return $this->studentReviews;
    }

    public function addStudentReview(StudentReview $studentReview): self
    {
        if (!$this->studentReviews->contains($studentReview)) {
            $this->studentReviews[] = $studentReview;
            $studentReview->setStudent($this);
        }

        return $this;
    }

    public function removeStudentReview(StudentReview $studentReview): self
    {
        if ($this->studentReviews->contains($studentReview)) {
            $this->studentReviews->removeElement($studentReview);
            // set the owning side to null (unless already changed)
            if ($studentReview->getStudent() === $this) {
                $studentReview->setStudent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|SubmissionWorks[]
     */
    public function getSubmissionWorks(): Collection
    {
        return $this->submissionWorks;
    }

    public function addSubmissionWork(SubmissionWorks $submissionWork): self
    {
        if (!$this->submissionWorks->contains($submissionWork)) {
            $this->submissionWorks[] = $submissionWork;
            $submissionWork->setStudent($this);
        }

        return $this;
    }

    public function removeSubmissionWork(SubmissionWorks $submissionWork): self
    {
        if ($this->submissionWorks->contains($submissionWork)) {
            $this->submissionWorks->removeElement($submissionWork);
            // set the owning side to null (unless already changed)
            if ($submissionWork->getStudent() === $this) {
                $submissionWork->setStudent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Affectation[]
     */
    public function getAffectations(): Collection
    {
        return $this->affectations;
    }

    public function addAffectation(Affectation $affectation): self
    {
        if (!$this->affectations->contains($affectation)) {
            $this->affectations[] = $affectation;
            $affectation->setStudent($this);
        }

        return $this;
    }

    public function removeAffectation(Affectation $affectation): self
    {
        if ($this->affectations->contains($affectation)) {
            $this->affectations->removeElement($affectation);
            // set the owning side to null (unless already changed)
            if ($affectation->getStudent() === $this) {
                $affectation->setStudent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ResourceRecommendation[]
     */
    public function getResourceRecommendation(): Collection
    {
        return $this->resourceRecommendation;
    }

    public function addResourceRecommendation(ResourceRecommendation $resourceRecommendation): self
    {
        if (!$this->resourceRecommendation->contains($resourceRecommendation)) {
            $this->resourceRecommendation[] = $resourceRecommendation;
            $resourceRecommendation->setApprentice($this);
        }

        return $this;
    }

    public function removeResourceRecommendation(ResourceRecommendation $resourceRecommendation): self
    {
        if ($this->resourceRecommendation->contains($resourceRecommendation)) {
            $this->resourceRecommendation->removeElement($resourceRecommendation);
            // set the owning side to null (unless already changed)
            if ($resourceRecommendation->getApprentice() === $this) {
                $resourceRecommendation->setApprentice(null);
            }
        }

        return $this;
    }
}

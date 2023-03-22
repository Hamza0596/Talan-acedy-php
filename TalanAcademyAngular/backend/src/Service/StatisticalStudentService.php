<?php


namespace App\Service;


use App\Entity\Cursus;
use App\Repository\CandidatureRepository;
use App\Repository\StudentRepository;

class StatisticalStudentService
{
    private $studentRepository;
    /**
     * @var CandidatureRepository
     */
    private $candidatureRepository;

    public function __construct(StudentRepository $studentRepository,CandidatureRepository $candidatureRepository)
    {
        $this->studentRepository = $studentRepository;
        $this->candidatureRepository = $candidatureRepository;
    }

    public function getAllCandidate($role=null)
    {
        return $this->studentRepository->findAllCandidateByRole($role);
    }

    public function getCandidateByCursus(Cursus $cursus,$role)
    {
        return  $this->candidatureRepository->getCandidateByCursusAndRole($cursus,$role);
    }

}

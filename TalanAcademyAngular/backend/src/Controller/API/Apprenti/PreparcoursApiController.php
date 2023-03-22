<?php

namespace App\Controller\API\Apprenti;

use App\Entity\Candidature;
use App\Entity\Preparcours;
use App\Entity\PreparcoursCandidate;
use App\Entity\User;
use App\Service\CandidatureApiService;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;


/**
 * Class PreparcoursApiController
 * @package App\Controller
 * @Rest\Route("/preparcours")
 */
class PreparcoursApiController extends AbstractController
{
    private $candidatureApiService;

    private $entityManager;

    public function __construct(CandidatureApiService $candidatureApiService, EntityManagerInterface $entityManager)
    {
        $this->candidatureApiService = $candidatureApiService;
        $this->entityManager = $entityManager;
    }

    /**
     * @Rest\Post("/add/{id}", name="add_preparcours_api")
     * @Rest\View(serializerGroups={"preparcoursCandidate"})
     * @param User $user
     * @param Request $request
     * @return mixed
     */
    public function addPreparcours(User $user, Request $request)
    {
        return $this->candidatureApiService->addCandidatePreparcours($user, $request);
    }

    /**
     * @Rest\Get("/preparcoursCandidate/{id}/{candidatureId}", name="get_preparcours_api")
     * @ParamConverter("candidature", options={"id"="candidatureId"})
     * @Rest\View(serializerGroups={"preparcoursCandidate"})
     * @param User $user
     * @param Candidature $candidature
     * @return object|null
     */
    public function getPreparcoursCandidate(User $user, Candidature $candidature)
    {
        return $this->entityManager->getRepository(PreparcoursCandidate::class)->findOneBy(['candidate' => $user, 'candidature' => $candidature]);
    }

    /**
     * @Rest\Get("/downloadPreparcoursCandidate/{id}", name="download_preparcours_api")
     * @Rest\View(serializerGroups={"preparcoursCandidate"})
     * @param PreparcoursCandidate $preparcoursCandidate
     * @return string
     */
    public function downloadPreparcours(PreparcoursCandidate $preparcoursCandidate)
    {
        return $this->candidatureApiService->viewPreparcoursPdf($preparcoursCandidate);
    }

    /**
     * @Rest\Post("/saveRepo/{id}", name="save_repo_git_api")
     * @Rest\View(serializerGroups={"preparcoursCandidate"})
     * @param PreparcoursCandidate $preparcoursCandidate
     * @param Request $request
     * @return mixed
     */
    public function saveRepoGit(PreparcoursCandidate $preparcoursCandidate, Request $request)
    {
        return $this->candidatureApiService->saveRepoGit($preparcoursCandidate, $request);
    }

    /**
     * @Rest\Get("/getPreparcours", name="get_preparcours")
     * @Rest\View(serializerGroups={"preparcours"})
     */
    public function getPreparcours()
    {
        return $this->entityManager->getRepository(Preparcours::class)->findOneBy(['isActivated' => 1]);
    }

}

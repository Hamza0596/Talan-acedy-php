<?php
/**
 * Created by PhpStorm.
 * User: jghada
 * Date: 09/06/2020
 * Time: 15:55
 */

namespace App\Service;


use App\Entity\Candidature;
use App\Entity\CandidatureState;
use App\Entity\Cursus;
use App\Entity\Preparcours;
use App\Entity\PreparcoursCandidate;
use App\Entity\User;
use App\Repository\CandidatureRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class CandidatureApiService extends AbstractController
{

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var CandidatureRepository
     */
    private $candidatureRepository;

    private $levelProfileService;

    private $cvUploadService;

    public function __construct(EntityManagerInterface $entityManager, CandidatureRepository $candidatureRepository,
                                LevelProfileService $levelProfileService, CvUploadService $cvUploadService)
    {

        $this->entityManager = $entityManager;
        $this->candidatureRepository = $candidatureRepository;
        $this->levelProfileService = $levelProfileService;
        $this->cvUploadService = $cvUploadService;
    }

    public function applyCursus(User $user, Request $request)
    {
        $result=[];
        $cursus = $this->entityManager->getRepository(Cursus::class)->find($request->request->get('cursus'));
        $candidature = $this->candidatureRepository->findOneBy(['candidat' => $user ,'status' => Candidature::DRAFT]);
        if ($candidature){
            $levelProfile = $this->levelProfileService->levelProfile($user);
            $levelCandidature = $this->levelProfileService->levelCandidature($candidature);
            if ($levelProfile == 100 && $levelCandidature == 100) {
                $user->setRoles([User::ROLE_CANDIDAT]);
                $candidature->setStatus(Candidature::NOUVEAU);
                $candidature->setCursus($cursus);
                $candidature->setDatePostule(new \DateTime());
                $this->entityManager->persist($candidature);
                $candidatureState = new CandidatureState();
                $candidatureState->setCandidature($candidature);
                $candidatureState->setTitle('Dépôt de candidature');
                $candidatureState->setStatus($candidature->getStatus());
                $candidatureState->setDate($candidature->getDatePostule());
                $this->entityManager->persist($candidatureState);
                $this->entityManager->flush();
                $result['message'] = 'Candidature enregistré avec succès';
                $result['cursusName'] = $cursus->getName();
                $result['user'] = $user;
                $result['candidature'] = $candidature;
                $result['code'] = 1;
            } else {
                $result['message'] = 'Veuillez remplir tous les champs obligatoires avant de postuler';
                $result['code'] = 0;
            }
        }

       return $result;

    }

    public function editCandidateFormation(Request $request, User $user, Cursus $cursus)
    {
        $data = $request->request->all();
        $data['cv'] = $request->files->get('cv');
        $candidature = $this->entityManager->getRepository(Candidature::class)->findOneBy(['candidat' => $user, 'status' => Candidature::DRAFT]);
        if (!$candidature) {
            $candidature = new Candidature();
            $candidature->setStatus(Candidature::DRAFT);
            $candidature->setCandidat($user);
            $candidature->setDatePostule(new DateTime());
        }
        $candidature->setCursus($cursus);
        $candidature->setCurrentSituation($data['currentSituation']);
        $candidature->setItExperience($data['itExperience']);
        $candidature->setLinkLinkedin($data['linkLinkedin']);
        $candidature->setGrades($data['grades']);
        $candidature->setDegree($data['degree']);
        if ($data['cv']) {
            $fileName = $this->cvUploadService->upload($data['cv'], $user, $candidature);
            $candidature->setCv($fileName);
        }
        $this->entityManager->persist($candidature);
        $this->entityManager->flush();

        $result['code'] = 1;
        $result ['message'] = "Modification enregistrée avec succès !";
        return $result;
    }

    public function addCandidatePreparcours(User $user, Request $request)
    {
        $filesystem = new Filesystem();
        $preparcours = $this->entityManager->getRepository(Preparcours::class)->findOneBy(['isActivated' => 1]);
        $candidatureId = $request->request->get('candidature');
        $candidature = $this->entityManager->getRepository(Candidature::class)->find($candidatureId);
        if ($preparcours) {
            $findCandidatePreparcours = $this->entityManager->getRepository(PreparcoursCandidate::class)->findBy(['candidate' => $user, 'preparcours' => $preparcours]);
            if (!$findCandidatePreparcours) {
                $preparcoursCandidate = new PreparcoursCandidate();
                $preparcoursCandidate->setStatus("en cours");
                $preparcoursCandidate->setStartDate(new DateTime());
                try {
                    $filesystem->copy('../private/preparcours/'. $preparcours->getPdf(), '../private/preparcours_candidate/' . $preparcours->getPdf());
                } catch (IOExceptionInterface $exception) {
                    $result['code'] = 0;
                    $result['message'] = "An error occurred while creating your directory at " . $exception->getPath();
                }

                $preparcoursCandidate->setPreparcoursPdf($preparcours->getPdf());
                $preparcoursCandidate->setCandidate($user);
                $preparcoursCandidate->setPreparcours($preparcours);
                $preparcoursCandidate->setCandidature($candidature);
                $this->entityManager->persist($preparcoursCandidate);
                $this->entityManager->flush();

                /*---------------calcul deadline----------*/
                $preparcoursStartDate = $preparcoursCandidate->getStartDate();
                $preparcoursStartDateClone = clone $preparcoursStartDate;
                $preparcoursEndDate = $preparcoursStartDateClone->modify('+1 week');
                $date = new DateTime();
                $deadline = $date->format('d-m-Y H:i:s') < $preparcoursEndDate;
                /*---------------calcul deadline------------*/

                $candidature = $this->entityManager->getRepository(Candidature::class)->findOneBy(['candidat' => $user, 'status' => Candidature::NOUVEAU]);

                $result['code'] = 1;
                $result['deadline'] = $deadline;
                $result['preparcours'] = $preparcoursCandidate;
                return $result;
            }
            $result['code'] = 0;
            $result['message'] = 'Vous avez commencé le préparcours';
            return $result;
        }
        $result['code'] = 0;
        $result['message'] = 'Aucun préparcours n\'est activé';

        return $result;
    }

    public function viewPreparcoursPdf(PreparcoursCandidate $preparcoursCandidate)
    {
        $binaryFileResponse= new BinaryFileResponse($this->getParameter('preparcours_candidate_directory'). $preparcoursCandidate->getPreparcoursPdf());
        $binaryFileResponse->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $binaryFileResponse->getFile()->getFilename()
        );
        $binaryFileResponse->headers->set('Content-Type', 'application/pdf');

        return $binaryFileResponse;
    }

    public function saveRepoGit(PreparcoursCandidate $preparcoursCandidate, Request $request)
    {
        $repoGit = $request->request->get('repoGit');
        if ($preparcoursCandidate->getStatus() == PreparcoursCandidate::EN_COURS) {
            $preparcoursCandidate->setRepoGit($repoGit);
            $preparcoursCandidate->setSubmissionDate(new DateTime());
            $preparcoursCandidate->setStatus(PreparcoursCandidate::SOUMIS);
            $this->entityManager->persist($preparcoursCandidate);
            $this->entityManager->flush();
            $result['code'] = 1;
            $result['message'] = 'Travail soumis avec succès';
            $result['preparcours'] = $preparcoursCandidate;
        } else {
            $result['code'] = 0;
            $result['message'] = 'Tu as dépassé le deadline! Tu ne peux plus soumettre le préparcours';
        }
        return $result;
    }
}

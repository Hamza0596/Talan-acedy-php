<?php
/**
 * Created by PhpStorm.
 * User: sourajini
 * Date: 26/04/2019
 * Time: 14:49
 */

namespace App\Service;

use App\Controller\API\Admin\CandidateController;
use App\Controller\API\Admin\SessionController;
use App\Controller\API\Admin\SessionManagementController;
use App\Entity\Candidature;
use App\Entity\CandidatureState;
use App\Entity\Correction;
use App\Entity\Preparcours;
use App\Entity\PreparcoursCandidate;
use App\Entity\Session;
use App\Entity\SessionDayCourse;
use App\Entity\SessionModule;
use App\Entity\SessionProjectSubject;
use App\Entity\SessionUserData;
use App\Entity\StudentReview;
use App\Entity\SubmissionWorks;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

class DataTableService
{
    const NOT_SUBMITTED_MSG = "<span flow='left' tooltip='Le travail n`a pas été soumis'><i class='fas fa-times-circle fa-lg'></i></span>";
    const NOT_SUBMITTED_REPO = "<span flow='left' tooltip='Le travail n`a pas été soumis' style='padding-right: 5px'><i class='fas fa-unlink'></i></span>";
    const NOT_CORRECTED_MSG = "<span flow='left' tooltip='Le travail n`a pas été corrigé'><i class='fas fa-times-circle fa-lg'></i></span>";
    const WAITING_CORRECTION_MSG = "<i class='fas fa-hourglass-half fa-lg' style='padding-right: 5px'></i>";

    private $router;
    private $params;
    /**
     * @var EntityManager
     */
    private $em;
    /**
     * @var CalculateAverageService
     */
    private $averageService;
    /**
     * @var ApprentiService
     */
    private $apprentiService;
    /**
     * @var Environment
     */
    private $twig;
    /**
     * @var AssociateDateService
     */
    private $associateDateService;
    /**
     * @var AdminDashboardService
     */
    private $adminDashboardService;
    /**
     * @var SessionService
     */
    private $sessionService;

    public function __construct(AssociateDateService $associateDateService, ApprentiService $apprentiService, Environment $twig, RouterInterface $router, ParameterBagInterface $params, EntityManagerInterface $em, CalculateAverageService $averageService, AdminDashboardService $adminDashboardService, SessionService $sessionService) //NOSONAR
    {
        $this->params = $params;
        $this->router = $router;
        $this->em = $em;
        $this->averageService = $averageService;
        $this->apprentiService = $apprentiService;
        $this->twig = $twig;
        $this->associateDateService = $associateDateService;
        $this->adminDashboardService = $adminDashboardService;
        $this->sessionService = $sessionService;
    }

    public function ordersDataTables($orders, $columns)
    {
        foreach ($orders as $key => $order) {
            $orders[$key]['name'] = $columns[$order['column']]['name'];
        }
        return $orders;
    }

    public function switchCaseAdminController($column, $staff, $responseTemp)
    {
        switch ($column['name']) {
            case 'firstName':
            {
                $responseTemp = $staff->getFirstName();
                break;
            }

            case 'lastName':
            {
                $responseTemp = $staff->getLastName();
                break;
            }
            case 'email':
            {
                $responseTemp = $staff->getEmail();
                break;
            }
            case 'function':
            {
                $responseTemp = $staff->getFunction();
                break;
            }

            case 'cursus':
            {
                $cursus = $staff->getCursus();
                if ($cursus !== null) {
                    $responseTemp = ucfirst($cursus->getName());
                }
                break;
            }
            case 'actions':
            {
                $id = $staff->getId();
                $urldelete = $this->router->generate('staff_delete', ['id' => $id]);
                $urlreset = $this->router->generate('staff_mail_resetPassword', ['id' => $id]);
                $urlupdate = $this->router->generate('staff_getEditModal', ['id' => $id]);
                $urldisableEnable = $this->router->generate('staff_disable_enable', ['id' => $id]);
                $responseTemp = "<a href='" . $urlreset . "' class='staff-reset' data-toggle='tooltip' data-placement='bottom' data-original-title='Réinitialiser le mot de passe' '><i class='fa fa-lock'></i></a>";
                $responseTemp .= " ";
                $responseTemp .= "<a href='" . $urlupdate . "' class='staff-edit' data-toggle='tooltip' data-placement='bottom' data-original-title='Modifier' '><i class='fa fa-edit icon-edit'></i></a>";
                if ($staff->getIsActivated() == 1) {
                    $responseTemp .= " <a href='" . $urldisableEnable . "' class='staff-diasble' data-toggle='tooltip' data-placement='bottom' data-original-title='Désactiver''><i class='fa fa-ban'></i></a>";
                } else {
                    $responseTemp .= " <a href='" . $urldisableEnable . "' class='staff-enable' data-toggle='tooltip' data-placement='bottom' data-original-title='Activer''><i class='fa fa-user-circle'></i></a>";
                }
                $responseTemp .= " ";
                $responseTemp .= "<a href='" . $urldelete . "' class='staff-delete' data-toggle='tooltip' data-placement='bottom' data-original-title='Supprimer' '><i class='fas fa-times'></i></a> ";

            }
            default :
                break;

        }
        return $responseTemp;
    }

    public function switchCaseSessionControllerValidationsApprenti($column, $dayValidant, $responseTemp, $user)
    {
        $moy = self::NOT_SUBMITTED_MSG;
        $repo = self::NOT_SUBMITTED_REPO;
        $commentResult = "<span flow='left' tooltip='Le travail n`a pas été soumis' style='padding-right: 5px'><i class='fas fa-comments' style='font-size: 1.2rem'></i></span>";
        $details = self::NOT_SUBMITTED_MSG;
        $module = $dayValidant->getModule();

        if ($validation = $this->em->getRepository(SubmissionWorks::class)->findOneBy(['course' => $dayValidant, 'student' => $user])) {
            $moy = self::NOT_CORRECTED_MSG;
            $commentResult = "<span flow='left' tooltip='Le travail n`a pas été corrigé' style='padding-right: 5px'><i class='fas fa-comments' style='font-size: 1.2rem'></i></span>";
            $details = self::NOT_CORRECTED_MSG;
            $repo = "<a class='link-repo-day' target='_blank' href='" . $validation->getRepoLink() . "' style='padding-right: 5px;'><i class='fab fa-git fa-2x'></i></a>";
            $dayCorrection = $this->em->getRepository(SessionDayCourse::class)->findOneBy(['module' => $module, 'ordre' => $dayValidant->getOrdre() + 1]);
            $corrections = $this->em->getRepository(Correction::class)->findBy(['corrected' => $user, 'day' => $dayCorrection]);
            $dayDate = $this->associateDateService->getPlanifiedDateFromSessionDay($dayCorrection);
            $dayDateMax = clone $dayDate;
            $today = new \DateTime();
            date_time_set($dayDateMax, $module->getSession()->getHMaxCorection(), 0, 0);
            if ($corrections) {
                $myCorrectionsList = $this->apprentiService->createCorrectionsMetrics($corrections);
                $score = $this->averageService->calculateDayScore($dayCorrection, $user);
                if ($score) {
                    if ($score['total'] != 0) {
                        $moy = $score['note'] * 100 / $score['total'];
                        $moy = round($moy) . '%';
                    }
                    $i = 0;
                    $emptyComment = true;
                    while ($i < count($corrections) && $emptyComment) {
                        if ($corrections[$i]->getComment()) {
                            $emptyComment = false;
                        }
                        $i++;
                    }

                    if ($emptyComment) {
                        $commentResult = "<span flow='left' tooltip='Pas de commentaires' style='padding-right: 5px;'><span style='display:none'>Pas de commentaires</span><i class='fas fa-comments' style='font-size: 1.2rem'></i></span>";
                    } else {
                        $commentResult = $this->twig->render('dashboard/session/apprentice_performance/commentModal.html.twig', ['corrections' => $corrections, 'dayId' => $dayValidant->getId()]);
                    }
                    $details = $this->twig->render('dashboard/session/apprentice_performance/correctionModal.html.twig', ['myCorrectionsList' => $myCorrectionsList, 'dayId' => $dayValidant->getId()]);
                } elseif ($today < $dayDateMax) {
                    $moy = self::WAITING_CORRECTION_MSG;
                    $commentResult = self::WAITING_CORRECTION_MSG;
                    $details = self::WAITING_CORRECTION_MSG;
                }
            }
        }


        switch ($column['name']) {
            case 'module':
            {
                $responseTemp = ucfirst($dayValidant->getModule()->getTitle());
                break;
            }
            case 'course':
            {
                $responseTemp = ucfirst($dayValidant->getDescription());
                break;
            }
            case 'note':
            {
                $dayCorrection = $this->em->getRepository(SessionDayCourse::class)->findOneBy(['module' => $module, 'ordre' => $dayValidant->getOrdre() + 1]);
                $responseTemp = '<span class="' . $dayCorrection->getId() . '">' . $moy . '</span>';
                if ($repo != self::NOT_SUBMITTED_REPO) {
                    if ($corrections) {
                        $responseTemp = '<span style="display:none"> Moy : </span>';
                        $responseTemp .= '<span class="' . $dayCorrection->getId() . '">' . $moy . '</span>';
                    } else {
                        $responseTemp .= '<span style="display:none">Le travail n\'a pas été corrigé</span>';
                    }
                } else {
                    $responseTemp = '<span class="' . $dayCorrection->getId() . '">' . $moy . '</span>';
                    $responseTemp .= '<span style="display:none">Le travail n\'a pas été soumis</span>';
                }
                break;
            }
            case 'details':
            {
                $responseTemp = "<div class='d-inline-flex d-md-flex' style='justify-content: center; align-items: center;'>";
                $responseTemp .= $repo;
                if ($repo != self::NOT_SUBMITTED_REPO) {
                    $responseTemp .= '<span style="display:none"> Repo Git:' . $validation->getRepoLink() . '</span>';
                } else {
                    $responseTemp .= '<span style="display:none">Le travail n\'a pas été soumis</span>';
                }

                if ($repo != self::NOT_SUBMITTED_REPO) {
                    $responseTemp .= $commentResult;
                    if (!$corrections) {
                        $responseTemp .= '<span style="display:none">Le travail n\'a pas été corrigé</span>';
                    }

                } else {
                    $responseTemp .= $commentResult;
                    $responseTemp .= '<span style="display:none">Le travail n\'a pas été soumis</span>';
                }

                $responseTemp .= $details;
                if ($repo != self::NOT_SUBMITTED_REPO) {
                    if (!$corrections) {
                        $responseTemp .= '<span style="display:none">Le travail n\'a pas été corrigé</span>';
                    }
                } else {
                    $responseTemp .= '<span style="display:none">Le travail n\'a pas été soumis</span>';
                }
                $responseTemp .= "</div>";
            }
            default :
                break;

        }
        return $responseTemp;

    }

    public function switchCaseCandidateController($column, $candidature, $manager, $responseTemp, $preparcoursCandidateRepository)
    {
        switch ($column['name']) {
            case 'firstName':
            {
                $candidate = $candidature->getCandidat();
                $img = $candidate->getImage();
                $countCandidature = count($manager->getRepository(Candidature::class)->findBy(['candidat' => $candidature->getCandidat()]));
                if ($img) {
                    if (file_exists($this->params->get(CandidateController::IMAGE_USER_DIRECTORY) . $img)) {
                        $contentImage = file_get_contents($this->params->get(CandidateController::IMAGE_USER_DIRECTORY) . $img);
                        $imageBase64 = base64_encode($contentImage);
                    } else {
                        $contentImage = file_get_contents($this->params->get(CandidateController::IMAGE_USER_DIRECTORY_DEFAULT));
                        $imageBase64 = base64_encode($contentImage);
                    }
                } else {
                    $contentImage = file_get_contents($this->params->get(CandidateController::IMAGE_USER_DIRECTORY_DEFAULT));
                    $imageBase64 = base64_encode($contentImage);
                }
                if ($countCandidature == 1) {
                    $responseTemp = "<img title='' alt='' src='data:image/png;base64, " . $imageBase64 . "' class='user-avatar'>" . "&nbsp &nbsp" . $candidate->getFirstName() . ' ' . $candidate->getLastName();
                    break;
                } else {
                    $responseTemp = "<img title='' alt='' src='data:image/png;base64, " . $imageBase64 . "' class='user-avatar'>" . "&nbsp &nbsp" . $candidate->getFirstName() . ' ' . $candidate->getLastName() . ' (' . $countCandidature . ')';
                    break;
                }
            }

            case 'checkbox':
            {
                $id = $candidature->getId();
                $responseTemp = " <form method='post' action='/admin/check' id='ch'><div class='form-checkbox'><input class='check' type='checkbox' value='" . $id . "' name='checkCandidate'><span class='check'><i class='zmdi zmdi-check zmdi-hc-lg'></i></span></div></form>";
                break;
            }
            case 'date':
            {
                $id = $candidature->getCandidat()->getId();
                $date = $candidature->getDatePostule();
                $checkPreparcours = $manager->getRepository(Preparcours::class)->findOneBy(['isActivated' => 1]);
                $preparcours = $preparcoursCandidateRepository->findBy(['candidate' => $id]);
                $urlCandidaturePreparcours = $this->router->generate('candidate_preparcours', ['id' => $id, 'idCandidature' => $candidature->getId()]);

                if ($preparcours) {
                    if ($preparcours[0]->getStatus() == PreparcoursCandidate::DEBORDE) {
                        $dateStr = '' . $date->format('d-m-Y') . '<a href="' . $urlCandidaturePreparcours . '" class="candidature-preparcours-status">
                                                            <i class="far fa-times-circle text-red fa-lg ml-5"  data-toggle="tooltip" data-placement="bottom" data-original-title="Consulter l\'état du pré-parcours"></i>
                                                            </a>';
                    } elseif ($preparcours[0]->getStatus() == PreparcoursCandidate::SOUMIS) {
                        if ($preparcours[0]->getDecision() == PreparcoursCandidate::VALIDATED) {
                            $dateStr = '' . $date->format('d-m-Y') . '<a id="' . $id . '" href="' . $urlCandidaturePreparcours . '" class="candidature-preparcours-status" >
                                                           <i class="far fa-thumbs-up fa-lg ml-5" style="color: #00D998;" data-toggle="tooltip" data-placement="bottom" data-original-title="Consulter l\'état du pré-parcours"></i>
                                                            </a>';
                        } elseif ($preparcours[0]->getDecision() == PreparcoursCandidate::REJECTED) {
                            $dateStr = '' . $date->format('d-m-Y') . '<a id="' . $id . '" href="' . $urlCandidaturePreparcours . '" class="candidature-preparcours-status" >
                                                            <i class="far fa-thumbs-down fa-lg ml-5 text-red" data-toggle="tooltip" data-placement="bottom" data-original-title="Consulter l\'état du pré-parcours"></i>
                                                            </a>';
                        } else {
                            $dateStr = '' . $date->format('d-m-Y') . '<a id="' . $id . '" href="' . $urlCandidaturePreparcours . '" class="candidature-preparcours-status" >
                                                            <i class="far fa-check-circle fa-lg ml-5" style="color: #00D998;" data-toggle="tooltip" data-placement="bottom" data-original-title="Consulter l\'état du pré-parcours"></i>
                                                            </a>';
                        }

                    } else {
                        $dateStr = '' . $date->format('d-m-Y') . '<a id="' . $id . '" href="' . $urlCandidaturePreparcours . '" class="candidature-preparcours-status">
                                                            <i class="fas fa-hourglass-half" style="color: #3d00f2; width: 0.9em;height: 1.2em;margin-left: 3.2rem;"  data-toggle="tooltip" data-placement="bottom" data-original-title="Consulter l\'état du pré-parcours"></i>
                                                            </a>';
                    }
                } else {
                    if ($candidature->getStatus() == Candidature::ACCEPTE || $candidature->getStatus() == Candidature::ABANDONMENT || $candidature->getStatus() == Candidature::REFUSE) {
                        $dateStr = '' . $date->format('d-m-Y');
                    } else {
                        if ($checkPreparcours) {
                            $dateStr = '' . $date->format('d-m-Y') . '<a id="' . $id . '" href="' . $urlCandidaturePreparcours . '" class="candidature-preparcours-status">
                                                            <i class="far fa-hourglass" style="color: #3d00f2; width: 0.9em;height: 1.2em;margin-left: 3.2rem;"  data-toggle="tooltip" data-placement="bottom" data-original-title="Consulter l\'état du pré-parcours"></i>
                                                            </a>';
                        } else {
                            $dateStr = '' . $date->format('d-m-Y') . '<span>
                                                           <i class="fas fa-ban ml-5 text-red" style="width: 1.2em;height: 1.2em;"  data-toggle="tooltip" data-placement="bottom" data-original-title="Pré-parcours inexistant"></i>
                                                            </span>';
                        }
                    }
                }
                $responseTemp = $dateStr;
                break;
            }

            case CandidateController::CURSUS:
            {
                $cursus = $candidature->getCursus();
                if ($cursus !== null) {
                    $responseTemp = ucfirst($cursus->getName());
                }
                break;
            }

            case 'status':
            {
                $candidate = $candidature->getCandidat();
                $countInterview = count($manager->getRepository(CandidatureState::class)->findBy(['candidature' => $candidature, 'status' => Candidature::INVITE_ENTRETIEN]));
                if ($candidature !== null) {
                    if ($candidature->getStatus() == Candidature::NOUVEAU) {
                        $responseTemp = "<span class='d-inline d-md-block badge badge-primary' style='width: 50%;'>A traiter</span>";
                    } elseif ($candidature->getStatus() == Candidature::ACCEPTE) {
                        $session = $candidature->getSessionUserData()->getSession();
                        $responseTemp = "<span class='d-inline d-md-block badge badge-primary recrute' style='width: 50%;'>Recruté </span><span class='d-inline d-md-block badge badge-light' style='width: 70%; margin-top: 2%'>" . $session->getCursus()->getName() . " " . $session->getName() . "</span>";
                    } elseif ($candidature->getStatus() == Candidature::INVITE_ENTRETIEN) {
                        $responseTemp = "<span class='d-inline d-md-block badge badge-primary' style='width: 50%'>A recevoir #" . $countInterview . "</span>";
                    } elseif ($candidature->getStatus() == Candidature::REFUSE) {
                        $responseTemp = "<span class='d-inline d-md-block badge badge-primary' style='width: 50%'>Négatif</span>";
                    } elseif ($candidature->getStatus() == Candidature::ABANDONMENT) {
                        $responseTemp = "<span class='d-inline d-md-block badge badge-primary' style='width: 50%'>Abandon</span>";
                    }
                }
                break;
            }

            case 'actions':
            {
                $id = $candidature->getCandidat()->getId();
                $urlprofil = $this->router->generate('candidate_profil', ['id' => $candidature->getId()]);
                $urlCandidature = $this->router->generate('candidate_candidature', ['id' => $id, 'idCandidature' => $candidature->getId()]);
                $responseTemp = "<a href='" . $urlprofil . " ' class='candidate-profil mr-1' data-toggle='tooltip' data-placement='bottom' data-original-title='Consulter le profil' '><i class='fas fa-address-card'></i></a> ";
                $responseTemp .= "  ";
                $responseTemp .= "<a href=' " . $urlCandidature . "' class='candidate-candidature' data-toggle='tooltip' data-placement='bottom' data-original-title='Consulter la candidature' '><i class='fas fa-cogs'></i></a> ";
                break;
            }
            default:
                break;
        }
        return $responseTemp;
    }

    public function switchCaseRegistredUserController($column, $responseTemp, $registred)
    {
        switch ($column['name']) {
            case 'checkbox':
            {
                $id = $registred->getId();
                $responseTemp = " <div class='form-checkbox'><input type='checkbox' value='" . $id . "' name='check' class='checkbox'><span class='check'><i class='zmdi zmdi-check zmdi-hc-lg'></i></span></div>";
                break;
            }
            case 'firstName':
            {
                $responseTemp = ucfirst($registred->getFirstName()) . ' ' . strtoupper($registred->getLastName());
                break;
            }

            case 'email':
            {
                $responseTemp = $registred->getEmail();
                break;
            }

            case 'actions':
            {
                $id = $registred->getId();
                $urlreset = $this->router->generate('registred_mail_resetPassword', ['id' => $id]);
                $urldelete = $this->router->generate('registred_delete', ['id' => $id]);
                $responseTemp = "<a href='" . $urlreset . "' class='registred-reset' data-toggle='tooltip' data-placement='bottom' data-original-title='Réinitialiser le mot de passe' '><i class='fas fa-lock'></i></a>";
                $responseTemp .= " ";
                $responseTemp .= "<a href='" . $urldelete . "' class='pl-1 registred-delete' data-toggle='tooltip' data-placement='bottom' data-original-title='Supprimer' '><i class='fas fa-times'></i></a> ";
                break;
            }
            case 'actif':
            {
                if ($registred->getIsActivated() == 1) {
                    $responseTemp = "<i style='color:#3D00F2' class=\"fa fa-check\"></i>";
                }
            }
            default:
                break;

        }
        return $responseTemp;
    }

    public function switchCaseCandidateSessionController($column, $responseTemp, $session, $sessionUser, $previouRoute, $role)
    {
        switch ($column['name']) {
            case 'firstName':
            {
                $registred = $sessionUser->getUser();
                $img = $registred->getImage();
                $status = $sessionUser->getStatus();
                if ($img) {
                    if (file_exists($this->params->get('image_user_directory') . $img)) {
                        $contentImage = file_get_contents($this->params->get('image_user_directory') . $img);
                        $imageBase64 = base64_encode($contentImage);
                    } else {
                        $contentImage = file_get_contents($this->params->get('image_user_directory_default'));
                        $imageBase64 = base64_encode($contentImage);
                    }
                } else {
                    $contentImage = file_get_contents($this->params->get('image_user_directory_default'));
                    $imageBase64 = base64_encode($contentImage);
                }
                $responseTemp = "<div style='display: flex'><img title='' alt='' src='data:image/png;base64, " . $imageBase64 . "' class='user-avatar mr-4'>" . "&nbsp <div style='display: flex; flex-direction: column; justify-content: center;'>" . $registred->getFirstName() . ' ' . strtoupper($registred->getLastName());

                if ($status == SessionUserData::APPRENTI) {
                    $responseTemp .= "<span class='badge badge-pill text-white' style='background-color: #3381FF;width: 70px '>" . ucfirst($status) . "</span></div></div>";
                } elseif ($status == SessionUserData::QUALIFIED) {
                    $responseTemp .= "<span class='badge badge-pill text-white' style='background-color: #3381FF;width: 70px '>Qualifié</span></div></div>";
                } elseif ($status == SessionUserData::ELIMINATED) {
                    $responseTemp .= "<span class='badge badge-pill text-white' style='background-color: #3381FF;width: 70px '>Éliminé</span></div></div>";
                } elseif ($status == SessionUserData::CONFIRMED) {
                    $responseTemp .= "<span class='badge badge-pill text-white' style='background-color: #3381FF;width: 70px '>Confirmé</span></div></div>";
                } elseif ($status == SessionUserData::NOTSELECTED) {
                    $responseTemp .= "<span class='badge badge-pill text-white' style='background-color: #3381FF; width: 70px'>Non Retenu</span></div></div>";
                }

                break;
            }

            case 'joker':
            {
                $registred = $sessionUser->getUser();
                $affectation = $this->em->getRepository(SessionUserData::class)->findOneBy(['session' => $session, 'user' => $registred]);
                $nbJoker = $affectation->getNbrJoker();
                $responseTemp = '';
                $responseTemp .= '<span style="display: none">' . $nbJoker . '</span><span class=\'jocker_number\'>';
                $nbJokerSession = $session->getJokerNbr();

                while ($nbJoker > 0) {
                    $responseTemp .= "<a class=''><i class='fas fa-heart' style='color:darkred;font-size: 1.2rem'></i></a>";
                    $nbJoker--;
                }
                $nbJoker = $affectation->getNbrJoker();
                if ($nbJokerSession > $nbJoker) {
                    $diff = $nbJokerSession - $nbJoker;
                    while ($diff > 0) {
                        $responseTemp .= "<a class=''><i class='fas fa-heart' style='color:grey;font-size: 1.2rem'></i></a>";
                        $diff--;
                    }

                }
                $responseTemp .= '</span>';
                break;
            }
            case 'moyenne':
            {
                $registred = $sessionUser->getUser();
                $average = $this->averageService->calculateMinMaxScore($session, $registred);
                $responseTemp = " <div class='d-inline d-md-flex justify-content-end'>";
                $responseTemp .= "<span class='d-inline d-md-flex score_row_mobile' style='font-size: 14px; min-width: 23%; align-items: center; justify-content: center' data-toggle='tooltip' data-placement='bottom' data-original-title='Moyenne des scores'><span style='display: none'> Moy: </span> " . $average['average'] . "%</span>";
                $responseTemp .= "<div class='min-max' style='font-size: 12px;min-width: 30%; display: flex; flex-direction: column; align-items: flex-start'><div data-toggle='tooltip' data-placement='bottom' data-original-title='Max'><span><i class='fas fa-chevron-circle-up' style='color: #3D00F2;'></i><span> <span style='display: none'> Max: </span> " . $average['max'] . "%</span></span></div>";
                $responseTemp .= "<div data-toggle='tooltip' data-placement='bottom' data-original-title='Min'> <span><i class='fas fa-chevron-circle-down text-red' style='color: #82D6D9'></i><span> <span style='display: none'> Min: </span> " . $average['min'] . "%</span></span></div></div></div>";
                break;

            }
            case 'evaluation':
            {
                $registred = $sessionUser->getUser();
                $eval = $this->em->getRepository(StudentReview::class)->countNbrEvaluatingDayForUser($session, $registred);
                $responseTemp = "<div class='ml-4 d-inline d-md-block eval_row'>" . $eval . "</div>";
                break;
            }

            case 'actions':
            {
                $routeName = '';
                switch ($previouRoute) {
                    case SessionManagementController::APPRENTICE_LIST_DASH:
                    {
                        $routeName = SessionController::APPRENTICE_PERFORMANCE_DASH;
                        break;
                    }
                    case SessionManagementController::APPRENTICE_LIST:
                    {
                        $routeName = SessionController::APPRENTICE_PERFORMANCE;
                        break;
                    }
                    case SessionManagementController::APPRENTICE_LISTE_DASH:
                    {
                        $routeName = SessionController::MENTOR_APPRENTICE_PERFORMANCE_DASH;
                        break;
                    }
                }

                $urlProfileCandidate = $this->router->generate('candidate_profil', ['id' => $sessionUser->getCandidature()->getId()]);
                if ($role === User::ROLE_MENTOR) {
                    $urlProfileCandidate = $this->router->generate('candidate_profil_mentor', ['id' => $sessionUser->getCandidature()->getId()]);
                }

                $urlProfile = $this->router->generate($routeName, ['id' => $sessionUser->getId()]);


                $responseTemp = "<a href='" . $urlProfileCandidate . "' class='candidate-profil' data-toggle='tooltip' data-placement='bottom' data-original-title='Consulter le profil'><i class='fas fa-user-circle' style='font-size: 1.2rem'></i></a>";
                $responseTemp .= " ";
                $responseTemp .= "<a href='" . $urlProfile . "' class='' data-toggle='tooltip' data-placement='bottom' data-original-title='Consulter le bilan'><i class='fas fa-medal' style='font-size: 1.2rem'></i></a>";
                $responseTemp .= " ";
                $responseTemp .= "<a href='" . $sessionUser->getRepoGit() . "' class='' data-toggle='tooltip' data-placement='bottom' data-original-title='Accéder au repo' target='_blank'><i class='fab fa-github' style='font-size: 1.2rem'></i></a>";

            }
            default:
                break;
        }

        return $responseTemp;
    }

    public function switchCaseSessionControllerEvaluationApprenti($column, $evaluation, $responseTemp, $studentReviewRepository, $sessionDyCourseRepository)
    {

        switch ($column['name']) {
            case 'module':
            {
                $module = $evaluation['title'];
                $responseTemp = ucfirst($module);
                break;
            }
            case 'course':
            {
                $course = $evaluation['description'];
                $responseTemp = ucfirst($course);
                break;
            }

            case 'note':
            {

                $responseTemp = '';
                $note = $evaluation['rating'];
                if ($note == 0) {
                    $responseTemp .= '<div tooltip=\'0\' flow=\'left\' class="d-flex star_note">';

                } else {

                    $responseTemp .= '<div class="star_note" tooltip=' . $note . ' flow=\'left\'>';
                }


                while ($note > 0) {
                    $responseTemp .= '<i class=\'fa fa-star\' style=\'color:#3D00F2;font-size: 1.2rem\'></i>';
                    $note--;
                }
                $note = $evaluation['rating'];

                if (5 > $note) {
                    $diff = 5 - $note;
                    while ($diff > 0) {
                        $responseTemp .= '<i class=\'fa fa-star\' style=\'color:#ddd;font-size: 1.2rem\'></i>';
                        $diff--;
                    }
                }
                $responseTemp .= '</div>';
                if ($note == 0) {
                    $responseTemp .= '<span style="display:none">Note: 0<span>';
                } else {
                    $responseTemp .= '<span style="display:none">Note: ' . $note . '<span>';
                }

                break;
            }

            case 'comment':
            {
                $dayCourse = $sessionDyCourseRepository->find($evaluation['id']);
                $comment = $studentReviewRepository->findCommentNotNullByDayAndCandidate($dayCourse, $evaluation['studentId']);
                if ($comment) {
                    $responseTemp .= "<a class='dayCommentBtn' data-toggle='tooltip' data-placement='bottom' id='" . $evaluation['id'] . "'  data-value='" . $evaluation['studentId'] . "' data-original-title='Consulter les commentaires' href=''><i class='fas fa-comments' style='font-size: 1.2rem'></i></a>";
                } else {
                    $responseTemp .= "<a class='isDisabled' data-toggle='tooltip' data-placement='bottom' id='" . $evaluation['id'] . "'  data-value='" . $evaluation['studentId'] . "' data-original-title='pas de commentaire'><i class='fas fa-comments' style='font-size: 1.2rem'></i></a>";

                }
                $responseTemp .= " ";

                break;
            }
            default :
                break;

        }

        return $responseTemp;

    }

    public function switchCaseValidationSessionManagementController($column, $responseTemp, $evaluation, $manager, $session)
    {

        $dayId = $evaluation['id'];
        $sessionDayCourse = $manager->getRepository(SessionDayCourse::class)->find($dayId);
        switch ($column['name']) {
            case 'module':
            {
                $course = $evaluation['title'];
                $responseTemp = ucfirst($course);
                break;
            }

            case 'leçon':
            {
                $responseTemp = "<span style='white-space: pre-wrap;'>" . $evaluation['description'] . "</span>";

                break;
            }

            case 'date':
            {
                $responseTemp = $this->associateDateService->getPlanifiedDateFromSessionDay($sessionDayCourse)->format('d-m-Y');
                break;
            }
            case 'note':
            {
                $correctionDay = $this->em->getRepository(SessionDayCourse::class)->findOneBy(['module' => $sessionDayCourse->getModule(), 'ordre' => $sessionDayCourse->getOrdre() + 1]);
                $note = $this->averageService->calculateAverageDayForAllUsers($correctionDay, $session);
                $responseTemp = " <div class='d-flex justify-content-end'>";
                $responseTemp .= "<span class='d-flex align-items-center' style='font-size: 14px; min-width:20%;' data-toggle='tooltip' data-placement='bottom' data-original-title='Moyenne des scores'><span style='display:none;'> Moy: </span>" . $note['average'] . "%</span>";
                $responseTemp .= "<div class='d-none d-md-flex' style='font-size: 12px;min-width: 30%; display: flex; flex-direction: column; align-items: flex-start'><div data-toggle='tooltip' data-placement='bottom' data-original-title='Max'><span><i class='fas fa-chevron-circle-up' style='color:#3D00F2'></i><span><span style='display:none;'>  Max: </span> " . $note['max'] . "%</span></span></div>";
                $responseTemp .= "<div data-toggle='tooltip' data-placement='bottom' data-original-title='Min'><span><i class='fas fa-chevron-circle-down text-red' style='color: #82D6D9'></i><span><span style='display:none;'>  Min: </span> " . $note['min'] . "%</span></span></div></div></div>";
                break;
            }

            default :
                break;

        }
        return $responseTemp;

    }

 
    private function formatRatingByRatingValue($ratings)
    {
        $output = array('avg' => 0, 'totalVoters' => 0, 'details' => array('1' => 0, '2' => 0, '3' => 0, '4' => 0, '5' => 0));
        $totalVoters = 0;
        $totalVotersValue = 0;
        foreach ($ratings as $rating) {
            $ratingValue = $rating['rating_value'];
            $ratingCount = intval($rating['rating_count']);
            $totalVoters += $ratingCount;
            $totalVotersValue += ($ratingValue * $ratingCount);
            $output['details'][$ratingValue] = $ratingCount;
        }
        if ($totalVoters != 0) {
            $avg = $totalVotersValue / $totalVoters;
            $output['avg'] = $avg;
            $output['totalVoters'] = $totalVoters;
        }
        return $output;
    }

  

    public function switchCaseApprentiControllerPassedSession($column, $session, $responseTemp)
    {

        switch ($column['name']) {
            case 'cursus':
            {
                $course = $session['name'];
                $responseTemp = ucfirst($course);
                break;
            }

            case 'session':
            {

                $responseTemp = '<span class=\'font-weight-semibold\'>De </span>' . $session['startDate']->format('d-m-Y') . '<span class=\'font-weight-semibold\'>   à  </span>' . $session['endDate']->format('d-m-Y');

                break;
            }

            case 'finalResult':
            {
                if ($session['status'] == SessionUserData::ABANDONMENT) {
                    $responseTemp = '<span>Abandon</span>';
                } elseif ($session['status'] == SessionUserData::APPRENTI) {
                    $responseTemp = '<span>Apprenti</span>';
                } elseif ($session['status'] == SessionUserData::QUALIFIED) {
                    $responseTemp = '<span>Qualifié</span>';
                } elseif ($session['status'] == SessionUserData::ELIMINATED) {
                    $responseTemp = '<span>Éliminé</span>';
                } elseif ($session['status'] == SessionUserData::CONFIRMED) {
                    $responseTemp = '<span>Confirmé</span>';
                } elseif ($session['status'] == SessionUserData::NOTSELECTED) {
                    $responseTemp = '<span>Non Retenu</span>';
                }

                break;
            }

            case 'actions':
            {
                $courseLink = $this->router->generate('curriculum_viewer', ['id' => $session['id']]);
                $responseTemp = '<div class=\'d-flex justify-content-center\'><a href=' . $courseLink . ' class=\'gx-btn gx-btn-primary gx-btn-sm text-white\'>Voir le cours</a></div>';
                break;
            }

            default :
                break;

        }
        return $responseTemp;

    }

    public function getDataTableConfig($request)
    {
        $res=[];
        $res['draw'] = intval($request->request->get('draw'));
        $res['start']  = $request->request->get('start');
        $res['length']  = $request->request->get('length');
        $res['search'] = $request->request->get('search');
        $res['orders'] = $request->request->get('order');
        $res['columns'] = $request->request->get('columns');
        $res['extraSearch'] = json_decode($request->request->get('extra_search'), true);
        return $res;

    }
}

<?php

namespace App\Controller\API\Admin;

use App\Entity\Cursus;
use App\Repository\CursusRepository;
use App\Service\StatisticalCursusService;
use App\Service\StatisticalModulesService;
use App\Service\CursusService;
use App\Repository\ModuleRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


/**
 * Class CursusController
 * @package App\Controller
 * @Rest\Route("/admin")
 */

class CursusController extends AbstractController
{
    private $statisticalCursusService;
    private $cursusRepo;
    private $cursusService;
    

    public function __construct(CursusRepository $cursusRepo,
                                StatisticalCursusService $statisticalCursusService,
                                CursusService $cursusService)
    {
        $this->cursusRepo=$cursusRepo;
        $this->statisticalCursusService=$statisticalCursusService;
        $this->cursusService=$cursusService;
    }

    /**
     * Show Cursus List
     * @Rest\Get("/cursus")
     * @Rest\View(serializerGroups={"cursus"})
     */
    public function getAllCursus(StatisticalCursusService $statisticalService)
    {
        $cursusList = $this->cursusRepo->findBy([], ['id' => 'DESC']);

        $statistic = $statisticalService->statisticPageCursus();
        $returnResponse = new JsonResponse();
        $result= [
            'statistic' => $statistic,
            'cursusList' => $cursusList,
        ];
        return $result;
    }

      /**
     * Show Cursus Details by id
     * @Rest\Get("/cursus/{id}", name="cursus_list")
     * @Rest\View(serializerGroups={"cursus"})
     */
    public function getCursusById(Cursus $cursus, StatisticalModulesService $statisticalModulesService)
    {
            $staticForPageModules = $statisticalModulesService->staticForPageModules($cursus);
            $listModules = $this->cursusService->getCursusContent($cursus);
          
    
            return [
                'static' => $staticForPageModules,
                'modules' => $listModules,
                'cursus' => $cursus,
            ];
        }

        

    /**
     * Edit cursus visibility
     *
     * @Rest\Post("/cursus/{id}/visibility", name="cursus_edit_visibility")
     * @param Cursus $cursus
     * @return JsonResponse
     */
    public function editCursusVisibility(Cursus $cursus)
    {
            $visibility = $cursus->getVisibility() === Cursus::VISIBLE ? Cursus::INVISIBLE : Cursus::VISIBLE;
            $cursus->setVisibility($visibility);
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            return new JsonResponse([
                'visibility' => $visibility,
                'status' => 201,
                'message' => "La visibilité a été mise à jour avec succès !"]
                , 201);
    }


      /**
     * download Cursus pdf
     * @param Cursus $cursus
     * @Rest\Get("/cursus/{id}/pdf")
     */
    public function getCursusByIdPDF(Cursus $cursus)
    {
            return $this->cursusService->getCursusContentPDF($cursus);
            
    }

    
}
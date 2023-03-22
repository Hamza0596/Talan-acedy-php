<?php
/**
 * Created by PhpStorm.
 * User: jghada
 * Date: 22/11/2019
 * Time: 14:47
 */

namespace App\Service;


use App\Entity\Cursus;
use App\Entity\DayCourse;
use App\Entity\Resources;
use App\Entity\SessionDayCourse;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Environment;
/**
 * Class ExtractPropositionService
 * @package App\Service
 * @codeCoverageIgnore
 */

class ExtractPropositionService
{
    /**
     * @var EntityManagerInterface
     */
    private $manager;
    /**
     * @var Mailer
     */
    private $mailer;
    /**
     * @var Environment
     */
    private $templating;


    /**
     * ExtractPropositionService constructor.
     * @param Environment $templating
     * @param EntityManagerInterface $manager
     * @param Mailer $mailer
     */
    public function __construct(Environment $templating,EntityManagerInterface $manager, Mailer $mailer)
    {
        $this->manager = $manager;
        $this->mailer = $mailer;
        $this->templating = $templating;
    }

    public function getPropostition()
    {
        $resources = $this->manager->getRepository(Resources::class)->getProposedResources();
        $resourcesResult =  [];
        foreach ($resources as $resource){
            $day= $this->manager->getRepository(DayCourse::class)->find($resource['day']);
            if ($day){
                $sessionDayCourse = $this->manager->getRepository(SessionDayCourse::class)->findOneBy(['reference'=>$day->getReference()]);
                if ($sessionDayCourse) {

                    $sessionDayCourseTitle = $sessionDayCourse->getDescription();

                    $dayCourseTitle = $day->getDescription();
                    if ($sessionDayCourseTitle != $dayCourseTitle) {
                        $resource['day'] = $sessionDayCourseTitle;
                    }
                    else{
                        $resource['day'] =$dayCourseTitle;

                    }
                    $resource['owner']=$resource['owner'].' '.$resource['lastName'];
                    unset($resource['lastName']);
                }
                $resourcesResult[]=$resource;
            }

        }

        return $resourcesResult;
    }

    public function sendEmailProposedResources()
    {
        $proposedResources = $this->getPropostition();
        $body= $this->templating->render('dashboard/emailForPropsedResources.html.twig',['resources'=>$proposedResources

        ]);
        $this->mailer->sendMail('mouna.makni@talan.com','Les ressources proposées',$body);


    }
}
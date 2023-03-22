<?php
/**
 * Created by PhpStorm.
 * User: jghada
 * Date: 06/01/2020
 * Time: 17:23
 */

namespace App\Service;


use App\Entity\ResourceRecommendation;
use App\Entity\SessionResources;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ResourceRecommendationExtension extends AbstractExtension
{
    /**
     * @var EntityManagerInterface
     */
    private $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('resourceScore', [$this, 'getResourceScore']),
            new TwigFunction('checkRecommendation',[$this,'checkRecommendation']),
            new TwigFunction('cursusResourceScore',[$this,'cursusResourceScore'])
        ];
    }

    public function getResourceScore($resource)
    {
        $resourceScore = $this->manager->getRepository(ResourceRecommendation::class)->sumResourceScore($resource);
        if ($resourceScore) {
            return $resourceScore;
        }
            return 0;
    }

    public function checkRecommendation($resource, $user)
    {
        $resourceRecommendation = $this->manager->getRepository(ResourceRecommendation::class)->findOneBy(['apprentice' => $user, 'resource' => $resource]);
        if ($resourceRecommendation) {
            return $resourceRecommendation->getScore();
        }
            return false;
    }


    public function cursusResourceScore($ref)
    {
        $sessionResource = $this->manager->getRepository(SessionResources::class)->findOneBy(['ref'=>$ref]);
        if($sessionResource){
            return $this->getResourceScore($ref);
        }
            return 0;
    }
}
<?php


namespace App\Service;


use App\Entity\PreparcoursCandidate;
use Doctrine\Common\Persistence\ObjectManager;
/**
 * Class OverflowDeadlineService
 * @package App\Service
 * @codeCoverageIgnore
 */

class OverflowDeadlineService
{
    private $manager;

    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    public function verifyOverflowDeadline()
    {
        $today = new \DateTime('now', new \DateTimeZone('Africa/Tunis'));
        $today = $today->format('d-m-Y H:i');
        $candidatesPreparcours = $this->manager->getRepository(PreparcoursCandidate::class)->findAll();

        foreach ($candidatesPreparcours as $candidatePreparcours){
            $startDate = $candidatePreparcours->getStartDate()->format('d-m-Y H:i');
            $deadline = date('d-m-Y H:i', strtotime($startDate. '+ 1 week'));
            $preparcoursStatus = $candidatePreparcours->getStatus();
            if ((strtotime($today) > strtotime($deadline)) && ($preparcoursStatus == $candidatePreparcours::EN_COURS)){
                $candidatePreparcours->setStatus($candidatePreparcours::DEBORDE);
                $this->manager->persist($candidatePreparcours);
                $this->manager->flush();
            }
        }
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: sourajini
 * Date: 17/08/2019
 * Time: 20:33
 */

namespace App\Service;


use App\Entity\Candidature;
use App\Entity\CandidatureState;
use Doctrine\Common\Persistence\ObjectManager;

class CandidatureService
{
public function compareInterviewDateAndToday(Candidature $candidature, ObjectManager $manager)
{
    $notAllowed = false;
    if($candidature->getStatus()==Candidature::INVITE_ENTRETIEN ) {
        $candidatureStates = $manager->getRepository(CandidatureState::class)->findBy(['candidature' => $candidature, 'status' => Candidature::INVITE_ENTRETIEN]);
        $lastCandidatureState = $candidatureStates[count($candidatureStates) - 1];
        $description = $lastCandidatureState->getDescription();
        $date = substr($description, 3, 10) . '' . substr($description, 16, 21);
        $today = new \DateTime();
        if (strtotime($today->format('d-m-Y H:i')) < strtotime($date)) {
            $notAllowed = true;
        }
    }
        return $notAllowed;
    }
}

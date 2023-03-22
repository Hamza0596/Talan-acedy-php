<?php
/**
 * Created by PhpStorm.
 * User: jghada
 * Date: 15/06/2020
 * Time: 11:02
 */

namespace App\EventListener;


use App\Entity\Student;
use App\Repository\SessionUserDataRepository;
use App\Service\CalculateAverageService;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\Security\Core\User\UserInterface;

class AuthenticationSuccessListener
{

    /**
     * @var CalculateAverageService
     */
    private $averageService;
    /**
     * @var SessionUserDataRepository
     */
    private $sessionUserDataRepository;

    public function __construct(SessionUserDataRepository $sessionUserDataRepository, CalculateAverageService $averageService)
    {
        $this->averageService = $averageService;
        $this->sessionUserDataRepository = $sessionUserDataRepository;
    }

    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event)
    {
        $data = $event->getData();
        $user = $event->getUser();

        if (!$user instanceof UserInterface) {
            return;
        }

        $data['data'] = array(
            'roles' => $user->getRoles(),
            'lastName' =>$user->getLastName(),
            'firstName' =>$user->getFirstName(),
            'id' =>$user->getId(),
            'email' =>$user->getUsername(),
            'image' => $user->getImage()
        );
        if ($user instanceof Student){
            $sessionUser = $this->sessionUserDataRepository->findSessionInProgressByUser($user->getId());
            if ($sessionUser) {
                $session = $sessionUser->getSession();
                $data['data']['average'] = $this->averageService->calculateMinMaxScore($session, $user)['average'];
            }
            $data['data']['tel']= $user->getTel();
            $data['data']['linkedin']= $user->getLinkedin();
        }

        $event->setData($data);

    }
}

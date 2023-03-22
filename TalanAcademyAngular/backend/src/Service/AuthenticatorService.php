<?php
/**
 * Created by PhpStorm.
 * User: sarfaoui
 * Date: 29/05/2019
 * Time: 11:07
 */

namespace App\Service;


use App\Entity\Student;
use App\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class AuthenticatorService
{

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getRouteFromUser($user)
    {
        $route = 'showcase';
        if (in_array(User::ROLE_ADMIN, $user->getRoles())) {
            $route = 'dashboard';
        } elseif (in_array(User::ROLE_INSCRIT, $user->getRoles()) ||
            in_array(User::ROLE_CANDIDAT, $user->getRoles()) ) {
            $route = 'list-cursus';
        }
        elseif (in_array(User::ROLE_APPRENTI, $user->getRoles()) && ($user instanceof Student)) {
            $route = 'dashboard-apprenti';
        }elseif (in_array(User::ROLE_MENTOR, $user->getRoles())){
            $route = 'dashboard-mentor';
        }
        return $route;
    }

    public function loginUser($user, $request) {

        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());

        $this->container->get('security.token_storage')->setToken($token);

        $this->container->get('session')->set('_security_main', serialize($token));

        $event = new InteractiveLoginEvent($request, $token);
        $dispatcher = new EventDispatcher();
        $dispatcher->dispatch("security.interactive_login", $event);
    }
}

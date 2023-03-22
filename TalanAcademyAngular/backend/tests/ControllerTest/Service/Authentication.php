<?php
/**
 * Created by PhpStorm.
 * User: jghada
 * Date: 26/04/2019
 * Time: 12:23
 */

namespace App\Tests\ControllerTest\Service;


use App\Entity\User;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;

class Authentication
{
    private function logIn($client)
    {
        $session = $client->getContainer()->get('session');
        $user = $client->getContainer()->get('doctrine')->getRepository(User::class)->findOneByEmail('admin@talan.com');
        $firewall = 'main';
        $token = new PostAuthenticationGuardToken($user, $firewall, $user->getRoles());
        $session->set('_security_'.$firewall, serialize($token));
        $session->save();
        $cookie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->set($cookie);
    }


}
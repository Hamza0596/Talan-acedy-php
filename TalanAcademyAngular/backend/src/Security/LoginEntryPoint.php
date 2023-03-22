<?php
/**
 * Created by PhpStorm.
 * User: sarfaoui
 * Date: 08/08/2019
 * Time: 14:49
 */

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * When the user is not authenticated at all (i.e. when the security context has no token yet),
 * the firewall's entry point will be called to start() the authentication process.
 */

class LoginEntryPoint implements AuthenticationEntryPointInterface
{
    protected $router;

    public function __construct($router)
    {
        $this->router = $router;
    }

    /**
     * This method receives the current Request object and the exception by which the exception
     * listener was triggered.
     *
     * The method should return a Response object
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse('',401);
        }
        return new RedirectResponse($this->router->generate('showcase', ['_fragment' => 'login']));
    }
}
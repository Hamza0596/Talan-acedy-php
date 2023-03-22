<?php
/**
 * Created by PhpStorm.
 * User: sarfaoui
 * Date: 05/07/2019
 * Time: 10:10
 */

namespace App\Security;


use App\Service\AuthenticatorService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;

class AccessDeniedHandler implements AccessDeniedHandlerInterface
{

    /**
     * @var AuthenticatorService
     */
    private $authenticatorService;
    /**
     * @var Security
     */
    private $security;
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    public function __construct(AuthenticatorService $authenticatorService, Security $security, UrlGeneratorInterface $urlGenerator)
    {
        $this->authenticatorService = $authenticatorService;
        $this->security = $security;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * Handles an access denied failure.
     * @param Request $request
     * @param AccessDeniedException $accessDeniedException
     * @return RedirectResponse
     */
    public function handle(Request $request, AccessDeniedException $accessDeniedException)
    {
        if (!$request->isXmlHttpRequest()) {

            if ($user = $this->security->getUser()) {
                $request->getSession()->getFlashBag()->add(
                    'redirectAccessDenied',
                    'true'
                );
                $route = $this->authenticatorService->getRouteFromUser($user);
                return new RedirectResponse($this->urlGenerator->generate($route));
            } else {
                return new RedirectResponse($this->urlGenerator->generate('showcase', ['_fragment' => 'login']));
            }
        }
    }
}
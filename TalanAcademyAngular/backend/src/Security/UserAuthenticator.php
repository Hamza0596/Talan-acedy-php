<?php
/**
 * Created by PhpStorm.
 * User: sarfaoui
 * Date: 27/03/2019
 * Time: 11:26
 */

namespace App\Security;

use App\Controller\Web\SecurityController;
use App\Entity\User;
use App\Service\AuthenticatorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class UserAuthenticator extends AbstractFormLoginAuthenticator
{
    use TargetPathTrait;

    const CODE_ERROR_NOT_ACTIVE = 2;
    private $entityManager;
    private $urlGenerator;
    private $csrfTokenManager;
    private $passwordEncoder;
    private $request;
    private $authenticationUtils;
    /**
     * @var AuthenticatorService
     */
    private $authenticatorService;

    public function __construct(AuthenticationUtils $authenticationUtils,
                                RequestStack $request,
                                EntityManagerInterface $entityManager,
                                UrlGeneratorInterface $urlGenerator,
                                CsrfTokenManagerInterface $csrfTokenManager,
                                UserPasswordEncoderInterface $passwordEncoder,
                                AuthenticatorService $authenticatorService)
    {
        $this->request = $request;
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->authenticationUtils = $authenticationUtils;
        $this->authenticatorService = $authenticatorService;
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function supports(Request $request)
    {
        return 'app_login' === $request->attributes->get('_route')
            && $request->isMethod('POST');
    }

    /**
     * @param Request $request
     * @return array|mixed
     */
    public function getCredentials(Request $request)
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $request->request->get('email')]);
        if ($user) {
            $active = $user->getIsActivated();
            $user->getId();
        } else {
            $active = true;
        }
        $credentials = [
            'email' => $request->request->get('email'),
            'password' => $request->request->get('password'),
            'isActived' => $active,
            'csrf_token' => $request->request->get('_csrf_token'),
        ];
        $request->getSession()->set(
            Security::LAST_USERNAME,
            $credentials['email']
        );
        return $credentials;
    }

    /**
     * @param mixed $credentials
     * @param UserProviderInterface $userProvider
     * @return User|null|object|UserInterface
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $activeStatus = $credentials['isActived'];
        $token = new CsrfToken('authenticate', $credentials['csrf_token']);
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new InvalidCsrfTokenException();
        }
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $credentials['email']]);
        if (!$user) {
            // fail authentication with a custom error
            throw new CustomUserMessageAuthenticationException('Email ou mot de passe incorrect');

        } else if (!$activeStatus) {
            throw new CustomUserMessageAuthenticationException(
                'Votre compte n\'est pas encore actif, veuillez vérifier votre boîte de réception',
                [$user],
                self::CODE_ERROR_NOT_ACTIVE
            );
        }

        return $user;
    }

    /**
     * @param mixed $credentials
     * @param UserInterface $user
     * @return bool
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        if ($check = $this->passwordEncoder->isPasswordValid($user, $credentials['password'])) {
            return $check;
        } else {
            throw new CustomUserMessageAuthenticationException('Email ou mot de passe incorrect');
        }

    }

    /**
     * @param Request $request
     * @param TokenInterface $token
     * @param string $providerKey
     * @return RedirectResponse
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        $route = $this->authenticatorService->getRouteFromUser($token->getUser());
        return new RedirectResponse($this->urlGenerator->generate($route));
    }

    /**
     * @return string
     */
    protected function getLoginUrl()
    {
        $error = $this->authenticationUtils->getLastAuthenticationError();
        if ($error && $error->getCode() === self::CODE_ERROR_NOT_ACTIVE) {
            $user = $error->getMessageData();
            $this->request->getCurrentRequest()->getSession()->set('userId', $user[0]->getId());
            $this->request->getCurrentRequest()->getSession()->getFlashBag()->set(
                'warning-resendConfirmation',
                'Votre compte n\'est pas encore actif, veuillez vérifier votre boîte de réception'
            );

        }else {
            if ($error){
                $this->request->getCurrentRequest()->getSession()->getFlashBag()->set(
                    SecurityController::ERROR,
                    $error->getMessageKey()
                );
            }
        }
        return $this->urlGenerator->generate('showcase', ['_fragment' => 'login']);
    }
}

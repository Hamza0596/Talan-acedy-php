<?php


namespace App\Controller\API\Apprenti;



use App\Repository\UserRepository;
use App\Service\Mailer;
use App\Service\UserService;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;


/**
 * Class ApprentiAPIController
 * @package App\Controller
 * @Rest\Route("/user")
 */

class UserAPIController
{

    /**
     * @var UserRepository
     */
    private $userService;



    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @Rest\Post("/")
     * @param Request $request
     * @return array
     * @Rest\View(serializerGroups={"user"})
     */
    public function inscription(Request $request)
    {
        return $this->userService->inscription($request);
    }

    /**
     * @Rest\Post("/contactUs")
     * @param Request $request
     * @param Mailer $mailer
     * @return array
     * @Rest\View(serializerGroups={"users"})
     */
    public function contactUs(Request $request,Mailer $mailer)
    {
        return $this->userService->contactUs($request,$mailer);
    }

    /**
     * @Rest\Get("/")
     * @Rest\View(serializerGroups={"users"})
     * @param UserRepository $userRepository
     * @return array
     */
    public function fetchAll(UserRepository $userRepository)
    {
       return $userRepository->findAll();

    }
}

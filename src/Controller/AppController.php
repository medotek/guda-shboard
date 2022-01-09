<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AppController extends AbstractController
{
    private UserRepository $userRepository;

    public function __construct(
        UserRepository         $userRepository
    )
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/exists", name="existCredentials")
     */
    public function exist(): JsonResponse
    {
        if (empty($this->userRepository->findOneBy(['name' => 'test']))) {
            return new JsonResponse('exists');
        }
        return new JsonResponse('doesn\'t exist');
    }

    /**
     * @Route("/{entry}", name="app.entry")
     */
    public function app() : Response
    {
        return $this->render('base.html.twig');
    }
}

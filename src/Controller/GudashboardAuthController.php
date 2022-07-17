<?php

namespace App\Controller;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class GudashboardAuthController extends AbstractController
{
    /**
     * @Route("/gudadmin/login", name="gudadmin_login")
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        try {
            $securityContext = $this->container->get('security.authorization_checker');
            /* If the user is logged, then redirect */
            if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
                return $this->redirectToRoute('admin');
            }
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            dump($e);
        }
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('gudashboard_auth/index.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }
}

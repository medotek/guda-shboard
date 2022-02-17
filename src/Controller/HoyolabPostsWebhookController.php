<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HoyolabPostsWebhookController extends AbstractController
{
    /**
     * @Route("/hoyolab/posts/webhook", name="hoyolab_posts_webhook")
     */
    public function index(): Response
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/HoyolabPostsWebhookController.php',
        ]);
    }
}

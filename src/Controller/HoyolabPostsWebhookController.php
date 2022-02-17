<?php

namespace App\Controller;

use App\Entity\HoyolabPost;
use App\Entity\HoyolabPostStats;
use App\Entity\User;
use App\Repository\HoyolabPostRepository;
use App\Repository\HoyolabPostStatsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @Route("/api")
 */
class HoyolabPostsWebhookController extends AbstractController
{
    private HttpClientInterface $client;
    private HoyolabPostRepository $hoyolabPostRepository;
    private Security $security;
    private EntityManagerInterface $entityManager;

    public function __construct(
        HttpClientInterface $client,
        HoyolabPostRepository $hoyolabPostRepository,
        Security $security,
        EntityManagerInterface $entityManager
    )
    {
        $this->client = $client;
        $this->hoyolabPostRepository = $hoyolabPostRepository;
        $this->security = $security;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/hoyolab/post/new/{id}", name="hoyolab_posts_webhook")
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function new(Request $request, int $id): Response
    {
        // single post
        $hoyolabSinglePostUrl = 'https://bbs-api-os.hoyolab.com/community/post/wapi/getPostFull?gids=2&post_id=' . $id . '&read=1';
        // User feed - post list
        $hoyolabListPostUrl = 'https://bbs-api-os.mihoyo.com/community/post/wapi/userPost?size=50&uid=' . $id;

        if (!$request->isMethod('POST') || !$id) {
            return $this->json([
                'error' => 'wrong request method or no id found'
            ], 404);
        }

        $postParams = $request->query->get('post');
        $listParams = $request->query->get('list');

        /** @var User $user */
        $user = $this->security->getUser();
        if (!$user) {
            return $this->json([
                'error' => 'you need to be authenticated'
            ], 404);
        }

        if ($postParams) {
            $response = $this->client->request('GET', $hoyolabSinglePostUrl);
        } else if ($listParams) {
            $response = $this->client->request('GET', $hoyolabListPostUrl);
        }

        if ($response->getStatusCode() === 200) {
            try {
                $post = $response->toArray();

                // Verify if the url is a post
                if ($postParams && array_key_exists('post', $post['data'])) {
                    $postData = $post['data']['post']['post'];
                    $statsData = $post['data']['post']['stat'];
                    $existsHoyolabPosts = $this->hoyolabPostRepository->find($id);

                    if ($existsHoyolabPosts) {
                        return $this->json([
                            'error' => 'L\'Article existe déjà dans notre base de données'
                        ], 400);
                    }
                    $this->setHoyolabPostEntity($postData, $statsData, $user);
                } else if ($listParams && array_key_exists('list', $post['data'])){
                    if (!empty($list = $post['data']['list'])) {
                        foreach($list as $post) {
                            $postData = $post['post'];
                            $statsData = $post['stat'];
                            if ((int) $postData['post_id'] &&
                                !$this->hoyolabPostRepository->find((int) $postData['post_id'])
                            ) {
                                $this->setHoyolabPostEntity($postData, $statsData, $user);
                            }
                        }
                    }
                }

                $this->entityManager->flush();

                return $this->json([
                    'message' => 'success'
                ]);

            } catch (
                ClientExceptionInterface|
                RedirectionExceptionInterface|
                ServerExceptionInterface|
                DecodingExceptionInterface|
                TransportExceptionInterface $e
            ) {
            }

        }

        return $this->json([
            'error' => 'No response from hoyolab, maybe the post doesn\'t exist'
        ], 400);
    }

    /**
     * @param $postData
     * @param $statsData
     * @param $user
     * @return void
     * @throws \Exception
     */
    public function setHoyolabPostEntity($postData, $statsData, $user) {
        $hoyolabPost = new HoyolabPost();
        $hoyolabPostStats = new HoyolabPostStats();

        // Hoyolab Post Stats
        $hoyolabPostStats->setLikes($statsData['like_num']);
        $hoyolabPostStats->setBookmark($statsData['bookmark_num']);
        $hoyolabPostStats->setReply($statsData['reply_num']);
        $hoyolabPostStats->setShare($statsData['share_num']);
        $hoyolabPostStats->setView($statsData['view_num']);

        // Hoyolab Post
        $hoyolabPost->setUser($user);
        $hoyolabPost->setCreationDate(new \DateTime());
        $hoyolabPost->setPostCreationDate((new \DateTime())->setTimestamp((int) $postData['created_at']));
        if ($postData['reply_time'])
            $hoyolabPost->setLastReplyTime((new \DateTime($postData['reply_time'])));
        $hoyolabPost->setPostId($postData['post_id']);
        $hoyolabPost->setSubject($postData['subject']);
        $hoyolabPost->setHoyolabPostStats($hoyolabPostStats);

        $this->entityManager->persist($hoyolabPost);
        $this->entityManager->persist($hoyolabPostStats);
    }
}

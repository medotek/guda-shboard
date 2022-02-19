<?php

namespace App\Controller;

use App\Entity\HoyolabPost;
use App\Entity\HoyolabPostStats;
use App\Entity\HoyolabPostUser;
use App\Entity\User;
use App\Repository\HoyolabPostRepository;
use App\Repository\HoyolabPostStatsRepository;
use App\Repository\HoyolabPostUserRepository;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;
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
    private HoyolabPostUserRepository $hoyolabPostUserRepository;
    private UserRepository $userRepository;
    private SerializerInterface $serializer;

    public function __construct(
        HttpClientInterface    $client,
        HoyolabPostRepository  $hoyolabPostRepository,
        Security               $security,
        EntityManagerInterface $entityManager,
        HoyolabPostUserRepository $hoyolabPostUserRepository,
        UserRepository         $userRepository,
        SerializerInterface $serializer
    )
    {
        $this->client = $client;
        $this->hoyolabPostRepository = $hoyolabPostRepository;
        $this->security = $security;
        $this->entityManager = $entityManager;
        $this->hoyolabPostUserRepository = $hoyolabPostUserRepository;
        $this->userRepository = $userRepository;
        $this->serializer = $serializer;
    }

    /**
     * Get all hoyo users for a guda user
     * @Route("/hoyolab/users",name="hoyolab_posts_users")
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function getHoyolabUsers(): Response
    {
        /** @var User $user */
        $user = $this->security->getUser();
        $data = $this->serializer->normalize($user, null, ['groups' => ['user', 'hoyolab_post_user']]);

        if ($user) {
            return $this->json($data['hoyolabPostUsers']);
        }

        return $this->json('error', 400);
    }

    /**
     * Get all stats from the user's hoyo posts
     * @Route("/hoyolab/user/{uid}/posts",name="hoyolab_user_posts")
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function getPostsListByHoyoUid(int $uid): Response
    {
        /** @var User $user */
        $user = $this->security->getUser();
        if ($user) {
            $dql = "SELECT hp
                    FROM App:HoyolabPost hp
                    INNER JOIN App:HoyolabPostUser hpu WITH hp.hoyolabPostUser = hpu.id
                    INNER JOIN App:User u WITH hpu.user = :user
                    WHERE hpu.uid = :uid
                    ";

            $query = $this->entityManager->createQuery($dql)
                ->setParameter(':uid', $uid)
                ->setParameter(':user', $user->getId())
                ->setFirstResult(0)
                ->setMaxResults(20);

            $paginator = new Paginator($query, $fetchJoinCollection = true);

            $data = $this->serializer->normalize($paginator, null, ['groups' => ['hoyolab_post_user']]);

            return $this->json($data);
        }
        return $this->json('You need to be authenticated', 400);
    }

    /**
     * Get all stats from the user's hoyo posts
     * @Route("/hoyolab/user/{uid}/stats",name="hoyolab_posts_stats")
     */
    public function getStatsPerHoyoUser(int $uid): Response
    {
        /** @var User $user */
        $user = $this->security->getUser();
        $stats = [
            'view' => 0,
            'bookmark' => 0,
            'like' => 0,
            'share' => 0,
            'reply' => 0,
        ];

        if ($user) {
            /** @var HoyolabPostUser $hoyolabPostUser */
            $hoyolabPostUser = $this->hoyolabPostUserRepository->findOneBy(['user' => $user, 'uid' => $uid]);
            $arrayHoyolabPosts = new ArrayCollection($hoyolabPostUser->getHoyolabPosts()->toArray());
            foreach($arrayHoyolabPosts->toArray() as $hoyolabPost) {
                /** @var HoyolabPost $hoyolabPost */
                $hoyoPostStat = $hoyolabPost->getHoyolabPostStats();
                $stats['view'] += $hoyoPostStat->getView();
                $stats['bookmark'] += $hoyoPostStat->getBookmark();
                $stats['like'] += $hoyoPostStat->getLikes();
                $stats['share'] += $hoyoPostStat->getShare();
                $stats['reply'] += $hoyoPostStat->getReply();
            }

            return $this->json($stats);
        }
        return $this->json('You need to be authenticated', 400);
    }

    /**
     * @Route("/hoyolab/post/new/{id}", name="hoyolab_posts_webhook")
     * @throws TransportExceptionInterface
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

                $i = 0;
                // Verify if the url is a post
                if ($postParams && array_key_exists('post', $post['data'])) {
                    $postData = $post['data']['post']['post'];
                    $statsData = $post['data']['post']['stat'];
                    $userData = $post['data']['post']['user'];
                    $imageData = $post['data']['post']['image_list'];
                    $existsHoyolabPosts = $this->hoyolabPostRepository->findOneBy(['postId' => $id]);

                    if ($existsHoyolabPosts) {
                        return $this->json([
                            'error' => 'L\'Article existe déjà dans notre base de données'
                        ], 400);
                    }
                    $this->setHoyolabPostEntity($postData, $statsData, $user, $userData, $imageData);
                } else if ($listParams && array_key_exists('list', $post['data'])) {
                    // Loop
                    if (!empty($list = $post['data']['list'])) {
                        foreach ($list as $post) {
                            $postData = $post['post'];
                            $statsData = $post['stat'];
                            $userData = $post['user'];
                            $imageData = $post['image_list'];
                            if ((int)$postData['post_id'] && !$this->hoyolabPostRepository->findOneBy(['postId' => $postData['post_id']])) {
                                $this->setHoyolabPostEntity($postData, $statsData, $user, $userData, $imageData);
                                $i++;
                            }
                        }
                    }
                }

                $this->entityManager->flush();

                return $this->json([
                    'message' => 'success',
                    'count' => $i
                ]);

            } catch (
                ClientExceptionInterface|
                RedirectionExceptionInterface|
                ServerExceptionInterface|
                DecodingExceptionInterface|
                TransportExceptionInterface|\Exception $e
            ) {
                return $this->json([
                    $e
                ], 500);
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
     * @param $hoyoUser
     * @return void
     * @throws \Exception
     */
    public function setHoyolabPostEntity($postData, $statsData, $user, $hoyoUser, $image)
    {
        $hoyolabPost = new HoyolabPost();
        $hoyolabPostStats = new HoyolabPostStats();
        $hoyolabPostUser = new HoyolabPostUser();

        /** @var HoyolabPostUser $existHoyolabPostUser */
        /** @var User $user */
        $existHoyolabPostUser = $this->hoyolabPostUserRepository->findOneBy(['uid' => $hoyoUser['uid'], 'user' => $user]);

        $hoyolabPostUserCreated = false;
        // Hoyolab Post User
        if (!$existHoyolabPostUser) {
            $hoyolabPostUser->setUid($hoyoUser['uid']);
            $hoyolabPostUser->setNickname($hoyoUser['nickname']);
            $hoyolabPostUser->setPendant($hoyoUser['pendant']);
            $hoyolabPostUser->setAvatarUrl($hoyoUser['avatar_url']);
            $hoyolabPostUser->setUser($user);
            $hoyolabPost->setHoyolabPostUser($hoyolabPostUser);
            $hoyolabPostUserCreated = true;
        } else {
            $hoyolabPost->setHoyolabPostUser($existHoyolabPostUser);
        }

        // Hoyolab Post Stats
        $hoyolabPostStats->setLikes($statsData['like_num']);
        $hoyolabPostStats->setBookmark($statsData['bookmark_num']);
        $hoyolabPostStats->setReply($statsData['reply_num']);
        $hoyolabPostStats->setShare($statsData['share_num']);
        $hoyolabPostStats->setView($statsData['view_num']);

        // Hoyolab Post
        $hoyolabPost->setCreationDate(new \DateTime());
        $hoyolabPost->setPostCreationDate((new \DateTime())->setTimestamp((int)$postData['created_at']));
        if ($postData['reply_time'])
            $hoyolabPost->setLastReplyTime((new \DateTime($postData['reply_time'])));
        if (!empty($image))
            $hoyolabPost->setImage($image[0]['url']);
        $hoyolabPost->setPostId($postData['post_id']);
        $hoyolabPost->setSubject($postData['subject']);
        $hoyolabPost->setHoyolabPostStats($hoyolabPostStats);

        $this->entityManager->persist($hoyolabPost);
        $this->entityManager->persist($hoyolabPostStats);
        if ($hoyolabPostUserCreated) {
            $this->entityManager->persist($hoyolabPostUser);
        }
        $this->entityManager->flush();
    }
}

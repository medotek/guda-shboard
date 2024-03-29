<?php

namespace App\Controller;

use App\Contract\Encryption\EncryptionManager;
use App\Contract\Stats\TaxonomyInterface;
use App\Entity\HoyolabPost;
use App\Entity\HoyolabPostStats;
use App\Entity\HoyolabPostUser;
use App\Entity\HoyolabStats;
use App\Entity\User;
use App\Repository\HoyolabPostRepository;
use App\Repository\HoyolabPostStatsRepository;
use App\Repository\HoyolabPostUserRepository;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
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
    private LoggerInterface $logger;

    public function __construct(
        HttpClientInterface       $client,
        HoyolabPostRepository     $hoyolabPostRepository,
        Security                  $security,
        EntityManagerInterface    $entityManager,
        HoyolabPostUserRepository $hoyolabPostUserRepository,
        UserRepository            $userRepository,
        SerializerInterface       $serializer,
        LoggerInterface           $logger
    )
    {
        $this->client = $client;
        $this->hoyolabPostRepository = $hoyolabPostRepository;
        $this->security = $security;
        $this->entityManager = $entityManager;
        $this->hoyolabPostUserRepository = $hoyolabPostUserRepository;
        $this->userRepository = $userRepository;
        $this->serializer = $serializer;
        $this->logger = $logger;
    }

    /**
     * Get all hoyo users for a guda user
     * @Route("/hoyolab/users",name="hoyolab_posts_users")
     * @throws ExceptionInterface
     */
    public function getHoyolabUsers(): Response
    {
        /** @var User $user */
        $user = $this->security->getUser();

        if ($user) {
            $data = $this->serializer->normalize($user, null, ['groups' => ['user', 'hoyolab_post_user']]);
            return $this->json($data['hoyolabPostUsers']);
        }

        return $this->json('error', 400);
    }


    /**
     * Get all hoyo users for a guda user
     * @Route("/hoyolab/user/{uid}",name="hoyolab_posts_user")
     * @throws ExceptionInterface
     */
    public function getHoyolabUser(int $uid): Response
    {
        /** @var User $user */
        $user = $this->security->getUser();

        if ($user) {
            $hoyoUser = $this->hoyolabPostUserRepository->findOneBy(['user' => $user, 'uid' => $uid]);
            $data = $this->serializer->normalize($hoyoUser, null, ['groups' => ['user', 'hoyolab_post_user', 'hoyolab_post_user_detail']]);

            return $this->json($data);
        }

        return $this->json('error', 400);
    }


    /**
     * Get all stats from the user's hoyo posts
     * @Route("/hoyolab/user/{uid}/posts",name="hoyolab_user_posts")
     * @throws ExceptionInterface
     */
    public function getPostsListByHoyoUid(Request $request, string $uid): Response
    {
        $page = (int)$request->query->get('page', 1);

        /** @var User $user */
        $user = $this->security->getUser();
        if ($user) {
            //set page size
            $pageSize = '20';

            $dql = "SELECT hp
                    FROM App:HoyolabPost hp
                    INNER JOIN App:HoyolabPostUser hpu WITH hp.hoyolabPostUser = hpu.id
                    INNER JOIN App:User u WITH hpu.user = :user
                    WHERE hpu.uid = :uid
                    ORDER BY hp.postCreationDate DESC
                    ";

            $query = $this->entityManager->createQuery($dql)
                ->setParameter(':uid', (int)$uid)
                ->setParameter(':user', $user->getId());

            $paginator = new Paginator($query, true);

            // you can get total items
            $totalItems = count($paginator);

            // get total pages
            $pagesCount = ceil($totalItems / $pageSize);

            $paginator
                ->getQuery()
                ->setFirstResult($pageSize * ($page - 1)) // set the offset
                ->setMaxResults($pageSize);;

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
            'views' => 0,
            'bookmarks' => 0,
            'likes' => 0,
            'shares' => 0,
            'replies' => 0,
            'posts' => 0
        ];

        if ($user) {
            /** @var HoyolabPostUser $hoyolabPostUser */
            $hoyolabPostUser = $this->hoyolabPostUserRepository->findOneBy(['user' => $user, 'uid' => $uid]);
            $arrayHoyolabPosts = new ArrayCollection($hoyolabPostUser->getHoyolabPosts()->toArray());
            foreach ($arrayHoyolabPosts->toArray() as $key => $hoyolabPost) {
                /** @var HoyolabPost $hoyolabPost */
                $hoyoPostStat = $hoyolabPost->getHoyolabPostStats();
                $stats['views'] += $hoyoPostStat->getView();
                $stats['bookmarks'] += $hoyoPostStat->getBookmark();
                $stats['likes'] += $hoyoPostStat->getLikes();
                $stats['shares'] += $hoyoPostStat->getShare();
                $stats['replies'] += $hoyoPostStat->getReply();
                $stats['posts'] = $key + 1;
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
     * @param $image
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
        $hoyolabPostStats->setLikes($statsData[TaxonomyInterface::LIKES_MAPPING]);
        $hoyolabPostStats->setBookmark($statsData[TaxonomyInterface::BOOKMARKS_MAPPING]);
        $hoyolabPostStats->setReply($statsData[TaxonomyInterface::REPLIES_MAPPING]);
        $hoyolabPostStats->setShare($statsData[TaxonomyInterface::SHARES_MAPPING]);
        $hoyolabPostStats->setView($statsData[TaxonomyInterface::VIEWS_MAPPING]);

        // Init stats
        $stat = new HoyolabStats();
        $stat->setDate(new \DateTime());
        $stat->setView($statsData[TaxonomyInterface::VIEWS_MAPPING]);
        $stat->setLikes($statsData[TaxonomyInterface::LIKES_MAPPING]);
        $stat->setReply($statsData[TaxonomyInterface::REPLIES_MAPPING]);
        $stat->setShare($statsData[TaxonomyInterface::SHARES_MAPPING]);
        $stat->setBookmark($statsData[TaxonomyInterface::BOOKMARKS_MAPPING]);
        $stat->setHoyolabPost($hoyolabPost);

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

        $this->entityManager->persist($stat);
        $this->entityManager->persist($hoyolabPost);
        $this->entityManager->persist($hoyolabPostStats);
        if ($hoyolabPostUserCreated) {
            $this->entityManager->persist($hoyolabPostUser);
        }
        $this->entityManager->flush();
    }

    /**
     * Add or modify hoyolab webhook cronjob
     * @Route("/hoyolab/user/{uid}/webhookcronjob", name="hoyolab_user_cronjob")
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function cronWebhookUrl(Request $request, int $uid): Response
    {
        /** @var User $user */
        $user = $this->security->getUser();
        $jsonData = json_decode($request->getContent());
        if (!$user || !$jsonData->webhookUrl) {
            return $this->json('error', 400);
        }

        /** @var HoyolabPostUser $hoyoUser */
        $hoyoUser = $this->hoyolabPostUserRepository->findOneBy(['user' => $user, 'uid' => $uid]);
        $decryptedExistingUrl = EncryptionManager::decrypt($hoyoUser->getWebhookUrl(), $user->getCreationDate()->getTimestamp());
        if ($decryptedExistingUrl === $jsonData->webhookUrl) {
            return $this->json('Same webhook url', 400);
        }

        try {
            $discordRequest = $this->client->request('GET', $jsonData->webhookUrl);
            $statusCode = $discordRequest->getStatusCode();
            if ($statusCode !== 200) {
                return $this->json('This is not a discord webhook', 400);
            }
            $content = $discordRequest->toArray();
            if (array_key_exists('guild_id', $content) &&
                array_key_exists('token', $content) &&
                array_key_exists('channel_id', $content) &&
                array_key_exists('avatar', $content) &&
                array_key_exists('id', $content) &&
                array_key_exists('name', $content)
            ) {
                $hoyoUser->setWebhookUrl(EncryptionManager::encrypt($jsonData->webhookUrl, $user->getCreationDate()->getTimestamp()));
                $this->entityManager->persist($hoyoUser);
                $this->entityManager->flush();
            }
            return $this->json('Webhook added successfully for the hoyo user');
        } catch (ClientExceptionInterface|DecodingExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface|TransportExceptionInterface $e) {
        }

        return $this->json("Couldn't modify or add the webhook in the database", 400);
    }


    /**
     * Update the posts list of all hoyolab users
     * @return void
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     * @throws \Exception
     */
    public function updateHoyolabUserPostsList()
    {
        $hoyoUsers = $this->hoyolabPostUserRepository->findAll();
        $arrayHoyoUsers = new ArrayCollection($hoyoUsers);
        /** @var HoyolabPostUser $hoyoUser */
        foreach ($arrayHoyoUsers->toArray() as $hoyoUser) {
            $hoyolabListPostUrl = 'https://bbs-api-os.mihoyo.com/community/post/wapi/userPost?size=10&uid=' . $hoyoUser->getUid();
            $response = $this->client->request('GET', $hoyolabListPostUrl);

            if ($response->getStatusCode() === 200) {
                try {
                    $postList = $response->toArray()['data']['list'];
                } catch (ClientExceptionInterface|DecodingExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface|TransportExceptionInterface $e) {
                    dump($e);
                    return;
                }
            } else {
                return;
            }

            // Verify unexisting posts
            foreach ($postList as $post) {
                $statsData = $post['stat'];
                $userData = $post['user'];
                $imageData = $post['image_list'];

                if (!$this->hoyolabPostRepository->findOneBy(['postId' => $post['post']['post_id']])) {
                    $this->setHoyolabPostEntity($post['post'], $statsData, $hoyoUser->getUser(), $userData, $imageData);
                }
            }
        }

    }
}

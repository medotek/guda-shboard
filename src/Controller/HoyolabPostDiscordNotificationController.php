<?php

namespace App\Controller;

use App\Entity\HoyolabPost;
use App\Entity\HoyolabPostDiscordNotification;
use App\Entity\HoyolabPostStats;
use App\Entity\HoyolabPostUser;
use App\Entity\User;
use App\Repository\HoyolabPostRepository;
use App\Repository\HoyolabPostUserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Spatie\Async\Pool;

/**
 * @Route("/api")
 */
class HoyolabPostDiscordNotificationController extends AbstractController
{
    private HoyolabPostUserRepository $hoyolabPostUserRepository;
    private EncryptionManagerController $encryptionManager;
    private HttpClientInterface $client;
    private EntityManagerInterface $entityManager;
    private int $counter1 = 0;
    private int $counter2 = 0;
    private int $errorCounter = 0;
    private array $arr;

    public function __construct(
        HoyolabPostUserRepository   $hoyolabPostUserRepository,
        EncryptionManagerController $encryptionManager,
        HttpClientInterface         $client,
        EntityManagerInterface      $entityManager
    )
    {
        $this->hoyolabPostUserRepository = $hoyolabPostUserRepository;
        $this->encryptionManager = $encryptionManager;
        $this->client = $client;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/hoyolab/cron/update/all", name="first_cron")
     * @throws Exception
     * @throws TransportExceptionInterface
     */
    public function discordNotificationCron()
    {
        $poolUsers = Pool::create();
        $poolUsers
            // Execute 10 per 10
            ->concurrency(10)
            // Wait 2s before starting the next 10
            ->sleepTime(2000);

        $allHoyoUsers = $this->hoyolabPostUserRepository->findAll();
        $arrayHoyoUsers = new ArrayCollection($allHoyoUsers);

        /** @var HoyolabPostUser $hoyoUser */
        foreach ($arrayHoyoUsers->toArray() as $hoyoUser) {
            // If there is no webhook setup, skip the current iteration
            if (!$hoyoUser->getWebhookUrl()) {
                continue;
            }

            // Multithreading
            $poolUsers->add(function () use ($hoyoUser) {
                return $this->processHoyolabUserForPostsNotification($hoyoUser);
            })
                ->then(function ($output) {
                    $this->counter1 += (int)$output;
                })->catch(function ($exception) {
                    $this->errorCounter++;
                    dump($exception);
                    // When an exception is thrown from within a process, it's caught and passed here.
                });
            // TODO : Logger

            dump($this->counter1 . ' users treated');
        }
        $poolUsers->wait();
        // Await asynchronous task
        dump($this->arr);
        // TODO : Maybe make it async
        // Treat discord notification
        $this->embedNotification($this->arr);

        dump($poolUsers->getFinished());
        if (empty($poolUsers->getFailed())) {
            // $this->entityManager->flush();
            return $this->json(['success' => $this->counter1, 'posts' => $this->counter2]);
        }

        return $this->json(['error' => $this->errorCounter], 500);
    }


    /**
     * @param $hoyoUser
     * @return false
     */
    private function processHoyolabUserForPostsNotification($hoyoUser): bool
    {
        /** @var HoyolabPostUser $hoyoUser */
        dump($hoyoUser->getUid());

        // Hoyo Posts
        $postEmbedData = [];
        // No posts
        if (empty($hoyoUser->getHoyolabPosts()->toArray())) {
            return false;
        }

        $arrayHoyoPosts = new ArrayCollection($hoyoUser->getHoyolabPosts()->toArray());
        /** @var HoyolabPost $hoyoPost */
        $poolPosts = Pool::create();
        $poolPosts  // Execute 10 per 10
        ->concurrency(10);

        $this->counter2 = 0;

        foreach ($arrayHoyoPosts->toArray() as $i => $hoyoPost) {
            $poolPosts->add(function () use ($hoyoPost, $postEmbedData) {
                return $this->processHoyolabPostsNotification($hoyoPost, $postEmbedData);
            })->then(function ($output) use ($hoyoUser) {
                /** @var HoyolabPostUser $hoyoUser */
                $this->counter2 += !empty($output) ? 1 : 0;
                if (!empty($output)) {
                    $this->arr[$hoyoUser->getUid()][] = $output;
                }
            });
        }
        $poolPosts->wait();
        // TODO : send discord notification after treating hoyo accounts -> $this->arr[uid]
        
        // TODO : Logger
        dump($this->counter2 . ' Posts notifiés pour l\'uid ' . $hoyoUser->getUid());
        // Flush
        return true;
    }

    /**
     * @param $hoyoPost
     * @param $postEmbedData
     * @return array
     * @throws TransportExceptionInterface
     */
    private function processHoyolabPostsNotification($hoyoPost, $postEmbedData): array
    {
        /** @var HoyolabPost $hoyoPost */
        $newStats = $hoyoPost->getHoyolabPostStats();
        $discordNotification = $hoyoPost->getHoyolabPostDiscordNotification();

        $post = $this->updateHoyolabPost($hoyoPost->getPostId());
        $oldStats = [];
        $statsData = [];
        // Update the hoyo post here
        if (array_key_exists('post', $post['data'])) {
            $postData = $post['data']['post']['post'];
            $statsData = $post['data']['post']['stat'];

            // Don't update if there is no new replies
            dump($hoyoPost->getPostId());
            if ((int)$statsData['reply_num'] === $newStats->getReply()) {
                return [];
            }

            $oldStats = [
                'like_num' => $newStats->getLikes(),
                'view_num' => $newStats->getView(),
                'bookmark_num' => $newStats->getBookmark(),
                'share_num' => $newStats->getShare(),
                'reply_num' => $newStats->getReply()
            ];

            // Hoyolab Post Stats
            $newStats->setLikes($statsData['like_num']);
            $newStats->setBookmark($statsData['bookmark_num']);
            $newStats->setReply($statsData['reply_num']);
            $newStats->setShare($statsData['share_num']);
            $newStats->setView($statsData['view_num']);

            if ($postData['reply_time'])
                $hoyoPost->setLastReplyTime((new \DateTime($postData['reply_time'])));
            $hoyoPost->setSubject($postData['subject']);

            $this->entityManager->persist($hoyoPost);
            // Only persist the new stats
            $this->entityManager->persist($newStats);
        }

        $diffView = (int)$statsData['view_num'] - $oldStats['view_num'];
        $diffView ? ($diffView = " **+{$diffView}**") : $diffView = "";

        $diffBookmark = (int)$statsData['bookmark_num'] - $oldStats['bookmark_num'];
        $diffBookmark ? $diffBookmark = " **+{$diffBookmark}**" : $diffBookmark = "";

        $diffLike = (int)$statsData['like_num'] - $oldStats['like_num'];
        $diffLike ? $diffLike = " **+{$diffLike}**" : $diffLike = "";

        $diffShare = (int)$statsData['share_num'] - $oldStats['share_num'];
        $diffShare ? $diffShare = " **+{$diffShare}**" : $diffShare = "";

        $diffReply = (int)$statsData['reply_num'] - $oldStats['reply_num'];
        $diffReply ? $diffReply = " **+{$diffReply}**" : $diffReply = "";

        // Compare cron stats with updated stats
        $statistics = [
            'view' => $oldStats['view_num'] . $diffView,
            'bookmark' => $oldStats['bookmark_num'] . $diffBookmark,
            'like' => $oldStats['like_num'] . $diffLike,
            'share' => $oldStats['share_num'] . $diffShare,
            'reply' => $oldStats['reply_num'] . $diffReply,
        ];

        // Prepare embed data
        $postEmbedData = [
            'postId' => $hoyoPost->getPostId(),
            'news' => $newStats->getReply() - $oldStats['reply_num'],
            'subject' => $hoyoPost->getSubject(),
            'stats' => $statistics,
            'postCreationDate' => $hoyoPost->getPostCreationDate(),
            'hoyoUserImage' => $hoyoPost->getImage()
        ];

        dump($postEmbedData);
        // If the post never has a notification message on discord, then create one!
        if (!$discordNotification) {
            $discordNotification = new HoyolabPostDiscordNotification();
            $discordNotification->setHoyolabPost($hoyoPost);
        }
        $discordNotification->setProcessDate(new \DateTime());
        $this->entityManager->persist($discordNotification);

        // To increment
        return $postEmbedData;
    }

    /**
     * Send discord notification
     * @param $array
     * @return void
     * @throws TransportExceptionInterface
     */
    private function embedNotification($array): void
    {
        if (!is_array($array) || empty($array)) {
            return;
        }

        // TODO : ? maybe make it async with pool
        foreach ($array as $uid => $userPosts) {
            // On estime qu'un compte hoyolab n'est uniquement lié qu'a un utilisateur
            /** @var HoyolabPostUser $hoyoUser */
            $hoyoUser = $this->hoyolabPostUserRepository->findOneBy(['uid' => $uid]);
            // Get user key to decrypt the webhookUrl
            $userKey = $hoyoUser->getUser()->getCreationDate()->getTimestamp();
            $webhookUrl = $this->encryptionManager->decrypt($hoyoUser->getWebhookUrl(), $userKey);
            // Eviter trop d'envoi de message discord dans le webhook, on groupe par 10
            $embedsGroups = array_chunk($userPosts, 10);

            // groups of 10
            $messages = [];
            foreach ($embedsGroups as $embedsGroup) {

                // Treat 10 values
                $message['embeds'] = [];
                foreach ($embedsGroup as $embed) {
                    $message['embeds'][] = $this->embed($embed);
                }

                $messages[] = $message;
            }

            dump($messages);

            foreach ($messages as $send) {
                $response = $this->client->request('POST', $webhookUrl . '?wait=true', [
                    'headers' => [
                        'Content-Type: application/json',
                        'Accept: application/json',
                    ],
                    'body' => json_encode($send)
                ]);

                if ($response->getStatusCode() === 200) {
                    try {
                        continue;
                    } catch (ClientExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface|TransportExceptionInterface $e) {
                        // TODO : logger
                        dump($e);
                    }
                } else {
                    dump('error');
                }
            }
        }
    }

    /**
     * Return a single embed array
     * @param $embed
     * @return array
     */
    private function embed($embed): array
    {
        $s = '';
        $x = '';
        if ($embed['news'] > 1) {
            $s = 's';
            $x = 'x';
        }

        $desc = "Vous avez **{$embed['news']}** nouveau{$x} message{$s} sur ce post hoyo";

        return [
            "color" => 6651640,
            "title" => $embed['subject'],
            "url" => "https://hoyolab.com/article/{$embed['postId']}",
            "description" => $desc,
            "fields" => [
                [
                    "name" => "**Views**",
                    "value" => $embed['stats']['view'],
                    "inline" => true
                ],
                [
                    "name" => "**Replies**",
                    "value" => $embed['stats']['reply'],
                    "inline" => true
                ],
                [
                    "name" => "**Likes**",
                    "value" => $embed['stats']['like'],
                    "inline" => true
                ]
            ],
            "thumbnail" => [
                "url" => $embed['hoyoUserImage']
            ]
//            "timestamp" => $embed['postCreationDate']
        ];
    }

    /**
     * @return array
     * Fetch hoyolab article data
     * @throws TransportExceptionInterface
     */
    private function updateHoyolabPost(int $id): array
    {
        $hoyolabArticle = 'https://bbs-api-os.hoyolab.com/community/post/wapi/getPostFull?gids=2&post_id=' . $id . '&read=1';

        $response = $this->client->request('GET', $hoyolabArticle);
        if ($response->getStatusCode() === 200) {
            try {
                return $response->toArray();
            } catch (ClientExceptionInterface|DecodingExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface|TransportExceptionInterface $e) {
                // TODO : logger
                dump($e);
            }
        }
        return ['error' => []];
    }
}

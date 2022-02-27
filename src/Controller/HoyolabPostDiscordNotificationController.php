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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class HoyolabPostDiscordNotificationController extends AbstractController
{
    private HoyolabPostUserRepository $hoyolabPostUserRepository;
    private EncryptionManagerController $encryptionManager;
    private HttpClientInterface $client;
    private EntityManagerInterface $entityManager;

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
     * @throws \Exception
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function discordNotificationCron(): void
    {
        $allHoyoUsers = $this->hoyolabPostUserRepository->findAll();
        $arrayHoyoUsers = new ArrayCollection($allHoyoUsers);

        /** @var HoyolabPostUser $hoyoUser */
        foreach ($arrayHoyoUsers->toArray() as $hoyoUser) {
            // If there is no webhook setup, skip the current iteration
            if (!$hoyoUser->getWebhookUrl()) {
                continue;
            }

            // Get user key to decrypt the webhookUrl
            $userKey = $hoyoUser->getUser()->getCreationDate()->getTimestamp();
            $webhookUrl = $this->encryptionManager->decrypt($hoyoUser->getWebhookUrl(), $userKey);

            // Hoyo Posts
            $postEmbedData = [];
            $arrayHoyoPosts = new ArrayCollection($hoyoUser->getHoyolabPosts()->toArray());
            /** @var HoyolabPost $hoyoPost */
            foreach ($arrayHoyoPosts->toArray() as $hoyoPost) {
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
                    if ((int)$statsData['reply_num'] === $newStats->getReply()) {
                        continue;
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
                $postEmbedData[] = [
                    'postId' => $hoyoPost->getPostId(),
                    'news' => $newStats->getReply() - $oldStats['reply_num'],
                    'subject' => $hoyoPost->getSubject(),
                    'stats' => $statistics,
                    'postCreationDate' => $hoyoPost->getPostCreationDate(),
                    'hoyoUserImage' => $hoyoPost->getImage()
                ];

                // If the post never has a notification message on discord, then create one!
                if (!$discordNotification) {
                    $discordNotification = new HoyolabPostDiscordNotification();
                    $discordNotification->setHoyolabPost($hoyoPost);
                    $discordNotification->setProcessDate(new \DateTime());
                    $this->entityManager->persist($discordNotification);
                }

            }

            // Treat discord notification
            $this->embedNotification($webhookUrl, $postEmbedData);

            // Flush
            $this->entityManager->flush();
        }
    }


    /**
     * Send discord notification
     * @param $webhook
     * @param $embeds
     * @return void
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    private function embedNotification($webhook, $embeds): void
    {
        if (!is_array($embeds) || empty($embeds)) {
            return;
        }

        $embedsGroups = array_chunk($embeds, 10);

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

        foreach ($messages as $send) {
            $response = $this->client->request('POST', $webhook . '?wait=true', [
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
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
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

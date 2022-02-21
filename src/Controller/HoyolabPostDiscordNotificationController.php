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
        EntityManagerInterface $entityManager
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
                $oldStats = $hoyoPost->getHoyolabPostStats();
                $discordNotification = $hoyoPost->getHoyolabPostDiscordNotification();

                $post = $this->updateHoyolabPost($hoyoPost->getPostId());

                // Update the hoyo post here
                if (array_key_exists('post', $post['data'])) {
                    $postData = $post['data']['post']['post'];
                    $statsData = $post['data']['post']['stat'];

                    // Don't update if there is no new replies
                    if ((int)$statsData['reply_num'] === $oldStats->getReply()) {
                        continue;
                    }

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

                // Compare cron stats with updated stats
                $statistics = [
                    'view' => "{$oldStats->getView()} **" . ($newStats->getView() - $oldStats->getView()) ."**",
                    'bookmark' => "{$oldStats->getBookmark()} **" . ($newStats->getBookmark() - $oldStats->getBookmark()) ."**",
                    'like' => "{$oldStats->getLikes()} **" . ($newStats->getLikes() - $oldStats->getLikes()) ."**",
                    'share' => "{$oldStats->getShare()} **" . ($newStats->getShare() - $oldStats->getShare()) ."**",
                    'reply' => "{$oldStats->getReply()} **" . ($newStats->getReply() - $oldStats->getReply()) ."**",
                ];

                // Prepare embed data
                $postEmbedData[] = [
                    'postId' => $hoyoPost->getPostId(),
                    'news' => $newStats->getReply() - $oldStats->getReply(),
                    'subject' => $hoyoPost->getSubject(),
                    'stats' => $statistics,
                    'postCreationDate' => $hoyoPost->getPostCreationDate()
                ];

                // If the post never has a notification message on discord, then create one!
                if (!$discordNotification) {
                    $discordNotification = new HoyolabPostDiscordNotification();
                    $discordNotification->setHoyolabPost($hoyoPost);
                    $discordNotification->setProcessDate(new \DateTime());
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
        if (!is_array($embeds)) {
            return;
        }

        $embedsGroups = array_chunk($embeds, 10);

        // groups of 10
        $messages = [];
        foreach ($embedsGroups as $embedsGroup) {

            // Treat 10 values
            $message['embeds'] = [];
            foreach($embedsGroup as $embed) {
                $message['embeds'][] = $this->embed($embed);
            }

            $messages[] = $message;
        }

        dump($messages);


        foreach ($messages as $send) {
            $this->client->request('POST', $webhook . '?wait=true', [
                'headers' => [
                    'Content-Type: application/json',
                    'Accept: application/json',
                ],
                'body' => json_encode($send)
            ]);
        }
    }

    /**
     * Return a single embed array
     * @param $embed
     * @return array
     */
    private function embed($embed): array
    {
        return [
            "color"=> 6651640,
            "title"=> $embed['subject'],
            "url" => "https://hoyolab.com/article/{$embed['postId']}",
            "description"=> "Vous avez {$embed['news']} nouveaux messages sur ce post hoyo",
            "fields"=> [
                [
                    "name"=> "**Views**",
                    "value"=> $embed['stats']['view'],
                    "inline"=> true
                ],
                [
                    "name"=> "**Replies**",
                    "value"=> $embed['stats']['reply'],
                    "inline"=> true
                ],
                [
                    "name"=> "**Likes**",
                    "value"=> $embed['stats']['like'],
                    "inline"=> true
                ]
            ],
            "timestamp"=>  $embed['postCreationDate']
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
                return ['error' => []];
            }
        }
        return ['error' => []];
    }
}

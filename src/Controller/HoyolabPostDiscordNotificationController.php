<?php

namespace App\Controller;

use App\Contract\Encryption\EncryptionManager;
use App\Contract\Request\HoyolabRequest;
use App\Entity\HoyolabPost;
use App\Entity\HoyolabPostDiscordNotification;
use App\Entity\HoyolabPostStats;
use App\Entity\HoyolabPostUser;
use App\Entity\User;
use App\Helper\Discord\EmbedBuilder;
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
    private EntityManagerInterface $entityManager;
    private HoyolabRequest $hoyolabRequest;

    public function __construct(
        HoyolabPostUserRepository   $hoyolabPostUserRepository,
        EntityManagerInterface      $entityManager,
        HoyolabRequest $hoyolabRequest
    )
    {
        $this->hoyolabPostUserRepository = $hoyolabPostUserRepository;
        $this->entityManager = $entityManager;
        $this->hoyolabRequest = $hoyolabRequest;
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
            $webhookUrl = EncryptionManager::decrypt($hoyoUser->getWebhookUrl(), $userKey);

            // Hoyo Posts
            $postEmbedData = [];
            // No posts
            if (empty($hoyoUser->getHoyolabPosts()->toArray())) {
                continue;
            }

            $arrayHoyoPosts = new ArrayCollection($hoyoUser->getHoyolabPosts()->toArray());
            /** @var HoyolabPost $hoyoPost */
            foreach ($arrayHoyoPosts->toArray() as $hoyoPost) {

                $newStats = $hoyoPost->getHoyolabPostStats();
                $discordNotification = $hoyoPost->getHoyolabPostDiscordNotification();
                $post = $this->hoyolabRequest->updateHoyolabPost($hoyoPost->getPostId());

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
                }
                $discordNotification->setProcessDate(new \DateTime());
                $this->entityManager->persist($discordNotification);
            }

            // Treat discord notification
            $this->embedNotification($webhookUrl, $postEmbedData);

            // Flush
            // TODO : REMOVE FOR PROD
            // $this->entityManager->flush();
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
                $message['embeds'][] = EmbedBuilder::hoyolabNotification($embed);
            }

            $messages[] = $message;
        }

        foreach ($messages as $send) {
            $response = $this->hoyolabRequest->sendDiscordEmbed($webhook, $send);

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

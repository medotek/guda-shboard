<?php

namespace App\Controller;

use App\Contract\Encryption\EncryptionManager;
use App\Contract\Request\HoyolabRequest;
use App\Contract\Stats\TaxonomyInterface;
use App\Entity\HoyolabPost;
use App\Entity\HoyolabPostDiscordNotification;
use App\Entity\HoyolabPostStats;
use App\Entity\HoyolabPostUser;
use App\Entity\HoyolabStats;
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

                    $currDateTime = new \DateTime('now');
                    $date = strtotime($currDateTime->format('Y-m-d H:i:s'));
                    $currentHour = date('H', $date);
                    $hourFirstHalf = new \DateTime($currentHour . ':00');
                    $hourEndFirstHalf = new \DateTime($currentHour . ':30');
                    if ($hourFirstHalf <= $currDateTime && $currDateTime <= $hourEndFirstHalf) {
                        // Vérifier s'il n'y a pas un hoyoStat entity qui existe dans l'interval, meme heure
                        $qb = $this->entityManager->createQueryBuilder();
                        $qb->select('hs');
                        $qb->from(' App:HoyolabStats', 'hs');
                        $qb->where('hs.date BETWEEN :from AND :to AND hs.hoyolabPost = :post');
                        $qb->setParameter('from', $hourFirstHalf);
                        $qb->setParameter('to', $hourEndFirstHalf);
                        $qb->setParameter('post', $hoyoPost);
                        $query = $qb->getQuery();

                        if ($query->getResult()) {
                            continue;
                        }

                        $stat = new HoyolabStats();
                        $stat->setDate(new \DateTime());
                        $stat->setView($statsData[TaxonomyInterface::VIEWS_MAPPING]);
                        $stat->setLikes($statsData[TaxonomyInterface::LIKES_MAPPING]);
                        $stat->setReply($statsData[TaxonomyInterface::REPLIES_MAPPING]);
                        $stat->setShare($statsData[TaxonomyInterface::SHARES_MAPPING]);
                        $stat->setBookmark($statsData[TaxonomyInterface::BOOKMARKS_MAPPING]);
                        $stat->setHoyolabPost($hoyoPost);
                        $this->entityManager->persist($stat);
                    }


                    // Don't update if there is no new replies
                    if ((int)$statsData[TaxonomyInterface::REPLIES_MAPPING] === $newStats->getReply()) {
                        continue;
                    }

                    $oldStats = [
                        TaxonomyInterface::LIKES_MAPPING => $newStats->getLikes(),
                        TaxonomyInterface::VIEWS_MAPPING => $newStats->getView(),
                        TaxonomyInterface::BOOKMARKS_MAPPING => $newStats->getBookmark(),
                        TaxonomyInterface::SHARES_MAPPING => $newStats->getShare(),
                        TaxonomyInterface::REPLIES_MAPPING => $newStats->getReply()
                    ];

                    // Hoyolab Post Stats
                    $newStats->setView($statsData[TaxonomyInterface::VIEWS_MAPPING]);
                    $newStats->setLikes($statsData[TaxonomyInterface::LIKES_MAPPING]);
                    $newStats->setReply($statsData[TaxonomyInterface::REPLIES_MAPPING]);
                    $newStats->setShare($statsData[TaxonomyInterface::SHARES_MAPPING]);
                    $newStats->setBookmark($statsData[TaxonomyInterface::BOOKMARKS_MAPPING]);

                    if ($postData['reply_time'])
                        $hoyoPost->setLastReplyTime((new \DateTime($postData['reply_time'])));
                    $hoyoPost->setSubject($postData['subject']);

                    $this->entityManager->persist($hoyoPost);
                    // Only persist the new stats
                    $this->entityManager->persist($newStats);
                }


                // TODO refacto
                $diffView = (int)$statsData[TaxonomyInterface::VIEWS_MAPPING] - $oldStats[TaxonomyInterface::VIEWS_MAPPING];
                $diffView ? ($diffView = " **+{$diffView}**") : $diffView = "";

                $diffBookmark = (int)$statsData[TaxonomyInterface::BOOKMARKS_MAPPING] - $oldStats[TaxonomyInterface::BOOKMARKS_MAPPING];
                $diffBookmark ? $diffBookmark = " **+{$diffBookmark}**" : $diffBookmark = "";

                $diffLike = (int)$statsData[TaxonomyInterface::LIKES_MAPPING] - $oldStats[TaxonomyInterface::LIKES_MAPPING];
                $diffLike ? $diffLike = " **+{$diffLike}**" : $diffLike = "";

                $diffShare = (int)$statsData[TaxonomyInterface::SHARES_MAPPING] - $oldStats[TaxonomyInterface::SHARES_MAPPING];
                $diffShare ? $diffShare = " **+{$diffShare}**" : $diffShare = "";

                $diffReply = (int)$statsData[TaxonomyInterface::REPLIES_MAPPING] - $oldStats[TaxonomyInterface::REPLIES_MAPPING];
                $diffReply ? $diffReply = " **+{$diffReply}**" : $diffReply = "";

                // Compare cron stats with updated stats
                $statistics = [
                    'view' => $oldStats[TaxonomyInterface::VIEWS_MAPPING] . $diffView,
                    'bookmark' => $oldStats[TaxonomyInterface::BOOKMARKS_MAPPING] . $diffBookmark,
                    'like' => $oldStats[TaxonomyInterface::LIKES_MAPPING] . $diffLike,
                    'share' => $oldStats[TaxonomyInterface::SHARES_MAPPING] . $diffShare,
                    'reply' => $oldStats[TaxonomyInterface::REPLIES_MAPPING] . $diffReply,
                ];

                // Prepare embed data
                $postEmbedData[] = [
                    'postId' => $hoyoPost->getPostId(),
                    'news' => $newStats->getReply() - $oldStats[TaxonomyInterface::REPLIES_MAPPING],
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

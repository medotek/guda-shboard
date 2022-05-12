<?php

namespace App\Controller;


use App\Contract\Encryption\EncryptionManager;
use App\Contract\Request\HoyolabRequest;
use App\Contract\Stats\TaxonomyInterface;
use App\Entity\HoyolabPost;
use App\Entity\HoyolabPostUser;
use App\Entity\HoyolabStats;
use App\Entity\HoyolabStatType;
use App\Entity\HoyolabUserStats;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class HoyolabStatsController extends AbstractController implements TaxonomyInterface
{
    private EntityManagerInterface $entityManager;
    private HoyolabRequest $hoyolabRequest;
    private HttpClientInterface $httpClient;

    public function __construct(
        EntityManagerInterface $entityManager,
        HoyolabRequest         $hoyolabRequest,
        HttpClientInterface    $httpClient
    )
    {
        $this->entityManager = $entityManager;
        $this->hoyolabRequest = $hoyolabRequest;
        $this->httpClient = $httpClient;
    }

    /**
     * Add hoyolab post stats per hour
     * @throws TransportExceptionInterface
     * @throws Exception
     */
    public function cronHoyoPostStats()
    {
        $allHoyoUsers = $this->entityManager->getRepository(HoyolabPostUser::class)->findAll();
        $arrayHoyoUsers = new ArrayCollection($allHoyoUsers);

        /** @var HoyolabPostUser $hoyoUser */
        foreach ($arrayHoyoUsers->toArray() as $hoyoUser) {
            // No posts
            if (empty($hoyoUser->getHoyolabPosts()->toArray())) {
                continue;
            }

            $arrayHoyoPosts = new ArrayCollection($hoyoUser->getHoyolabPosts()->toArray());
            /** @var HoyolabPost $hoyoPost */
            foreach ($arrayHoyoPosts->toArray() as $hoyoPost) {
                // Retrieves post informations
                $post = $this->hoyolabRequest->updateHoyolabPost($hoyoPost->getPostId());
                // Update the hoyo post here
                if (array_key_exists('post', $post['data'])) {
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

                        dump('creating !');

                        $stat = new HoyolabStats();
                        $stat->setDate(new \DateTime());
                        $stat->setView($statsData[self::VIEWS_MAPPING]);
                        $stat->setLikes($statsData[self::LIKES_MAPPING]);
                        $stat->setReply($statsData[self::REPLIES_MAPPING]);
                        $stat->setShare($statsData[self::SHARES_MAPPING]);
                        $stat->setBookmark($statsData[self::BOOKMARKS_MAPPING]);
                        $stat->setHoyolabPost($hoyoPost);
                        $this->entityManager->persist($stat);
                    }
                }
            }

            $this->entityManager->flush();
        }
    }

    /**
     * Add hoyolab user stats per hour
     * @return void
     */
    public function cronHoyoUserStats()
    {
        $allHoyoUsers = $this->entityManager->getRepository(HoyolabPostUser::class)->findAll();
        $arrayHoyoUsers = new ArrayCollection($allHoyoUsers);

        /** @var HoyolabPostUser $hoyoUser */
        foreach ($arrayHoyoUsers->toArray() as $hoyoUser) {
            $response = $this->hoyolabRequest->getHoyolabUserFullInformations($hoyoUser->getUid());

            if (200 === $response->getStatusCode()) {
                try {
                    $arrResponse = $response->toArray();
                    if (isset($arrResponse['data']['user_info']['achieve'])) {
                        $stats = $arrResponse['data']['user_info']['achieve'];
                        $currDateTime = new \DateTime('now');
                        $date = strtotime($currDateTime->format('Y-m-d H:i:s'));
                        $currentHour = date('H', $date);
                        $hourFirstHalf = new \DateTime($currentHour . ':00');
                        $hourEndFirstHalf = new \DateTime($currentHour . ':30');

                        if ($hourFirstHalf <= $currDateTime && $currDateTime <= $hourEndFirstHalf) {
                            $userStats = new HoyolabUserStats();
                            $userStats->setDate(new \DateTime());
                            $userStats->setLikes($stats[TaxonomyInterface::LIKES_MAPPING]);
                            $userStats->setFollowed($stats[TaxonomyInterface::FOLLOWED_MAPPING]);
                            $userStats->setNewFollowers($stats[TaxonomyInterface::NEWFOLLOWERS_MAPPING]);
                            $userStats->setPosts($stats[TaxonomyInterface::POSTS_MAPPING]);
                            $userStats->setReplyposts($stats[TaxonomyInterface::POSTRELIES_MAPPING]);
                            $userStats->setUser($hoyoUser);
                            $this->entityManager->persist($userStats);
                        }
                    }
                } catch (ClientExceptionInterface|DecodingExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface|TransportExceptionInterface $e) {
                    throw new Exception($e);
                }
            }
        }
        $this->entityManager->flush();
    }

    /**
     * Get hoyolab user stats
     * @param string $scope day, days, month, months, year
     * @return void
     * @throws TransportExceptionInterface
     * @throws Exception
     */
    public function getUserHoyoStats(string $scope)
    {

    }
}

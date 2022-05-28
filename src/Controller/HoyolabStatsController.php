<?php

namespace App\Controller;


use App\Builder\StatsBuilder;
use App\Contract\Encryption\EncryptionManager;
use App\Contract\Request\HoyolabRequest;
use App\Contract\Stats\TaxonomyInterface;
use App\Entity\HoyolabPost;
use App\Entity\HoyolabPostUser;
use App\Entity\HoyolabStats;
use App\Entity\HoyolabUserStats;
use App\Entity\User;
use App\Helper\Stat\StatsPeriodHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api")
 */
class HoyolabStatsController extends AbstractController implements TaxonomyInterface
{
    private EntityManagerInterface $entityManager;
    private HoyolabRequest $hoyolabRequest;
    private HttpClientInterface $httpClient;
    private Security $security;
    private LoggerInterface $logger;
    public array $errors = [];

    public function __construct(
        EntityManagerInterface $entityManager,
        HoyolabRequest         $hoyolabRequest,
        HttpClientInterface    $httpClient,
        Security               $security,
        LoggerInterface        $logger
    )
    {
        $this->entityManager = $entityManager;
        $this->hoyolabRequest = $hoyolabRequest;
        $this->httpClient = $httpClient;
        $this->security = $security;
        $this->logger = $logger;
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
     * @throws TransportExceptionInterface
     * @throws Exception
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
     * @Route("/hoyolab/user/{uid}/analytics/{scope}",name="hoyolab_user_analytics")
     * @param string $scope day, week, month, year
     * @param int $uid
     * @return JsonResponse
     * @throws Exception
     */
    public function getUserHoyoStats(string $scope, int $uid): JsonResponse
    {
        /** @var User $user */
        $user = $this->security->getUser();

        if ($user) {
            $day = new \DateTime('now');
            $dateFrom = null;
            switch ($scope) {
                case 'day':
                    $dateFrom = $day->modify('-1 day')->modify('-30 min');
                    break;
                case 'week':
                    $dateFrom = (new \DateTime($day->format('Y-m-d') . ' 00:00:00'))->modify('-7 day');
                    break;
                case 'month':
                    $dateFrom = (new \DateTime($day->format('Y-m-d') . ' 00:00:00'))->modify('-1 month');
                    break;
                case 'year':
                    $dateFrom = (new \DateTime($day->format('Y-m-d') . ' 00:00:00'))->modify('-1 year');
                    break;
            }

            if (null === $day | null === $dateFrom) {
                return $this->json(['error' => 'An error occurred'], 500);
            }

        $dateTo = new \DateTime('now');

        // Test
//        $userRepository = $this->entityManager->getRepository(User::class);
//        $user = $userRepository->find(29);

        $hoyoPostUserRepository = $this->entityManager->getRepository(HoyolabPostUser::class);
        if (!$hoyoPostUser = $hoyoPostUserRepository->findOneBy(['user' => $user, 'uid' => $uid])) {
            return $this->json(['error' => 'There is no existing hoyoUser id associated with the current user'], 500);
        }

            $qb = $this->entityManager->createQueryBuilder();
            $qb->select('hus.date, hus.likes, hus.posts, hus.replyposts, hus.followed');
            $qb->from(' App:HoyolabUserStats', 'hus');
            $qb->where('hus.date BETWEEN :from AND :to AND hus.user = :hoyoUser');
            $qb->setParameter('from', $dateFrom);
            $qb->setParameter('to', $dateTo);
            $qb->setParameter('hoyoUser', $hoyoPostUser);
            $query = $qb->getQuery();

            $results = $query->getResult();

            if (!empty($results)) {
                return $this->json(['success' => StatsBuilder::prepareStats($dateFrom, $dateTo, $scope, $results)]);
            }

        } else {
            $this->errors[] = 'You need ot be authenticated';
        }
        return $this->json(['error' => $this->errors], 400);
    }
}

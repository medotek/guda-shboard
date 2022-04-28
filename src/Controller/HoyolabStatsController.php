<?php

namespace App\Controller;


use App\Contract\Encryption\EncryptionManager;
use App\Contract\Request\HoyolabRequest;
use App\Contract\Stats\TaxonomyInterface;
use App\Entity\HoyolabPost;
use App\Entity\HoyolabPostUser;
use App\Entity\HoyolabStats;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HoyolabStatsController extends AbstractController implements TaxonomyInterface
{
    private EntityManagerInterface $entityManager;
    private HoyolabRequest $hoyolabRequest;

    public function __construct(
        EntityManagerInterface $entityManager,
        HoyolabRequest         $hoyolabRequest
    )
    {
        $this->entityManager = $entityManager;
        $this->hoyolabRequest = $hoyolabRequest;
    }

    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     * @throws \Exception
     */
    public function cronStats()
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
//                        if ($hourFirstHalf <= $currDateTime && $currDateTime <= $hourEndFirstHalf) {
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
//                        }
                }
            }

            $this->entityManager->flush();
        }
    }
}

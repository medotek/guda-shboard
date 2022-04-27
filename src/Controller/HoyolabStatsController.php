<?php

namespace App\Controller;


use App\Contract\Encryption\EncryptionManager;
use App\Contract\Request\HoyolabRequest;
use App\Contract\Stats\TaxonomyInterface;
use App\Entity\HoyolabPost;
use App\Entity\HoyolabPostUser;
use App\Entity\HoyolabStats;
use App\Entity\HoyolabStatType;
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
                    // Filter array data
                    $filteredStatsData = array_filter(
                        $statsData,
                        fn ($key) => in_array($key, array_keys(self::ALL_TAXONOMIES)),
                        ARRAY_FILTER_USE_KEY
                    );

                    foreach ($filteredStatsData as $mapping => $statData) {
                        $currDateTime = new \DateTime('now');
                        $date = strtotime($currDateTime->format('Y-m-d H:i:s'));
                        $currentHour = date('H', $date);
                        $hourFirstHalf = new \DateTime($currentHour. ':00');
                        $hourEndFirstHalf = new \DateTime($currentHour. ':30');
                        if ($hourFirstHalf <= $currDateTime && $currDateTime <= $hourEndFirstHalf) {
                            // Vérifier s'il n'y a pas un hoyoStat entity qui existe dans l'interval, meme heure
                            $qb = $this->entityManager->createQueryBuilder();
                            $qb->select('*');
                            $qb->from('HoyolabStats', 'hs');
                            $qb->where('hs.date BETWEEN :from AND :to AND hoyolabPost = :post');
                            $qb->setParameter('from', $hourFirstHalf);
                            $qb->setParameter('to', $hourEndFirstHalf);
                            $qb->setParameter('post', $hoyoPost);
                            $query = $qb->getQuery();

                            if ($query->getResult()) {
                                continue;
                            }

                            $stat = new HoyolabStats();
                            $stat->setDate(new \DateTime());
                            $statType = $this->getStatType(self::ALL_TAXONOMIES[$mapping]);
                            $stat->setStatType($statType);
                            $stat->setNumber($statData);
                            $stat->setHoyolabPost($hoyoPost);
                            $this->entityManager->persist($stat);
                        }
                    }
                }
                dump('passed !');
            }
        }
    }

    /**
     * @param $taxonomy
     * @return \App\Entity\HoyolabStatType
     * @see
     */
    public function getStatType($taxonomy): HoyolabStatType
    {
        $hoyoStatTypeRepository = $this->entityManager->getRepository(HoyolabStatType::class);
        return $hoyoStatTypeRepository->findOneBy(['taxonomy' => $taxonomy]);
    }
}

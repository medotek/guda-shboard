<?php

namespace App\Controller;


use App\Contract\Encryption\EncryptionManager;
use App\Contract\Request\HoyolabRequest;
use App\Contract\Stats\TaxonomyInterface;
use App\Entity\HoyolabPost;
use App\Entity\HoyolabPostUser;
use App\Entity\HoyolabStatType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class HoyolabStats extends AbstractController implements TaxonomyInterface
{
    private EntityManagerInterface $entityManager;
    private HoyolabRequest $hoyolabRequest;

    public function __construct(
        EntityManagerInterface $entityManager,
        HoyolabRequest $hoyolabRequest
    )
    {
        $this->entityManager = $entityManager;
        $this->hoyolabRequest = $hoyolabRequest;
    }

    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function cronStats()
    {
        $allHoyoUsers = $this->entityManager->getRepository(HoyolabPostUser::class)->findAll();
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
                dump($post);
                $oldStats = [];
                $statsData = [];
                // Update the hoyo post here
                if (array_key_exists('post', $post['data'])) {
                    $postData = $post['data']['post']['post'];
                    $statsData = $post['data']['post']['stat'];
                }
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

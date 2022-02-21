<?php

namespace App\Controller;

use App\Entity\HoyolabPostUser;
use App\Repository\HoyolabPostRepository;
use App\Repository\HoyolabPostUserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HoyolabPostDiscordNotificationController extends AbstractController
{
    private HoyolabPostUserRepository $hoyolabPostUserRepository;

    private EncryptionManagerController $encryptionManager;

    public function __construct(
        HoyolabPostUserRepository $hoyolabPostUserRepository,
        EncryptionManagerController $encryptionManager
    )
    {
        $this->hoyolabPostUserRepository = $hoyolabPostUserRepository;
        $this->encryptionManager = $encryptionManager;
    }

    public function discordNotificationCron(): Response
    {
        /** @var HoyolabPostUser[] $allHoyoUsers */
        $allHoyoUsers = $this->hoyolabPostUserRepository->findAll();
        $arrayHoyoUsers = new ArrayCollection($allHoyoUsers);

        /** @var HoyolabPostUser $hoyoUser */
        foreach($arrayHoyoUsers->toArray() as $hoyoUser) {
            // If there is no webhook setup, skip the current iteration
            if (!$hoyoUser->getWebhookUrl()) {
                continue;
            }


            // Get user key to decrypt the webhookUrl
            $userKey = $hoyoUser->getUser()->getCreationDate()->getTimestamp();
            $webhokUrl = $this->encryptionManager->decrypt($hoyoUser->getWebhookUrl(), $userKey);
        }
    }
}

<?php

namespace App\Controller;

use App\Entity\DiscordWebhook;
use App\Entity\User;
use App\Repository\DiscordWebhookRepository;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @Route("/api")
 */
class DiscordWebhookController extends AbstractController
{

    private HttpClientInterface $client;
    private DiscordWebhookRepository $discordWebhookRepository;
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;
    private SerializerInterface $serializer;
    private Security $security;

    public function __construct(
        HttpClientInterface      $client,
        DiscordWebhookRepository $discordWebhookRepository,
        UserRepository           $userRepository,
        SerializerInterface      $serializer,
        EntityManagerInterface   $entityManager,
        Security $security
    )
    {
        $this->client = $client;
        $this->discordWebhookRepository = $discordWebhookRepository;
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
        $this->security = $security;
    }

    /**
     * encrypt string
     * @param $string
     * @return false|string
     */
    private function encrypt($string)
    {
        $ciphering = "AES-128-CTR";
        $options = 0;
        $encryption_iv = '1234567891011121';
        $encryption_key = "GudaIsStrong";
        return openssl_encrypt($string, $ciphering,
            $encryption_key, $options, $encryption_iv);

    }

    /**
     * decrypt encrypted string :)
     * @param $string
     * @return false|string
     */
    private function decrypt($string)
    {
        $ciphering = "AES-128-CTR";
        $options = 0;
        $encryption_iv = '1234567891011121';
        $encryption_key = "GudaIsStrong";
        return openssl_decrypt($string, $ciphering,
            $encryption_key, $options, $encryption_iv);
    }

    /**
     * @Route("/discord/webhook/new", name="discord_webhook_new")
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function new(Request $request): Response
    {
        $jsonData = json_decode($request->getContent());
        $discordRequest = $this->client->request('GET', $jsonData->webhookUrl);

        $statusCode = $discordRequest->getStatusCode();

        if ($statusCode === 200) {
            try {
                $content = $discordRequest->toArray();
                // verify if the webhook exists in the database
                $existingWebhook = $this->discordWebhookRepository->findOneBy(['webhookId' => $content['id']]);

                if (!empty($existingWebhook)) {
                    return $this->json([], 201);
                }

                /** @var User $existingUser */
                $existingUser = $this->security->getUser();
                if ($existingUser) {
                    $discordWebhook = new DiscordWebhook();
                    $discordWebhook->setName($content['name']);
                    $discordWebhook->setWebhookId($content['id']);
                    $discordWebhook->setAvatarId($content['avatar']);
                    $discordWebhook->setChannelId($content['channel_id']);
                    $discordWebhook->setGuildId($content['guild_id']);
                    $discordWebhook->setToken($this->encrypt($content['token']));
                    $discordWebhook->setOwner($existingUser);
                    // Persist discordWebhook
                    $this->entityManager->persist($discordWebhook);
                    $this->entityManager->flush();
                }

                return $this->json([
                    'message' => 'created'
                ]);
            } catch (ClientExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface|TransportExceptionInterface|DecodingExceptionInterface $e) {
                return $this->json([], 203);
            }
        }

        return $this->json([
            'message' => 'An error occured'
        ], 500);
    }

    /**
     * @Route("/discord/webhook/list", name="discord_webhook_list")
     */
    public function getWebhooksList(): Response
    {
        /** @var User $user */
        $user = $this->security->getUser();
        $webhooks = $this->discordWebhookRepository->findBy(["owner" => $user]);

        if (!empty($webhooks) && $user) {
            $arrayWebhooks = new ArrayCollection($webhooks);
            // Decrypt token
            foreach ($arrayWebhooks->toArray() as $key => $webhook) {
                $arrayWebhooks->remove($key);
                /** @var DiscordWebhook $webhook */
                $webhook->setToken($this->decrypt($webhook->getToken()));
                $arrayWebhooks->add($webhook);
            }

            return $this->json([
                'webhooks' => $this->serializer->serialize($arrayWebhooks, 'json')
            ]);
        }

        return $this->json([
            'error' => 'No webhooks found'
        ], 400);
    }
}

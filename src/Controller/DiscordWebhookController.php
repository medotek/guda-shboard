<?php

namespace App\Controller;

use App\Contract\Encryption\EncryptionManager;
use App\Entity\DiscordWebhook;
use App\Entity\User;
use App\Repository\DiscordWebhookRepository;
use App\Repository\UserRepository;
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
        Security                 $security
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
                    $discordWebhook->setToken(EncryptionManager::encrypt($content['token'], $existingUser->getCreationDate()->getTimestamp()));
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
     * @Route(
     *     name="discord_webhook_list",
     *     path="/discord/webhook/list",
     *     defaults={"_api_collection_operation_name"="get"}
     * )
     */
    public function getWebhooksList(): Response
    {
        /** @var User $user */
        $user = $this->security->getUser();
        $webhooks = $this->discordWebhookRepository->findBy(["owner" => $user]);

        if (!empty($webhooks) && $user) {
            return $this->json([
                'webhooks' => $this->serializer->serialize($webhooks, 'json')
            ]);
        }

        return $this->json([
            'error' => 'No webhooks found'
        ], 400);
    }

    /**
     * @Route(
     *     name="discord_webhook_item",
     *     path="/discord/webhook/{id}",
     *     defaults={"_api_item_operation_name"="get"}
     * )
     */
    public function getWebhook(int $id): Response
    {
        /** @var User $user */
        $user = $this->security->getUser();
        $webhook = $this->discordWebhookRepository->findOneBy(["owner" => $user, 'id' => $id]);

        if (!empty($webhook) && $user) {
            return $this->json($this->serializer->serialize($webhook, 'json'));
        }

        return $this->json([
            'error' => 'No webhook found'
        ], 400);
    }
}

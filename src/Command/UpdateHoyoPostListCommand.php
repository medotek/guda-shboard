<?php

namespace App\Command;

use App\Controller\EncryptionManagerController;
use App\Controller\HoyolabPostDiscordNotificationController;
use App\Repository\HoyolabPostRepository;
use App\Repository\HoyolabPostUserRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class UpdateHoyoPostListCommand extends Command
{
    protected static $defaultName = 'app:guda:hoyo-update-postlist';
    private EntityManagerInterface $entityManager;
    private HttpClientInterface $client;
    private HoyolabPostUserRepository $hoyolabPostUserRepository;
    private HoyolabPostRepository $hoyolabPostRepository;
    private Security $security;
    private UserRepository $userRepository;
    private SerializerInterface $serializer;
    private LoggerInterface $logger;

    public function __construct(
        EntityManagerInterface $entityManager,
        HttpClientInterface $client,
        HoyolabPostUserRepository $hoyolabPostUserRepository,
        HoyolabPostRepository $hoyolabPostRepository,
        UserRepository              $userRepository,
        SerializerInterface         $serializer,
        Security                    $security,
        LoggerInterface             $logger
    )
    {
        parent::__construct();

        $this->entityManager = $entityManager;
        $this->client = $client;
        $this->hoyolabPostUserRepository = $hoyolabPostUserRepository;
        $this->hoyolabPostRepository = $hoyolabPostRepository;
        $this->userRepository = $userRepository;
        $this->serializer = $serializer;
        $this->security = $security;
        $this->logger   = $logger;
    }

    protected function configure(): void
    {
        $this
            ->setHelp('This command allows you to update and send a notification to all hoyolab users (a webhook needs to be configured)');
    }

    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $taskForce = new \App\Controller\HoyolabPostsWebhookController(
            $this->client,
            $this->hoyolabPostRepository,
            $this->security,
            $this->entityManager,
            $this->hoyolabPostUserRepository,
            $this->userRepository,
            $this->serializer,
            $this->logger
        );

        $taskForce->updateHoyolabUserPostsList();
        return Command::SUCCESS;
    }
}

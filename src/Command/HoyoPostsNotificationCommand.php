<?php

namespace App\Command;

use App\Controller\EncryptionManagerController;
use App\Controller\HoyolabPostDiscordNotificationController;
use App\Repository\HoyolabPostUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class HoyoPostsNotificationCommand extends Command
{
    protected static $defaultName = 'app:guda:hoyo-discord-notification';
    private EntityManagerInterface $entityManager;
    private EncryptionManagerController $encryptionManager;
    private HttpClientInterface $client;
    private HoyolabPostUserRepository $hoyolabPostUserRepository;

    public function __construct(
        string $name = null,
        EntityManagerInterface $entityManager,
        EncryptionManagerController $encryptionManager,
        HttpClientInterface $client,
        HoyolabPostUserRepository $hoyolabPostUserRepository
    )
    {
        parent::__construct($name);

        $this->entityManager = $entityManager;
        $this->encryptionManager = $encryptionManager;
        $this->client = $client;
        $this->hoyolabPostUserRepository = $hoyolabPostUserRepository;
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
        $taskForce = new \App\Controller\HoyolabPostDiscordNotificationController( $this->hoyolabPostUserRepository, $this->encryptionManager,$this->client, $this->entityManager);
        $taskForce->discordNotificationCron();
        return Command::SUCCESS;
    }
}

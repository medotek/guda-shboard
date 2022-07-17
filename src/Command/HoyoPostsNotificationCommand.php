<?php

namespace App\Command;

use App\Contract\Request\HoyolabRequest;
use App\Controller\HoyolabPostDiscordNotificationController;
use App\Repository\HoyolabPostUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class HoyoPostsNotificationCommand extends Command
{
    protected static $defaultName = 'app:guda:notification-process';
    private EntityManagerInterface $entityManager;
    private HoyolabPostUserRepository $hoyolabPostUserRepository;
    private HoyolabRequest $hoyolabRequest;
    private LoggerInterface $logger;

    public function __construct(
        EntityManagerInterface $entityManager,
        HoyolabPostUserRepository $hoyolabPostUserRepository,
        HoyolabRequest $hoyolabRequest,
        LoggerInterface $logger
    )
    {
        parent::__construct();

        $this->entityManager = $entityManager;
        $this->hoyolabPostUserRepository = $hoyolabPostUserRepository;
        $this->hoyolabRequest = $hoyolabRequest;
        $this->logger = $logger;
    }

    protected function configure(): void
    {
        $this
            ->setHelp('This command allows you to update and send a notification to all hoyolab users (a webhook needs to be configured)')
            ->addArgument('id', InputArgument::REQUIRED, 'Set hoyolab user id to run its process');
    }

    /**
     * @throws TransportExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($id = (int) $input->getArgument('id')) {
            $this->logger->info('[INFO] Run command : ' . self::$defaultName .'. Id : ' . $id);
            $taskForce = new HoyolabPostDiscordNotificationController($this->hoyolabPostUserRepository, $this->entityManager, $this->hoyolabRequest, $this->logger);
            $taskForce->discordNotificationCron($id);
        }
        return Command::SUCCESS;
    }
}

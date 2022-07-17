<?php

namespace App\Command;

use App\Contract\Request\HoyolabRequest;
use App\Controller\HoyolabStatsController;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class HoyolabStatsCommand extends Command
{
    protected static $defaultName = 'app:guda:hoyo-stats';
    private EntityManagerInterface $entityManager;
    private HoyolabRequest $hoyolabRequest;
    private HttpClientInterface $httpClient;
    private Security $security;
    private LoggerInterface $logger;

    public function __construct(
        EntityManagerInterface $entityManager,
        HoyolabRequest         $hoyolabRequest,
        HttpClientInterface    $httpClient,
        Security $security,
        LoggerInterface $logger

    )
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->hoyolabRequest = $hoyolabRequest;
        $this->httpClient     = $httpClient;
        $this->security = $security;
        $this->logger = $logger;
    }


    protected function configure(): void
    {
        $this
            ->setHelp('Add post & account stats on the database');
    }


    /**
     * @throws TransportExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $taskForce = new HoyolabStatsController($this->entityManager, $this->hoyolabRequest, $this->httpClient, $this->security, $this->logger);
        $taskForce->cronHoyoPostStats();
        $taskForce->cronHoyoUserStats();
        return Command::SUCCESS;
    }
}

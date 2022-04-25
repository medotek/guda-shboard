<?php

namespace App\Command;

use App\Contract\Request\HoyolabRequest;
use App\Controller\HoyolabStatsController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class HoyolabStatsCommand extends Command
{
    protected static $defaultName = 'app:guda:hoyo-stats';
    private EntityManagerInterface $entityManager;
    private HoyolabRequest $hoyolabRequest;

    public function __construct(
        string $name = null,
        EntityManagerInterface $entityManager,
        HoyolabRequest         $hoyolabRequest
    )
    {
        parent::__construct($name);
        $this->entityManager = $entityManager;
        $this->hoyolabRequest = $hoyolabRequest;
    }


    protected function configure(): void
    {
        $this
            ->setHelp('Add post & account stats on the database');
    }


    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $taskForce = new HoyolabStatsController($this->entityManager, $this->hoyolabRequest);
        $taskForce->cronStats();
        return Command::SUCCESS;
    }
}

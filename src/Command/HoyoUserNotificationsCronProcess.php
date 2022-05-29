<?php

namespace App\Command;

use App\Entity\HoyolabPostUser;
use App\Repository\HoyolabPostUserRepository;
use Doctrine\Common\Collections\Collection;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\PhpProcess;
use Symfony\Component\Process\Process;

class HoyoUserNotificationsCronProcess extends Command
{
    protected static $defaultName = 'app:guda:hoyo-discord-notification';
    private HoyolabPostUserRepository $hoyolabPostUserRepository;
    private LoggerInterface $logger;

    public function __construct(
        string                    $name = null,
        HoyolabPostUserRepository $hoyolabPostUserRepository,
        LoggerInterface $logger
    )
    {
        parent::__construct($name);
        $this->hoyolabPostUserRepository = $hoyolabPostUserRepository;
        $this->logger = $logger;
    }

    protected function configure(): void
    {
        $this
            ->setHelp('Exec command as multiple process');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // TODO : stand by, ne fait rien lorsqu'on l'execute
        /** @var HoyolabPostUser|Collection $hoyoUsers */
        $hoyoUsers = $this->hoyolabPostUserRepository->findAll();
        $phpBinaryFinder = new PhpExecutableFinder();
        $phpBinaryPath = $phpBinaryFinder->find();
        $errors = [];
        foreach ($hoyoUsers as $hoyoUser) {
            /** @var HoyolabPostUser $hoyoUser */
            $process = new Process([$phpBinaryPath, 'bin/console', 'app:guda:notification-process', $hoyoUser->getId()]);
            $process->setWorkingDirectory(getcwd());
            $process->start();

            // TODO : Si on wait, ça marche ... Mais ce n'est pas le but

            if ($process->waitUntil(function ($input, $output) {
                dump($output);
            }))

            if (!$process->isRunning()) {
                $errors[] = $process->getExitCode();
            }
        }

        if (!empty($errors)) {
            $this->logger->error('[ERROR] On command app:guda:hoyo-discord-notification, errors code : ' . implode(',', $errors));
        }

        return Command::SUCCESS;
    }
}
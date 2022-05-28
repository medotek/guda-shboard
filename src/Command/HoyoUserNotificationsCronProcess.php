<?php

namespace App\Command;

use App\Entity\HoyolabPostUser;
use App\Repository\HoyolabPostUserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class HoyoUserNotificationsCronProcess extends Command
{
    protected static $defaultName = 'app:guda:hoyo-discord-notification';
    private HoyolabPostUserRepository $hoyolabPostUserRepository;

    public function __construct(
        string                    $name = null,
        HoyolabPostUserRepository $hoyolabPostUserRepository
    )
    {
        parent::__construct($name);
        $this->hoyolabPostUserRepository = $hoyolabPostUserRepository;
    }

    protected function configure(): void
    {
        $this
            ->setHelp('Exec command as multiple process');
    }

    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var HoyolabPostUser|Collection $hoyoUsers */

        $hoyoUsers = $this->hoyolabPostUserRepository->findAll();
        foreach ($hoyoUsers as $hoyoUser) {
            /** @var HoyolabPostUser $hoyoUser */
            $process = new Process(['php bin/console app:guda:notification-process ' . $hoyoUser->getUid()]);
            $process->run();
        }

        return Command::SUCCESS;
    }
}
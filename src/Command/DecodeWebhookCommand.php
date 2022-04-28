<?php

namespace App\Command;

use App\Contract\Encryption\EncryptionManager;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DecodeWebhookCommand extends Command
{
    protected static $defaultName = 'app:guda:decodewebhook';

    protected function configure(): void
    {
        $this
            ->setHelp('Decode a guda webhook');
    }



    /**
     * Decode encrypted webhook
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $userCreationDate = new \DateTime('2022-03-05 17:09:07');
        $webhookUrl = EncryptionManager::decrypt('s+mNsVdZYqhh48k9djRwdlSEcLZjXQpXo/ctxp3jvrt0iHYkssicOnNlxiheqfNQp2OfF3uA+630QGpF8DkH/Drp8fNcKEV3r7dHZZk3yalKXd9+yibjP5cNo7hzTV/KgCFvrGuXZbEHtgtSoup2m6gHyJtASeia', $userCreationDate->getTimestamp());
        dump($webhookUrl);
        return Command::SUCCESS;
    }
}

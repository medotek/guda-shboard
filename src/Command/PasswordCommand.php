<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;

class PasswordCommand extends Command
{
    protected static $defaultName = 'app:guda:encodepwd';

    protected function configure(): void
    {
        $this
            ->setHelp('Encode guda user password')
            ->addArgument('pwd', InputArgument::REQUIRED, 'Set the password to be encoded');
    }

    /**
     * Decode encrypted webhook
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($pwd = $input->getArgument('pwd')) {
            dump($this->passwordHasher()->hash(
                $pwd
            ));
        }
        return Command::SUCCESS;
    }

    /**
     * Manage hashing
     * @return \Symfony\Component\PasswordHasher\PasswordHasherInterface
     */
    private function passwordHasher(): \Symfony\Component\PasswordHasher\PasswordHasherInterface
    {
        // Configure different password hashers via the factory
        $factory = new PasswordHasherFactory([
            'common' => ['algorithm' => 'bcrypt'],
            'memory-hard' => ['algorithm' => 'sodium'],
        ]);
        // Retrieve the right password hasher by its name
        return $factory->getPasswordHasher('common');

    }
}
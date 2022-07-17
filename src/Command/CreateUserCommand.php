<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CreateUserCommand extends \Symfony\Component\Console\Command\Command
{
    private UserPasswordHasherInterface $passwordHasher;
    private EntityManagerInterface $entityManager;
    protected static $defaultName = 'app:create:user';

    public function __construct(
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    )
    {
        parent::__construct();
        $this->passwordHasher = $passwordHasher;
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this
            ->setHelp('Decode a guda webhook')
            ->addOption('email', null, InputOption::VALUE_REQUIRED, 'Email')
            ->addOption('name', null, InputOption::VALUE_REQUIRED, 'Username')
            ->addOption('password', null, InputOption::VALUE_REQUIRED, 'Password')
        ;
    }

    /**
     * Decode encrypted webhook
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        if (null === $input->getOption('email')
            || null === $input->getOption('name')
            || null === $input->getOption('password')
        ) {
            $io->error('Missing options');
            return Command::FAILURE;
        }

        if (null !== $this->entityManager->getRepository(User::class)->findOneBy(['email' => $input->getOption('email')])) {
            $io->error('Existing user with the same email');
            return Command::FAILURE;
        }

        $newUser = new User();
        $newUser->setEmail($input->getOption('email'));
        $newUser->setName($input->getOption('name'));
        $password = $this->passwordHasher->hashPassword(
            $newUser,
            $input->getOption('password')
        );
        $newUser->setPassword($password);

        $this->entityManager->persist($newUser);
        $this->entityManager->flush();
        $io->success('User created');
        return Command::SUCCESS;
    }
}

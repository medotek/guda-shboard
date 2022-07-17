<?php

namespace App\Command;

use App\Entity\HoyolabPostUser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;

class RemoveHoyoUserCommand extends Command
{
    protected static $defaultName = 'app:guda:remove-hoyo-user';
    private EntityManagerInterface $em;
    public function __construct(
        EntityManagerInterface $em
    )
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function configure(): void
    {
        $this
            ->setHelp('Enter hoyo user id')
            ->addArgument('id', InputArgument::REQUIRED, 'Set the id of a hoyo user');
    }

    /**
     * Decode encrypted webhook
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($id = $input->getArgument('id')) {
            $hoyoUserRepository = $this->em->getRepository(HoyolabPostUser::class);
            /** @var HoyolabPostUser $user */
            if ($user = $hoyoUserRepository->find($id)) {
                $this->em->remove($user);;
                $this->em->flush();
            }
        }

        return Command::SUCCESS;
    }
}

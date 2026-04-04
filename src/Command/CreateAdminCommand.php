<?php

namespace App\Command;

use App\Entity\Admin;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Créer un administrateur',
)]
class CreateAdminCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('username', InputArgument::REQUIRED, 'Nom d\'utilisateur admin')
            ->addArgument('password', InputArgument::REQUIRED, 'Mot de passe admin')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $username = $input->getArgument('username');
        $password = $input->getArgument('password');

        $existing = $this->em->getRepository(Admin::class)->findOneBy(['username' => $username]);
        if ($existing) {
            $io->warning("L'administrateur '$username' existe déjà. Mise à jour du mot de passe...");
            $admin = $existing;
        } else {
            $admin = new Admin();
            $admin->setUsername($username);
        }

        $hashedPassword = $this->passwordHasher->hashPassword($admin, $password);
        $admin->setPasswordHash($hashedPassword);

        $this->em->persist($admin);
        $this->em->flush();

        $io->success("Administrateur '$username' créé/mis à jour avec succès !");

        return Command::SUCCESS;
    }
}

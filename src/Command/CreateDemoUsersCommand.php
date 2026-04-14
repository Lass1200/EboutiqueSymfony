<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


#[AsCommand(
    name: 'app:create-demo-users',
    description: 'Crée ou réinitialise admin@sneakers.fr et user@sneakers.fr (mots de passe connus)',
)]
final class CreateDemoUsersCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserRepository $users,
        private readonly UserPasswordHasherInterface $hasher,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $admin = $this->users->findOneBy(['email' => 'admin@sneakers.fr']);
        if (!$admin instanceof User) {
            $admin = new User();
            $admin->setEmail('admin@sneakers.fr');
            $admin->setNom('Admin')->setPrenom('Super');
            $admin->setAdresse('1 rue de la Paix')->setCodePostal('75001')->setVille('Paris');
        }
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->hasher->hashPassword($admin, 'admin123'));
        $this->em->persist($admin);

        $user = $this->users->findOneBy(['email' => 'user@sneakers.fr']);
        if (!$user instanceof User) {
            $user = new User();
            $user->setEmail('user@sneakers.fr');
            $user->setNom('Dupont')->setPrenom('Jean');
            $user->setAdresse('42 avenue Victor Hugo')->setCodePostal('69001')->setVille('Lyon');
        }
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($this->hasher->hashPassword($user, 'user123'));
        $this->em->persist($user);

        $this->em->flush();

        $io->success('Comptes prêts. Connexion :');
        $io->listing([
            'Admin : admin@sneakers.fr / admin123',
            'Client : user@sneakers.fr / user123',
        ]);

        return Command::SUCCESS;
    }
}

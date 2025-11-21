<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Crée un utilisateur administrateur avec le rôle ROLE_ADMIN',
)]
class CreateAdminCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::OPTIONAL, 'Email de l\'administrateur')
            ->addArgument('password', InputArgument::OPTIONAL, 'Mot de passe de l\'administrateur')
            ->addOption('promote', null, InputOption::VALUE_NONE, 'Promouvoir un utilisateur existant en administrateur')
            ->addOption('update', null, InputOption::VALUE_NONE, 'Mettre à jour le mot de passe d\'un administrateur existant')
            ->addOption('super-admin', null, InputOption::VALUE_NONE, 'Créer un super administrateur (ROLE_SUPER_ADMIN)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');
        $password = $input->getArgument('password');
        $promote = $input->getOption('promote');
        $update = $input->getOption('update');

        // Si l'option update est activée, mettre à jour le mot de passe
        if ($update) {
            if (!$email) {
                $email = $io->ask('Email de l\'administrateur à mettre à jour');
            }

            $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

            if (!$user) {
                $io->error(sprintf('Aucun utilisateur trouvé avec l\'email: %s', $email));
                return Command::FAILURE;
            }

            if (!$password) {
                $question = new Question('Nouveau mot de passe');
                $question->setHidden(true);
                $question->setHiddenFallback(false);
                $password = $io->askQuestion($question);
            }

            if (strlen($password) < 6) {
                $io->error('Le mot de passe doit contenir au moins 6 caractères.');
                return Command::FAILURE;
            }

            // Assurer que l'utilisateur a le rôle ROLE_ADMIN
            $roles = $user->getRoles();
            if (!in_array('ROLE_ADMIN', $roles)) {
                $roles[] = 'ROLE_ADMIN';
                $user->setRoles(array_unique($roles));
            }

            $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
            $user->setPassword($hashedPassword);
            $this->entityManager->flush();

            $io->success(sprintf('Le mot de passe de l\'administrateur %s a été mis à jour avec succès !', $email));
            return Command::SUCCESS;
        }

        // Si l'option promote est activée, promouvoir un utilisateur existant
        if ($promote) {
            if (!$email) {
                $email = $io->ask('Email de l\'utilisateur à promouvoir');
            }

            $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

            if (!$user) {
                $io->error(sprintf('Aucun utilisateur trouvé avec l\'email: %s', $email));
                return Command::FAILURE;
            }

            $roles = $user->getRoles();
            if (!in_array('ROLE_ADMIN', $roles)) {
                $roles[] = 'ROLE_ADMIN';
                $user->setRoles(array_unique($roles));
                $this->entityManager->flush();
                $io->success(sprintf('L\'utilisateur %s a été promu administrateur avec succès !', $email));
            } else {
                $io->info(sprintf('L\'utilisateur %s est déjà administrateur.', $email));
            }

            return Command::SUCCESS;
        }

        // Créer un nouvel utilisateur administrateur
        if (!$email) {
            $email = $io->ask('Email de l\'administrateur');
        }

        // Vérifier si l'utilisateur existe déjà
        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($existingUser) {
            $io->error(sprintf('Un utilisateur avec l\'email %s existe déjà.', $email));
            return Command::FAILURE;
        }

        if (!$password) {
            $question = new Question('Mot de passe de l\'administrateur');
            $question->setHidden(true);
            $question->setHiddenFallback(false);
            $password = $io->askQuestion($question);
        }

        if (strlen($password) < 6) {
            $io->error('Le mot de passe doit contenir au moins 6 caractères.');
            return Command::FAILURE;
        }

        // Créer l'utilisateur
        $user = new User();
        $user->setEmail($email);
        
        // Vérifier si c'est un super admin (option --super-admin)
        $isSuperAdmin = $input->getOption('super-admin') ?? false;
        $user->setRoles($isSuperAdmin ? ['ROLE_SUPER_ADMIN'] : ['ROLE_ADMIN']);

        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $roleText = $isSuperAdmin ? 'super administrateur' : 'administrateur';
        $io->success(sprintf('Le %s %s a été créé avec succès !', $roleText, $email));

        return Command::SUCCESS;
    }
}

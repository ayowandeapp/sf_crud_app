<?php

namespace App\Command;

use App\Entity\Post;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-user',
    description: 'Add a short description for your command',
)]
class CreateUserCommand extends Command
{
    public function __construct(private UserPasswordHasherInterface $hasher, private EntityManagerInterface $em)
    {

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'User email')
            ->addArgument('password', InputArgument::REQUIRED, 'User password')
            // ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');
        $password = $input->getArgument('password');

        // if ($arg1) {
        //     $io->note(sprintf('You passed an argument: %s', $arg1));
        // }

        // if ($input->getOption('option1')) {
        //     // ...
        // }


        $user = new User();
        $user->setEmail($email);
        $user->setPassword(
            $this->hasher->hashPassword($user, $password)
        );
        // $user->addPost($post);

        $post = new Post();
        $post->setTitle('welcome to china');
        $post->setContent('welcome to nigeria content');
        $post->setAuthor($user);
        $this->em->persist($post);
        $this->em->flush();



        $io->success(sprintf('User %s account was created!', $email));

        return Command::SUCCESS;
    }
}

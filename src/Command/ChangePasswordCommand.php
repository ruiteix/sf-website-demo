<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ChangePasswordCommand extends Command
{
    private $passwordEncoder;
    private $entityManager;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $entityManager)
    {
        parent::__construct();

        $this->passwordEncoder = $passwordEncoder;
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('security:user:change-password')
            ->setDescription('Change a user password.')
            ->addArgument('email', InputArgument::REQUIRED, 'The user email')
            ->addArgument('password', InputArgument::REQUIRED, 'The new password (if blank, will be interactively asked)')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = $input->getArgument('email');

        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'email' => $email,
        ]);

        if (!$user) {
            throw new \Exception('Unable to find a matching User for given e-mail address');
        }

        $password = $this->passwordEncoder->encodePassword($user, $input->getArgument('password'));

        $user->setPassword($password);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $output->writeln(sprintf('<comment>Updated user %s password</comment>', $email));

        return Command::SUCCESS;
    }

    /**
     * {@inheritdoc}
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $questions = [];

        if (!$input->getArgument('email')) {
            $question = new Question('Please enter email:');
            $questions['email'] = $question;
        }

        if (!$input->getArgument('password')) {
            $question = new Question('Please enter new password:');
            $question->setValidator(function ($password) {
                if (empty($password)) {
                    throw new \Exception('Password can not be empty');
                }

                return $password;
            });
            $question->setHidden(true);
            $questions['password'] = $question;
        }

        foreach ($questions as $name => $question) {
            $answer = $this->getHelper('question')->ask($input, $output, $question);
            $input->setArgument($name, $answer);
        }
    }
}

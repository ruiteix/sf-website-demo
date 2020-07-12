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
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var EntityManagerInterface
     */
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
    protected function configure(): void
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
        $email = $this->getStringArgument('email', $input);
        $password = $this->getStringArgument('password', $input);

        /** @var User|null $user */
        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'email' => $email,
        ]);

        if (!$user) {
            throw new \RuntimeException('Unable to find a matching User for given e-mail address');
        }

        $password = $this->passwordEncoder->encodePassword($user, $password);

        $user->setPassword($password);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $output->writeln(sprintf('<comment>Updated user %s password</comment>', $email));

        return Command::SUCCESS;
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings("PMD.CyclomaticComplexity")
     */
    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $questions = [];

        if (!$input->getArgument('email')) {
            $question = new Question('Please enter email:');
            $questions['email'] = $question;
        }

        if (!$input->getArgument('password')) {
            $question = new Question('Please enter new password:');
            $question->setValidator(function (string $password): string {
                if (empty($password)) {
                    throw new \RuntimeException('Password can not be empty');
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

    private function getStringArgument(string $name, InputInterface $input): string
    {
        $value = $input->getArgument($name);
        if (!\is_string($value)) {
            throw new \RuntimeException(sprintf('Invalid input value for "%s"', $name));
        }

        return $value;
    }
}

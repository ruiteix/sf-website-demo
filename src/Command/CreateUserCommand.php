<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class CreateUserCommand extends Command
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
        $help = <<<'EOT'
The <info>user:create</info> command creates a user:

  <info>php %command.full_name% toto@demo.com</info>

This interactive shell will ask you for a email/password.

EOT;
        $this
            ->setName('security:user:create')
            ->setDescription('Create a user.')
            ->setDefinition([
                new InputArgument('email', InputArgument::REQUIRED, 'The email'),
                new InputArgument('password', InputArgument::REQUIRED, 'The password'),
                new InputArgument('admin', InputArgument::REQUIRED, 'Set the user as admin (ROLE_ADMIN)'),
            ])
            ->setHelp($help);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = $this->getStringArgument('email', $input);
        $password = $this->getStringArgument('password', $input);
        $admin = $this->getStringArgument('admin', $input);

        $user = (new User())
            ->setEmail($email)
            ->setRoles('yes' === strtolower($admin) ? ['ROLE_ADMIN'] : ['ROLE_USER'])
        ;

        $password = $this->passwordEncoder->encodePassword($user, $password);

        $user->setPassword($password);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $output->writeln(sprintf('Created user <comment>%s</comment>', $email));

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
            $question = new Question('Please choose a password:');
            $question->setValidator(function (string $password): string {
                if (empty($password)) {
                    throw new \Exception('Password can not be empty');
                }

                return $password;
            });
            $question->setHidden(true);
            $questions['password'] = $question;
        }

        if (!$input->getArgument('admin')) {
            $question = new ChoiceQuestion('Admin user:', ['yes', 'no'], 'yes');
            $questions['admin'] = $question;
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
            throw new \RuntimeException(sprintf('Invalide input value for "%s"', $name));
        }

        return $value;
    }
}

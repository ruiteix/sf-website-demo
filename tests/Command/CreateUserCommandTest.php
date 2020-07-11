<?php

namespace App\Tests\Command;

use App\Command\CreateUserCommand;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class CreateUserCommandTest extends KernelTestCase
{
    /**
     * @var string[]
     */
    private $userData = [
        'email' => 'chuck@norris.com',
        'password' => 'password',
    ];

    /**
     * @dataProvider isAdminDataProvider
     */
    public function testCreateUserNonInteractive(bool $isAdmin): void
    {
        $input = $this->userData;
        $input['admin'] = $isAdmin ? 'yes' : 'no';
        $this->executeCommand($input);

        $this->assertUserCreated($isAdmin);
    }

    /**
     * @dataProvider isAdminDataProvider
     *
     * This test doesn't provide all the arguments required by the command, so
     * the command runs interactively and it will ask for the value of the missing
     * arguments.
     * See https://symfony.com/doc/current/components/console/helpers/questionhelper.html#testing-a-command-that-expects-input
     */
    public function testCreateUserInteractive(bool $isAdmin): void
    {
        // check if stty is supported, because it's needed for questions with hidden answers
        exec('stty 2>&1', $output, $exitcode);

        if (0 !== $exitcode) {
            $this->markTestSkipped('`stty` is required to test commands.');
        }

        $args = array_values($this->userData);
        $args[] = $isAdmin ? 'yes' : 'no';

        $this->executeCommand([], $args);

        $this->assertUserCreated($isAdmin);
    }

    /**
     * @return \Generator<boolean[]>
     */
    public function isAdminDataProvider(): \Generator
    {
        yield [false];
        yield [true];
    }

    private function assertUserCreated(bool $isAdmin): void
    {
        $container = self::$container;

        /** @var \App\Entity\User $user */
        $user = $container->get(UserRepository::class)->findOneBy(['email' => $this->userData['email']]);
        $this->assertNotNull($user);

        $this->assertSame($this->userData['email'], $user->getEmail());
        $this->assertTrue($container->get('security.password_encoder')->isPasswordValid($user, $this->userData['password']));
        $this->assertSame($isAdmin ? ['ROLE_ADMIN'] : ['ROLE_USER'], $user->getRoles());
    }

    /**
     * This helper method abstracts the boilerplate code needed to test the
     * execution of a command.
     *
     * @param string[] $arguments All the arguments passed when executing the command
     * @param string[] $inputs    The (optional) answers given to the command when it asks for the value of the missing arguments
     */
    private function executeCommand(array $arguments, array $inputs = []): void
    {
        self::bootKernel();

        // this uses a special testing container that allows you to fetch private services
        $command = self::$container->get(CreateUserCommand::class);
        $command->setApplication(new Application(self::$kernel));

        $commandTester = new CommandTester($command);
        $commandTester->setInputs($inputs);
        $commandTester->execute($arguments);
    }
}

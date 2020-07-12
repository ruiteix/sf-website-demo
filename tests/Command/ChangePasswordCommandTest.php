<?php

namespace App\Tests\Command;

use App\Command\ChangePasswordCommand;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ChangePasswordCommandTest extends KernelTestCase
{
    /**
     * @var string[]
     */
    private $userData = [
        'email' => 'demo-1@demo.com',
        'password' => 'newpassword',
    ];

    public function testChangePasswordUserNonInteractive(): void
    {
        $input = $this->userData;
        $this->executeCommand($input);

        $this->assertPasswordChanged();
    }

    public function testChangePasswordUserFailedOnNonExistentUser(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->executeCommand(['email' => 'foo@demo.com', 'password' => 'password']);
    }

    public function testCreateUserInteractive(): void
    {
        // check if stty is supported, because it's needed for questions with hidden answers
        exec('stty 2>&1', $output, $exitcode);

        if (0 !== $exitcode) {
            $this->markTestSkipped('`stty` is required to test commands.');
        }

        $this->executeCommand([], array_values($this->userData));

        $this->assertPasswordChanged();
    }

    private function assertPasswordChanged(): void
    {
        $container = self::$container;

        /** @var \App\Entity\User $user */
        $user = $container->get(UserRepository::class)->findOneBy(['email' => $this->userData['email']]);
        $this->assertNotNull($user);

        $this->assertSame($this->userData['email'], $user->getEmail());
        $this->assertTrue($container->get('security.password_encoder')->isPasswordValid($user, $this->userData['password']));
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
        $command = self::$container->get(ChangePasswordCommand::class);
        $command->setApplication(new Application(self::$kernel));

        $commandTester = new CommandTester($command);
        $commandTester->setInputs($inputs);
        $commandTester->execute($arguments);
    }
}

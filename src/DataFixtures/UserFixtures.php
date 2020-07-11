<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture implements OrderedFixtureInterface
{
    const PREFIX_REF = 'user_';

    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    /**
     * @throws \Exception
     */
    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create('fr_FR');

        $user = (new User())->setEmail('admin@demo.com')
            ->setRoles(['ROLE_ADMIN'])
        ;

        $password = $this->encoder->encodePassword($user, 'admin');
        $user->setPassword($password);

        $manager->persist($user);

        $this->setReference(self::PREFIX_REF.'admin', $user);

        for ($i = 0; $i < 5; ++$i) {
            $user = (new User())->setEmail('demo-'.$i.'@demo.com');

            $password = $this->encoder->encodePassword($user, 'password');
            $user->setPassword($password);

            $manager->persist($user);

            $this->setReference(self::PREFIX_REF.$i, $user);
        }

        $manager->flush();
    }

    public function getOrder()
    {
        return 1;
    }
}

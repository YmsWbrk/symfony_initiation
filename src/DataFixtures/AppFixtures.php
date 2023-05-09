<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

    private $hash;

    public function __construct(UserPasswordHasherInterface $hash)
    {
        $this->hash = $hash;
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setEmail('admin@admin.fr')
            ->setPassword($this->hash->hashPassword($user, 'password'))
            ->setRoles(["ROLE_ADMIN", "ROLE_USER"]);
        $manager->persist($user);

        for ($i = 1; $i <= 10; $i++) {
            $user = new User();
            $user->setEmail('user' . $i . '@user.fr')
                ->setPassword($this->hash->hashPassword($user, 'password'))
                ->setRoles(["ROLE_USER"]);
            $manager->persist($user);
        }



        $manager->flush();
    }
}

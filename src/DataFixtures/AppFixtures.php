<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        for ($i = 0; $i<20 ; $i++) {
            $user = new User();
            $user->setEmail('randomemail'.$i.'@proton.com');
            $user->setRoles(['ROLE_USER']);
            $user->setPassword('password');
            $manager->persist($user);
        }
        
        $manager->flush();
    }
}

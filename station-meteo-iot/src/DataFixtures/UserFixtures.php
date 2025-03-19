<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $admin = new User();
        $admin->setEmail('admin@station.com');
        $admin->setPassword('$2y$13$vOUvTcrIftSdrrfF7WrmAO4urT.j0pvBh//2W5L8Oqx3HNflA7k0K');  // Remplacez par le hash généré
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setIsVerified(true);
        
        $manager->persist($admin);
        $manager->flush();
    }
} 
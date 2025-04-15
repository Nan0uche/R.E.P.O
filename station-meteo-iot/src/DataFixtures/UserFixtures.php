<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Vérifier si l'utilisateur admin existe déjà
        $existingAdmin = $manager->getRepository(User::class)->findOneBy(['email' => 'admin@station.com']);

        if (!$existingAdmin) {
            // Création de l'utilisateur admin
            $admin = new User();
            $admin->setEmail('admin@station.com');
            $admin->setUsername('admin');
            $admin->setRoles(['ROLE_ADMIN']);
            $admin->setIsVerified(true);

            $hashedPassword = $this->passwordHasher->hashPassword(
                $admin,
                'root'
            );
            $admin->setPassword($hashedPassword);

            $manager->persist($admin);
            $manager->flush();
        }
    }
}
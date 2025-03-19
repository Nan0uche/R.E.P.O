<?php

namespace App\DataFixtures;

use App\Entity\WeatherStation;
use App\Entity\WeatherData;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class WeatherFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // Récupérer l'utilisateur admin
        $admin = $manager->getRepository(User::class)->findOneBy(['email' => 'admin@station.com']);

        // Créer une station météo
        $station = new WeatherStation();
        $station->setName('Station Principale');
        $station->setLocation('Jardin');
        $station->setDescription('Station météo principale du jardin');
        $station->setIsActive(true);
        $station->setUser($admin);
        
        $manager->persist($station);

        // Créer quelques données météo
        for ($i = 0; $i < 5; $i++) {
            $data = new WeatherData();
            $data->setTemperature(20 + rand(-5, 5));
            $data->setHumidity(60 + rand(-10, 10));
            $data->setPressure(1013 + rand(-10, 10));
            $data->setTimestamp(new \DateTime("-{$i} hours"));
            $data->setStation($station);
            
            $manager->persist($data);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}

<?php

namespace App\Command;

use App\Service\MqttService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:mqtt:subscribe',
    description: 'Subscribe to MQTT topics for weather stations',
)]
class MqttSubscribeCommand extends Command
{
    private $mqttService;

    public function __construct(MqttService $mqttService)
    {
        parent::__construct();
        $this->mqttService = $mqttService;
    }

    protected function configure(): void
    {
        $this
            ->addOption('topic', 't', InputOption::VALUE_OPTIONAL, 'MQTT topic to subscribe to', '/stationMeteo/+/+')
            ->addOption('broker', 'b', InputOption::VALUE_OPTIONAL, 'MQTT broker host', 'test.mosquitto.org')
            ->addOption('port', 'p', InputOption::VALUE_OPTIONAL, 'MQTT broker port', 1883)
            ->addOption('username', 'u', InputOption::VALUE_OPTIONAL, 'MQTT broker username', '')
            ->addOption('password', 'w', InputOption::VALUE_OPTIONAL, 'MQTT broker password', '')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $topic = $input->getOption('topic');
        $broker = $input->getOption('broker');
        $port = $input->getOption('port');
        $username = $input->getOption('username');
        $password = $input->getOption('password');

        $io->title('MQTT Subscriber for Weather Stations');
        $io->text('Subscribing to topic: ' . $topic);
        $io->text('Broker: ' . $broker . ':' . $port);

        try {
            $this->mqttService->connect();
            
            // Définir le callback pour les messages reçus
            $this->mqttService->onMessage(function($topic, $message) use ($io) {
                // Afficher le message brut pour le débogage
                $io->text("Message reçu brut: " . print_r($message, true));
                
                try {
                    // Extraire les informations du topic
                    $topicParts = explode('/', $topic);
                    if (count($topicParts) !== 4) {
                        throw new \Exception("Format de topic invalide");
                    }
                    
                    $type = $topicParts[2];       // type (humidite) est en position 2
                    $macAddress = $topicParts[3]; // MAC address est en position 3
                    
                    $data = json_decode($message, true);
                    if ($data) {
                        $io->table(
                            ['Topic', 'Type', 'MAC Address', 'Valeur'],
                            [[$topic, $type, $macAddress, json_encode($data, JSON_PRETTY_PRINT)]]
                        );
                    } else {
                        $io->text("Message reçu sur $topic (Type: $type, MAC: $macAddress): $message");
                    }
                } catch (\Exception $e) {
                    $io->error("Erreur de traitement du message: " . $e->getMessage());
                }
            });
            
            $this->mqttService->subscribe($topic);
            $io->success('Successfully subscribed to MQTT topic. Waiting for messages...');
            
            // Keep the command running
            while (true) {
                $this->mqttService->loop();
                usleep(100000); // 100ms pause pour ne pas surcharger le CPU
            }
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    public function handleMessage(string $topic, string $message): void
    {
        try {
            if (!$this->entityManager->isOpen()) {
                $this->logger->warning('EntityManager is closed. Reopening it.');
                $this->entityManager = $this->entityManager->getConnection()->getEntityManager();
            }

            $this->logger->info('Message reçu : ' . $message . ' sur le topic : ' . $topic);

            $topicParts = explode('/', $topic);
            if (count($topicParts) !== 4) {
                $this->logger->error('Format de topic invalide : ' . $topic);
                return;
            }

            $type = $topicParts[2];
            $macAddress = $topicParts[3];

            $station = $this->entityManager->getRepository(WeatherStation::class)
                ->findOneBy(['macAddress' => $macAddress]);

            if (!$station) {
                $this->logger->warning('Station non enregistrée, données ignorées. MAC : ' . $macAddress);
                return;
            }

            $weatherData = new WeatherData();
            $weatherData->setStation($station);
            $weatherData->setType($type);
            $weatherData->setValue($message);
            $weatherData->setTimestamp(new \DateTime());

            $this->entityManager->persist($weatherData);
            $this->entityManager->flush();

            $this->logger->info('Données météo enregistrées pour la station : ' . $macAddress);

        } catch (\Exception $e) {
            $this->logger->error('Erreur lors du traitement du message MQTT : ' . $e->getMessage());
        }
    }
}
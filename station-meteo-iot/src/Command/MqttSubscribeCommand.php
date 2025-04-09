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
            ->addOption('topic', 't', InputOption::VALUE_OPTIONAL, 'MQTT topic to subscribe to', 'weather/station/#')
            ->addOption('broker', 'b', InputOption::VALUE_OPTIONAL, 'MQTT broker host', 'localhost')
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
            $this->mqttService->subscribe($topic);
            
            $io->success('Successfully subscribed to MQTT topic. Waiting for messages...');
            
            // Keep the command running
            while (true) {
                sleep(1);
            }
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
} 
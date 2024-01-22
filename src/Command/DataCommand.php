<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

class DataCommand extends Command
{
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        parent::__construct();

        $this->serializer = $serializer;
    }

    protected function configure(): void
    {
        $this
            ->setName('app:save-api-data')
            ->setDescription('Save API data to a file every minute');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Replace with the actual API URL
//        $apiUrl = 'https://velib-metropole-opendata.smovengo.cloud/opendata/Velib_Metropole/station_information.json';
        $apiUrl = 'https://velib-metropole-opendata.smovengo.cloud/opendata/Velib_Metropole/station_status.json';

        // Fetch data from the API
        $httpClient = HttpClient::create();
        $response = $httpClient->request('GET', $apiUrl);
        $data = $response->toArray();

        // Serialize data to JSON
        $jsonContent = $this->serializer->serialize($data, JsonEncoder::FORMAT);

        // Save data to a file with a timestamp in the filename
        $timestamp = date('YmdHis');
        $filePath = $this->getApplication()->getKernel()->getProjectDir() . '/var/api_data_' . $timestamp . '.json';
        file_put_contents($filePath, $jsonContent);

        $io->success('API data saved successfully in file: ' . $filePath);

        return Command::SUCCESS;
    }
}

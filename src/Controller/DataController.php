<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;

class DataController extends AbstractController
{
    private $kernel;
    private $urlGenerator;

    public function __construct(KernelInterface $kernel,UrlGeneratorInterface $urlGenerator)
    {
        $this->kernel = $kernel;
        $this->urlGenerator = $urlGenerator;
    }


    #[Route('/data', name: 'app_data')]
    public function index(SerializerInterface $serializer): JsonResponse
    {
        // Replace this path with the actual path to your JSON file
        $filePath = $this->getApplication()->getKernel()->getProjectDir() . '/var/api_data_20220117120000.json';

        // Check if the file exists
        if (!file_exists($filePath)) {
            return new JsonResponse(['error' => 'The data file does not exist.'], 404);
        }

        // Read the JSON content from the file
        $jsonContent = file_get_contents($filePath);

        // Deserialize the JSON content into an associative array
        $data = json_decode($jsonContent, true);

        // You can now use $data in your response or further processing

        // Return JSON response
        return new JsonResponse($data);
    }

    #[Route('/data/timestamp/{timestamp}', name: 'app_data_by_timestamp', methods: ['GET'])]
    public function getDataByTimestamp(SerializerInterface $serializer, $timestamp): JsonResponse
    {
        $filePath = $this->kernel->getProjectDir() . "/var/api_data_$timestamp.json";

        if (!file_exists($filePath)) {
            return new JsonResponse(['error' => 'Data not found for the specified timestamp.'], 404);
        }

        $jsonContent = file_get_contents($filePath);
        $data = json_decode($jsonContent, true);

        return new JsonResponse($data);
    }

    #[Route('/data/list', name: 'app_data_list', methods: ['GET'])]
    public function listDataFiles(): JsonResponse
    {
        $varDirectory = $this->kernel->getProjectDir() . '/var';
        $files = scandir($varDirectory);

        $dataFiles = [];

        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'json') {
                $timestamp = pathinfo($file, PATHINFO_FILENAME);

                // Remove "api_data_" prefix from the timestamp
                $timestampWithoutPrefix = str_replace('api_data_', '', $timestamp);

                // Attempt to create a DateTime object
                $dateTime = \DateTime::createFromFormat('YmdHis', $timestampWithoutPrefix);

                if ($dateTime !== false) {
                    $formattedDateTime = $dateTime->format('Y-m-d H:i:s');
                } else {
                    $formattedDateTime = null; // or handle the error as needed
                }

                // Generate URL without "api_data_" prefix
                $url = $this->urlGenerator->generate('app_data_by_timestamp', ['timestamp' => $timestampWithoutPrefix], UrlGeneratorInterface::ABSOLUTE_URL);

                $dataFiles[] = [
                    'timestamp' => $timestamp,
                    'file' => $file,
                    'dateTime' => $formattedDateTime,
                    'url' => $url,
                ];
            }
        }

        return new JsonResponse($dataFiles);
    }

}

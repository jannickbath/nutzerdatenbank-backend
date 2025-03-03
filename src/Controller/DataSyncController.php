<?php

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DataSyncController extends AbstractController
{
    private $entityManager;
    private Connection $connection;

    public function __construct(EntityManagerInterface $em, Connection $connection)
    {
        $this->entityManager = $em;
        $this->connection = $connection;
    }

    /**
     * This endpoint is responsible for backing up the data provided by Microsoft & Co.
     */
    #[Route('/api/sync', name: 'sync_data', methods: ["GET"])]
    public function sync(Request $req): Response
    {
        $url = 'https://graph.microsoft.com/v1.0/users';
        $limit = 600; // Fetch all users at once
        $fullUrl = $url . "?\$top=$limit";

        $client = new  \GuzzleHttp\Client();
        $response = $client->request('GET', $fullUrl, [
            'headers' => [
                'Authorization' => 'Bearer ' . $_ENV["Microsoft_JWT_TOKEN"],
                'ConsistencyLevel' => 'eventual'
            ]
        ]);

        $responseBody = json_decode($response->getBody());
        $users = $responseBody->value;

        foreach ($users as $user) {
            $dataset = ["uuid" => $user->id, "displayName" => $user->displayName, "givenName" => $user->givenName, "jobTitle" => $user->jobTitle, "mail" => $user->mail, "mobilePhone" => $user->mobilePhone, "surname" => $user->surname];
            $columns = [];
            $values = [];

            foreach ($dataset as $key => $value) {
                $columns[] = is_string($key) ? $key : json_encode($key);
                $values[] = json_encode($value, JSON_UNESCAPED_UNICODE);
            }
            
            $columnStr = join(",", $columns);
            $valueStr = join(",", $values);

            // Insert User into Database
            $sql = "REPLACE INTO microsoft_users ($columnStr) VALUES ($valueStr);";
            $this->connection->prepare($sql)->executeQuery();
        }        
        
        return new JsonResponse(["success" => true]);
    }
}

<?php

namespace App\Controller;

use App\Entity\Adress;
use App\Entity\User;
use App\Repository\AdressRepository;
use App\Repository\UserRepository;
use App\SQL\MicrosoftUsers;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class ApiController extends AbstractController
{
    private Request|null $req = null;

    public function __construct(private EntityManagerInterface $entityManager, private UserRepository $uR, private AdressRepository $aR, private MicrosoftUsers $microsoftUsers) { }

    #[Route('/api/users', name: 'get_users', methods: ["GET"])]
    public function get_users(Request $req): JsonResponse
    {
        $this->req = $req;
        $search = $req->query->get("search");
        $filter = $req->query->get("filter");
        $limit = $req->query->get("limit") ?? 4;
        $offset = $req->query->get("offset") ?? 0;
        $id = $req->query->get("id");
        $options = ["limit" => $limit, "offset" => $offset];
        $categories = [];
        $userRepository = $this->entityManager->getRepository(User::class);

        // Specific user id given -> fetch user details
        if ($id != null || $id === 0) {
            $user = $userRepository->getUserById($id);
            
            if (empty($user)) {
                return $this->json([
                    "users" => [],
                    "error" => "User-ID $id does not exist."
                ]);
            }

            return $this->json([
                "users" => $user
            ]);
        }

        if (!empty($filter)) {
            $categories = explode(",", $filter);
        }

        if (empty($search)) {
            return $this->json([
                'users' => $userRepository->getAllUsers($options),
                'code' => 200,
            ]);
        }

        if (!empty($categories)) {
            return $this->json([
                'users' => $userRepository->getUsersBySearchAndCategory($search, $categories, $options),
                'code' => 200,
            ]);
        }
        
        return $this->json([
            'users' => $userRepository->getUsersBySearch($search, $options),
            'code' => 200,
        ]);
    }

    #[Route('/api/validateToken', name: 'validate_token', methods: ["POST"])]
    public function validateToken(Request $req): JsonResponse
    {
        // If the token is not given or the token is invalid you will get a response in the same format. You will get a 4** error response code.
        return new JsonResponse(["code" => 200, "message" => "Success; Token valid"]);
    }

    #[Route('/api/users', name: 'create_user', methods: ["POST"])]
    public function create_user(Request $req): JsonResponse
    {
        $this->req = $req;
        $user = new User();
        $adress = new Adress();

        foreach ($req->request->all() as $key => $value) {
            global $$key; // make the variable accessible outside of the loop
            $$key = $value;
        }

        $this->checkForRequiredUserFields($req);

        // Add adress fields
        $adress->setStreet($street);
        $adress->setPlz($plz);
        $adress->setCity($city);
        $this->updateDb($adress);

        // Add user fields
        $user->setFirstName($first_name);
        $user->setLastName($last_name);
        $user->setEmail($email);
        $user->setPersonnelNumber($personnel_number);
        $user->setAdressId($adress->getId());

        if (!empty($personio_number)) {
            $user->setPersonioNumber($personio_number);
        }

        if (!empty($description)) {
            $user->setDescription($description);
        }

        if (!empty($username)) {
            $user->setUsername($username);
        }

        if (!empty($password)) {
            $user->setPassword($password);
        }

        $this->updateDb($user);
        
        return $this->json([
            'text' => 'Success. User created.',
            'code' => 200,
        ]); 
    }

    #[Route('/api/users/update', name: 'update_user', methods: ["POST"])]
    public function update_user(Request $req): JsonResponse
    {
        foreach ($req->request->all() as $key => $value) {
            global $$key; // make the variable accessible outside of the loop
            $$key = $value;
        }

        // Id is provided by a hidden input field
        $user = $this->uR->find($id);
        $adress = $this->aR->find($user->getAdressId());

        if (empty($user)) {
            return $this->json([
                'text' => 'Couldnt find the requested user.',
                'code' => 400,
            ]);
        }

        $this->checkForRequiredUserFields($req);

        // Add adress fields
        $adress->setStreet($street);
        $adress->setPlz($plz);
        $adress->setCity($city);
        $this->updateDb($adress);

        // Add user fields
        $user->setFirstName($first_name);
        $user->setLastName($last_name);
        $user->setEmail($email);
        $user->setPersonnelNumber($personnel_number);
        $user->setAdressId($adress->getId());

        if (!empty($personio_number)) {
            $user->setPersonioNumber($personio_number);
        }

        if (!empty($description)) {
            $user->setDescription($description);
        }

        if (!empty($username)) {
            $user->setUsername($username);
        }

        if (!empty($password)) {
            $user->setPassword($password);
        }

        $this->updateDb($user);

        return new JsonResponse(["code" => 200]);
    }

    #[Route('/api/db/columns', name: 'list_columns', methods: ["GET"])]
    public function listColumns(Request $req) {
        $tableName = $req->query->get("tableName");
        $columnsList = [];

        if (empty($tableName)) {
            return new JsonResponse(["code" => 400, "text" => "Please provide a table name."]);
        }

        // Hole den SchemaManager
        $connection = $this->entityManager->getConnection();
        $schemaManager = $connection->createSchemaManager();

        // Spalteninformationen abrufen
        $columns = $schemaManager->listTableColumns($tableName);

        foreach ($columns as $col) {
            $name = $col->getName();
            $type = $col->getType()::class;
            $columnsList = [...$columnsList, $name => $type];
        }

        return new JsonResponse(["columns"=> $columnsList]);
    }

    #[Route('/api/db/tables', name: 'list_tables', methods: ["GET"])]
    public function listTables(Request $req) {
        $tableList = [];

        // Hole den SchemaManager
        $connection = $this->entityManager->getConnection();
        $schemaManager = $connection->createSchemaManager();

        $tables = $schemaManager->listTables();

        foreach ($tables as $table) {
            $name = $table->getName();
            $tableList = [...$tableList, $name];
        }

        return new JsonResponse(["tables"=> $tableList]);
    }

    #[Route('/api/microsoft_users', name: 'microsoft_users', methods: ["GET"])]
    public function listMicrosoftUsers(Request $req) {        
        $this->req = $req;
        $search = $req->query->get("search");
        $filter = $req->query->get("filter");
        $limit = $req->query->get("limit") ?? 4;
        $offset = $req->query->get("offset") ?? 0;
        $id = $req->query->get("id");
        $options = ["limit" => $limit, "offset" => $offset];
        $categories = [];
        $userRepository = $this->microsoftUsers;

        // Specific user id given -> fetch user details
        if ($id != null || $id === 0) {
            $user = $userRepository->getUserById($id);
            
            if (empty($user)) {
                return $this->json([
                    "users" => [],
                    "error" => "User-ID $id does not exist."
                ]);
            }

            return $this->json([
                "users" => $user
            ]);
        }

        if (!empty($filter)) {
            $categories = explode(",", $filter);
        }

        if (empty($search)) {
            return $this->json([
                'users' => $userRepository->getAllUsers($options),
                'code' => 200,
            ]);
        }

        if (!empty($categories)) {
            return $this->json([
                'users' => $userRepository->getUsersBySearchAndCategory($search, $categories, $options),
                'code' => 200,
            ]);
        }
        
        return $this->json([
            'users' => $userRepository->getUsersBySearch($search, $options),
            'code' => 200,
        ]);

        return new JsonResponse(json_decode($response->getBody()));
    }

    #[Route('/api/merged_users', name: 'merged_users', methods: ["GET"])]
    public function listMergedUsers(Request $req) {
        $microsoftUsers = $this->getMicrosoftUsers($req);
        $personioUsers = [];
        // etc... / merge data here

        return new JsonResponse($microsoftUsers);
    }

    private function getMicrosoftUsers(Request $req) {
        $url = 'https://graph.microsoft.com/v1.0/users';
        // Microsoft API-Parameters
        $search = $req->query->get("search") ?? ""; //propertyName:propertyValue
        // $limit = $req->query->get("limit") ?? 10;
        $top = $req->query->get("limit") ?? 10; // total page size of 5 users
        $skip = $req->query->get("offset");  // Skip the first x users
        $count = true; // Retrieve total amount of matches
        $expand = "manager"; // Also include related infos about the (manager)
        $filter = "startswith(givenName, 'J')"; // Filters results based on specific criteria

        $urlParams = [];
        $urlParamString = "";

        if (!empty($top)) {
            $urlParams = [...$urlParams, '$top=' . $top];
        }

        if (!empty($skip)) {
            $urlParams = [...$urlParams, '$skip=' . $skip];
        }

        if (!empty($search)) {
            $urlParams = [...$urlParams, '$search=' . $search];
        }

        $urlParamString = "?" . implode("&", $urlParams);
        $fullUrl = $url . $urlParamString;

        $client = new  \GuzzleHttp\Client([
            "http_version" => 2.0,
            "timeout" => 5,
            "connect_timeout" => 2,
            "handler" => \GuzzleHttp\HandlerStack::create(),
        ]);

        $response = $client->request('GET', $fullUrl, [
            'headers' => [
                'Authorization' => 'Bearer ' . $_ENV["Microsoft_JWT_TOKEN"],
                'ConsistencyLevel' => 'eventual'
            ]
        ]);

        return json_decode($response->getBody());
    }

    private function getQueryParameterByPropertyName(string $propertyName, string $search): string {
        return '$search="' . $propertyName . ":" . $search . "\"";
    }

    private function getPersonioUsers() {
        
    }

    private function getSnipeItAssets() {
        
    }

    #[Route('/api/db/add-column', methods: ['GET'])]
    public function addColumn(Request $req): JsonResponse
    {
        // Name und Typ der Spalte aus der Anfrage holen
        $columnName = $req->get('columnName');
        $columnType = $req->get('columnType');
        $tableName = $req->get('tableName');

        // Sicherstellen, dass Spaltenname und Typ vorhanden sind
        if (!$columnName || !$columnType) {
            return new JsonResponse(['error' => 'Invalid column name or type'], 400);
        }

        if (!$tableName) {
            return new JsonResponse(['error' => 'Invalid table name'], 400);
        }

        // Zugriff auf das DBAL-Connection-Objekt über den EntityManager
        $connection = $this->entityManager->getConnection();

        // SQL-Query, um eine Spalte hinzuzufügen
        $sql = sprintf('ALTER TABLE %s ADD %s %s', $tableName, $columnName, $columnType);

        try {
            // SQL-Statement ausführen
            $connection->executeStatement($sql);
            return new JsonResponse(['message' => 'Column added successfully']);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/api/db/delete-column', methods: ['GET'])]
    public function deleteColumn(Request $request): JsonResponse
    {
        // Get column name from the request
        $columnName = $request->get('columnName');
        $tableName = $request->get('tableName');

        // Ensure the column name is provided
        if (!$columnName) {
            return new JsonResponse(['error' => 'Invalid column name'], 400);
        }

        // Ensure the table name is provided
        if (!$tableName) {
            return new JsonResponse(['error' => 'Invalid table name'], 400);
        }

        // Access the DBAL connection via the EntityManager
        $connection = $this->entityManager->getConnection();

        // SQL query to drop the column
        $sql = sprintf('ALTER TABLE %s DROP COLUMN %s', $tableName, $columnName);

        try {
            // Execute the SQL statement
            $connection->executeStatement($sql);
            return new JsonResponse(['message' => 'Column deleted successfully']);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    private function checkForRequiredUserFields(Request $req) {
        foreach ($req->request->all() as $key => $value) {
            global $$key; // make the variable accessible outside of the loop
            $$key = $value;
        }

        if (empty($first_name)) {
            return $this->json([
                'text' => 'Bad Request. Please provide a first name.',
                'code' => 400,
            ]);
        }

        if (empty($last_name)) {
            return $this->json([
                'text' => 'Bad Request. Please provide a last name.',
                'code' => 400,
            ]);
        }

        if (empty($email)) {
            return $this->json([
                'text' => 'Bad Request. Please provide a email.',
                'code' => 400,
            ]);
        }

        if (empty($personnel_number)) {
            return $this->json([
                'text' => 'Bad Request. Please provide a personnel_number.',
                'code' => 400,
            ]);
        }

        // Adress
        if (empty($street)) {
            return $this->json([
                'text' => 'Bad Request. Please provide a street.',
                'code' => 400,
            ]);
        }

        if (empty($plz)) {
            return $this->json([
                'text' => 'Bad Request. Please provide a plz.',
                'code' => 400,
            ]);
        }

        if (empty($city)) {
            return $this->json([
                'text' => 'Bad Request. Please provide a city.',
                'code' => 400,
            ]);
        }
    }

    private function updateDb(object $entity) {
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    private function entityToArr(object $entity) {
        $serializer = new Serializer([new ObjectNormalizer()]);
        return $serializer->normalize($entity);
    }
}

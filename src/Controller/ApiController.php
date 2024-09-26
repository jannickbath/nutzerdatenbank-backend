<?php

namespace App\Controller;

use App\Entity\Adress;
use App\Entity\User;
use App\Repository\AdressRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class ApiController extends AbstractController
{
    private Request|null $req = null;

    public function __construct(private EntityManagerInterface $entityManager, private UserRepository $uR, private AdressRepository $aR) { }

    #[Route('/users', name: 'get_users', methods: ["GET"])]
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

    #[Route('/users', name: 'create_user', methods: ["POST"])]
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

    #[Route('/users/update', name: 'update_user', methods: ["POST"])]
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

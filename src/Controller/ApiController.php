<?php

namespace App\Controller;

use App\Entity\Adress;
use App\Entity\User;
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

    public function __construct(private EntityManagerInterface $entityManager) { }

    #[Route('/test', name: 'app_api')]
    public function index(Request $req): JsonResponse
    {
        $this->req = $req;
        
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/ApiController.php',
        ]);
    }

    #[Route('/get_users', name: 'get_users')]
    public function get_users(Request $req): JsonResponse
    {
        $this->req = $req;
        $userEntities = $this->entityManager->getRepository(User::class)->findAll();
        $userArr = [];
        
        foreach ($userEntities as $entity) {
            $userArr = [ ...$userArr, $this->entityToArr($entity) ];
        }
        
        return $this->json([
            'users' => $userArr,
            'code' => 200,
        ]);
    }

    #[Route('/create_user', name: 'create_user')]
    public function create_user(Request $req): JsonResponse
    {
        $this->req = $req;
        $user = new User();
        $adress = new Adress();

        foreach ($this->req->query->all() as $key => $value) {
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

        $adress->setStreet($street);
        $adress->setPlz($plz);
        $adress->setCity($city);
        $this->updateDb($adress);

        $user = new User();
        $user->setFirstName($first_name);
        $user->setLastName($last_name);
        $user->setEmail($email);
        $user->setPersonnelNumber($personnel_number);
        $user->setAdressId($adress->getId());

        if (!empty($personioNumber)) {
            $user->setPersonioNumber($personioNumber);
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

    private function updateDb(object $entity) {
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    private function entityToArr(object $entity) {
        $serializer = new Serializer([new ObjectNormalizer()]);
        return $serializer->normalize($entity);
    }
}

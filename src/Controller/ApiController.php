<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class ApiController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager) { }

    #[Route('/test', name: 'app_api')]
    public function index(Request $req): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/ApiController.php',
        ]);
    }

    #[Route('/create_user', name: 'create_user')]
    public function create_user(Request $req): JsonResponse
    {
        $username = $req->query->get("username");

        if (empty($username)) {
            return $this->json([
                'text' => 'Please provide a username.',
                'code' => 500,
            ]); 
        }

        $user = new User();
        $user->setFirstName($username);
        $user->setLastName("Mustermann");
        $user->setEmail($username . "@gmail.com");
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
}

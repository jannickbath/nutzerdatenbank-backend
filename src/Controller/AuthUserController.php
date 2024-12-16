<?php

namespace App\Controller;

use App\Entity\AuthUser;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class AuthUserController extends AbstractController
{
    private $passwordHasher;
    private $entityManager;

    public function __construct(UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $em)
    {
        $this->passwordHasher = $passwordHasher;
        $this->entityManager = $em;
    }

    /**
     * This endpoint is responsible for registering a new user. The login for the user is handled by the jwt-token bundle.
     */
    #[Route('/auth/register', name: 'register', methods: ["POST"])]
    public function register(Request $request): Response
    {
        $username = $request->get('username');
        $password = $request->get('password');

        if (empty($username)) {
            return new Response('Error. Please provide a username.');
        }

        if (empty($password)) {
            return new Response('Error. Please provide a password.');
        }

        $user = new AuthUser();
        $user->setUsername($request->get('username'));

        // Das Passwort vor dem Speichern hashen
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $request->get('password') // Passwort aus dem Request
        );
        $user->setPassword($hashedPassword);

        // Speichere den Benutzer in der Datenbank
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return new Response('User registered successfully');
    }
}

<?php

namespace App\Entity;

use App\Repository\AuthUserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: AuthUserRepository::class)]
class AuthUser implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $username = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private ?array $roles = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getRoles(): array
    {
        // Guarantee every user at least has ROLE_USER
        $roles = $this->roles ?? [];
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(?array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

     /**
     * Symfony >= 5.3: Replace getUsername() with getUserIdentifier()
     */
    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    /**
     * Removes sensitive data from the user.
     * This can be used when you store temporary, sensitive data on the user.
     */
    public function eraseCredentials()
    {
        // If you had any sensitive temporary data, you can clear it here
    }
}

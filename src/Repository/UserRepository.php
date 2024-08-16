<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    private $tableName = "user";

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function getAllUsers(): array
    {        
        return $this->executeSQLQuery("SELECT * FROM $this->tableName;");
    }

    public function getUsersBySearch(string $search): array
    {       
        $sql = "
            SELECT * FROM $this->tableName AS u
              WHERE u.first_name LIKE '%$search%'
                OR u.last_name LIKE '%$search%'
                OR u.username LIKE '%$search%'
                OR u.email LIKE '%$search%'
                OR u.personnel_number LIKE '%$search%'
                OR u.personio_number LIKE '%$search%';
        ";

        return $this->executeSQLQuery($sql);
    }

    private function executeSQLQuery(string $query) {
        $conn = $this->getEntityManager()->getConnection();
        $preparedStatement = $conn->prepare($query);
        $res = $preparedStatement->executeQuery();
        return $res->fetchAllAssociative();
    }
}

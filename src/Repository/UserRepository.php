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

    public function getAllUsers(array $options = []): array
    {   
        $sql = "SELECT * FROM $this->tableName";
        $limit = $options["limit"];
        $offset = $options["offset"];

        if (!empty($limit)) {
            $sql .= " LIMIT $limit";
        }

        if (!empty($offset)) {
            $sql .= " OFFSET $offset";
        }

        return $this->executeSQLQuery($sql . ";");
    }

    public function getUsersBySearch(string $search, array $options = []): array
    {    
        $limit = $options["limit"];
        $offset = $options["offset"];
        $sql = "
            SELECT * FROM $this->tableName AS u
              WHERE u.first_name LIKE '%$search%'
                OR u.last_name LIKE '%$search%'
                OR u.username LIKE '%$search%'
                OR u.email LIKE '%$search%'
                OR u.personnel_number LIKE '%$search%'
                OR u.personio_number LIKE '%$search%'
        ";

        if (!empty($limit)) {
            $sql .= " LIMIT $limit";
        }

        if (!empty($offset)) {
            $sql .= " OFFSET $offset";
        }

        return $this->executeSQLQuery($sql . ";");
    }

    public function getUsersBySearchAndCategory(string $search, array $categories, array $options = []): array
    {       
        $sql = "SELECT * FROM $this->tableName AS u ";
        $limit = $options["limit"];
        $offset = $options["offset"];

        foreach($categories as $key => $category) {
            if ($key === 0) {
                $sql .= "WHERE u.$category LIKE '%$search%' ";
            }else {
                $sql .= "OR u.$category LIKE '%$search% '";
            }
        }

        if (!empty($limit)) {
            $sql .= " LIMIT $limit";
        }

        if (!empty($offset)) {
            $sql .= " OFFSET $offset";
        }

        return $this->executeSQLQuery($sql . ";");
    }

    private function executeSQLQuery(string $query) {
        $conn = $this->getEntityManager()->getConnection();
        $preparedStatement = $conn->prepare($query);
        $res = $preparedStatement->executeQuery();
        return $res->fetchAllAssociative();
    }
}

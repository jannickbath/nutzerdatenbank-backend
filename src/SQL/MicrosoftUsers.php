<?php

namespace App\SQL;

use Doctrine\DBAL\Connection;

class MicrosoftUsers
{
    private $tableName = "microsoft_users";

    public function __construct(private Connection $connection)
    {

    }

    public function getUserById(int $id, array $options = []) {
        $sql = "
        SELECT * FROM $this->tableName WHERE $this->tableName.id=$id
        ";

        return $this->executeSQLQuery($sql . ";"); 
    }

    public function getAllUsers(array $options = []): array
    {   
        $sql = "SELECT * FROM $this->tableName";
        $limit = $options["limit"] ?? "";
        $offset = $options["offset"] ?? "";

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
              WHERE u.surname LIKE '%$search%'
                OR u.givenName LIKE '%$search%'
                OR u.mail LIKE '%$search%'
                OR u.jobTitle LIKE '%$search%'
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
        $preparedStatement = $this->connection->prepare($query);
        $res = $preparedStatement->executeQuery();
        return $res->fetchAllAssociative();
    }
}

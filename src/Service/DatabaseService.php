<?php 

namespace App\Service;
//Accès à la DB, le paramétrage se fait dans config/services.yaml
class DatabaseService
{
    private $pdo;

    public function __construct(string $dsn, string $username, string $password)
    {
        $this->pdo = new \PDO($dsn, $username, $password);
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    public function executeQuery(string $query, array $params)
    {
        $statement = $this->pdo->prepare($query);
        $statement->execute($params);

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }
}

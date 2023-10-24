<?php

namespace App\Controller;

// use Doctrine\Common\ClassLoader;
// use Doctrine\DBAL\Connection;
// use Doctrine\DBAL\Driver;
// use Doctrine\DBAL\Configuration;
// use Doctrine\DBAL\DriverManager;
// //use Doctrine\ORM\Mapping as ORM;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Alias;
use Symfony\Component\HttpFoundation\Response;

class UserController extends AbstractController
{
    #[Route('/user')]
    public function request(Request $request )
    {
        $connection = $this->getConnection();
        $tableExists = $this->executeRequest("SELECT * FROM information_schema.tables WHERE table_schema = 'symfony' AND table_name = 'user' LIMIT 1;", $connection);
        if (empty($tableExists)) {
            $this->executeRequest("CREATE TABLE user (id int, data varchar(255))", $connection);
            $this->executeRequest("INSERT INTO user(id, data) values(1, 'Barack - Obama - White House')", $connection);
            $this->executeRequest("INSERT INTO user(id, data) values(1, 'Britney - Spears - America')", $connection);
            $this->executeRequest("INSERT INTO user(id, data) values(1, 'Leonardo - DiCaprio - Titanic')", $connection);
        }

        if ($request->getMethod() == "GET") {
            $id = $request->get("id");
            $action = $request->get("action");

            if ($action == "delete") {
                $this->executeRequest("DELETE FROM user WHERE id = " . $id, $connection);
            }
        } else if ($request->getMethod() == "POST") {
            $firstname = $request->get("firstname");
            $lastname = $request->get("lastname");
            $address = $request->get("address");

            $this->executeRequest("INSERT INTO user(id, data) values(" . time() . ", '" . $firstname . " - " . $lastname . " - " . $address . "');", $connection);
        }

        $users = $this->executeRequest("SELECT * FROM user;", $connection);

        return $this->render('user.html.twig', [
            'obj' => $request->getMethod(),
            'users' => $users
        ]);
    }

    private function getConnection()
    {
        $connectionParams = [
            'dbname' => 'symfony',
            'user' => 'symfony',
            'password' => '',
            'host' => 'mariadb',
            'driver' => 'pdo_mysql',
        ];

       return DriverManager::getConnection($connectionParams);
    }

    private function executeRequest($sql, $connection)
    {
        $stmt = $connection->prepare($sql);
        return $stmt->executeQuery()->fetchAllAssociative();
    }
}
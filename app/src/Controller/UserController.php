<?php

namespace App\Controller;

use Doctrine\DBAL\DriverManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Alias;

class UserController extends AbstractController
{
    private $connection;
    public function __construct(){
        $this->connection =  $this->getConnection();
        $this->initTable($this->connection);
    }
    #[Route('/user')]
    public function request(Request $request)
    {
        //$action = $request->getMethod();
        $edit = array('id' => 0, 'firstname'=>'','lastname'=>'','address'=>'');
        $action = $request->get("action");

        if($action == 'delete' )
            $this->deleteUser($request);
        else if($action == 'add')
            $this->insertUser($request);
        else if($action == 'edit'){

            $id     = (int) $request->get('id');
            $sql    = "SELECT * FROM users where id = ".$id;
            $stmt   = $this->connection->prepare($sql);
            $user   =  $stmt->executeQuery()->fetchAssociative();
            if($user) $edit = $user;
        }

        $users = $this->executeRequest("SELECT * FROM users", $this->connection);

        return $this->render('user.html.twig', [
            'obj'   => $request->getMethod(),
            'users' => $users,
            'edit'  => $edit
        ]);


    }

    /**
     * check and create table users + add 3 users sample.
     **/
    private function initTable($connection){
        $tableExists = $this->executeRequest("SELECT * FROM information_schema.tables WHERE table_schema = 'symfony' AND table_name = 'users' LIMIT 1;", $connection);
        //$this->executeRequest("DROP TABLE users", $connection);
        if (empty($tableExists)) {

            $this->executeRequest("CREATE TABLE users (
                id  int NOT NULL AUTO_INCREMENT,
                firstname varchar(99) NOT NULL,
                lastname varchar(99) NOT NULL,
                address varchar(255),
                PRIMARY KEY (id)
            )",
            $connection);
            $this->executeRequest("INSERT INTO users(id, firstname ,lastname, address) values(NULL, 'Barack', 'Obama','White House')", $connection);
            $this->executeRequest("INSERT INTO users(id, firstname, lastname, address) values(NULL, 'Britney' , 'Spears' , 'America')", $connection);
            $this->executeRequest("INSERT INTO users(id, firstname, lastname, address) values(NULL, 'Leonardo','DiCaprio'
            ,'Titanic')", $connection);
        }

    }
    /**
     *Add new or update exist user.
     *
     *
     * **/
    private function insertUser(Request $request){

        $firstname  = $request->get("firstname");
        $lastname   = $request->get("lastname");
        $address    = $request->get("address");
        $token      = $request->request->get('token');

        if ($this->isCsrfTokenValid('insert_user', $token)) {
            $id = (int) $request->get("id");

            if( $id > 0){
                $sql = "UPDATE users SET firstname = '{$firstname}', lastname= '{$lastname}', address= '{$address}' WHERE id = ".$id;
                $this->executeRequest($sql, $this->connection);

            } else {
                $sql = "INSERT INTO users(id, firstname, lastname, address) values(NULL, $firstname, $lastname, $address)";
                $this->executeRequest($sql, $this->connection);

            }
        } else {
            die('token wrong');
        }
    }
    private function  deleteUser(Request $request){
        $id     = (int) $request->get("id");
        $token  = $request->get('token');
        if ($this->isCsrfTokenValid('delete_user', $token)) {
            $this->executeRequest("DELETE FROM users WHERE id = " . $id, $this->connection);
        } else {
            die('something wrong');
        }
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
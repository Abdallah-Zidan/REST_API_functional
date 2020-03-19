<?php
require 'DBInterface.php';
require_once 'config.php';

class Database implements DBInterface
{
    function connectToDatabase()
    {
        $conn = new mysqli(HOST, USER, PASSWORD, DATABASE, PORT);
        if ($conn->connect_errno) {
            echo "Failed to connect to MySQL: " . $conn->connect_error;
            exit();
        }
        return $conn;
    }

    function disconnect($link)
    {
        $link->close();
    }

    function selectUsers()
    {
        $conn = $this->connectToDatabase();
        $queryStringSelect = "select * from users;";
        $res = $conn->query($queryStringSelect);
        $this->disconnect($conn);
        return $res;
    }

    function selectUser($id)
    {
        $conn = $this->connectToDatabase();
        $queryStringSelect = "select * from users where id = $id;";
        $res = $conn->query($queryStringSelect);
        $this->disconnect($conn);
        return $res;
    }

    function insertUser($firstName, $email, $gender, $receiveEmails)
    {
        $conn = $this->connectToDatabase();
        $queryStringInsert = "insert into users (first_name,email,gender,receive_emails) values ('$firstName','$email','$gender','$receiveEmails');";
        $res = $conn->query($queryStringInsert);
        $this->disconnect($conn);
        return $res;
    }

    function updateUser($id, $firstName, $email, $gender, $receiveEmails)
    {
        $conn = $this->connectToDatabase();
        $queryStringUpdate = "update users set first_name='$firstName',email = '$email',gender='$gender',receive_emails='$receiveEmails' where id = $id;";
        $res = $conn->query($queryStringUpdate);
        $this->disconnect($conn);
        return $res;
    }

    function deleteUser($id)
    {
        $conn = $this->connectToDatabase();
        $queryStringDelete = "delete from `users` where `id` = $id;";
        $res = $conn->query($queryStringDelete);
        $this->disconnect($conn);
        return $res;
    }
}


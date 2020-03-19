<?php
interface DBInterface {

    function connectToDatabase();

    function disconnect( $link);

    function selectUsers();

    function selectUser($id);

    function insertUser($firstName, $email, $gender, $receiveEmails);

    function deleteUser($id);

    function updateUser($id , $firstName, $email, $gender, $receiveEmails);
}

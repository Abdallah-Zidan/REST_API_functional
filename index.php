<?php
require_once "config.php";
require_once "Database.php";
// expected url pieces localhost / {{foldername}} / index.php / users / id
header("Content-Type: application/json"); // set the response type as json data

$db = new Database(); // database object for queries
$allowed_methods = ["GET", "POST", "PUT", "DELETE"]; // the methods I want to allow on the server
$method = $_SERVER["REQUEST_METHOD"];
$url_pieces = explode("/", $_SERVER["REQUEST_URI"]);
$resource = isset($url_pieces[3]) ? $url_pieces[3] : -1;
$resource_id = isset($url_pieces[4]) ? $url_pieces[4] : -1;
$parameters = "";

// if method is post or put [ update or insert ] we need to get the data from request body
//using file_get_content to read the php input stream and decode it to associative array
if ($method == "POST" || $method == "PUT") {
    $parameters = json_decode(file_get_contents("php://input"), true);
}

//=================== start listening for requests====================//
        validateRequest();
//=================== start listening for requests====================//

/*
 * check if request is valid and required resources exist
 */
function validateRequest()
{
    global $method, $resource, $resource_id, $allowed_methods, $parameters; // getting the global variables in
    // the function
    if (!(in_array($method, $allowed_methods))) {
        returnError("method not allowed", 405);
    } else if ($resource != "users" ) {
        returnError("resource not found", 404);
    } else if (!is_numeric($resource_id)) {
        returnError("resource not found", 404);
    }
    // validate parameters ?
    if ($method == "POST" || $method == "PUT") {
        if (!validParameters($parameters)) {
            returnError("invalid user data ", 400);
        }
    }
    // everything is okay ... handle the request then and call handle request function
    handleRequest();
}

/*
 * validate parameters sent in the body if they match with the required user info
 * this function can be modified to suit your validation constraints
 */
function validParameters($parameters)
{
    return (isset($parameters["first_name"]) && isset($parameters["email"]) && isset($parameters["gender"]) && isset($parameters["receive_emails"]));
}

/*
 * handle the request and return the suitable response
 */
function handleRequest()
{
    global $method, $resource_id, $parameters;
    switch ($method) {
        case "GET":
            // if no resource id then return all users
            if ($resource_id == -1) {
                getAll();
            } else {
                get($resource_id);
            }
            break;
        case "DELETE":
            if ($resource_id == -1) {
                returnError("resource not found", 404);
            } else {
                deleteUser($resource_id);
            }
            break;
        case "PUT":
            if ($resource_id == -1) {
                returnError("resource not found", 404);
            } else {
                updateUser($resource_id, $parameters);
            }
            break;
        case "POST":
            if ($resource_id != -1) {
                returnError("resource not found", 404);
            } else {
               $res= insertUser($parameters);
               if($res){
                   returnData("inserted successfully",201);
               }else{
                   returnError("something went wrong",406);
               }
            }
    }
}

// ================ response functions ================//
/*
 * return error with error message and status code
 * @params $error [error message  ]
 * @params $code [http response code]
 */
function returnError($error, $code)
{
    http_response_code($code);
    $res = json_encode(array("data" => "", "error" => $error));
    die($res);
}

/*
 * return requested data with status code
 * @params $data [associative array containing the data for response]
 * @params $code [http response code]
 */
function returnData($data, $code)
{
    http_response_code($code);
    $res = json_encode(array("data" => $data, "error" => ""));
    die($res);
}

// ================ Database functions ================//

function getAll()
{
    /* get all users in case no id is provided*/
    global $db;
    $users = array();
    $res = $db->selectUsers();
    while ($row = $res->fetch_assoc()) {
        $user = array();
        foreach ($row as $key => $value) {
            $user[$key] = $value;
        }
        $users[] = $user;
    }
    returnData($users, 200);
}

function get($id)
{
    /* get user by id */
    global $db;
    $res = $db->selectUser($id);
    $user = $res->fetch_assoc();
    if (empty($user)) {
        returnError("resource not found", 404);
    } else {
        returnData($user, 200);
    }
}

function deleteUser($id)
{
    global $db;
    // check first if user exists
    $res = $db->selectUser($id);
    $exists = $res->fetch_assoc();
    if (empty($exists)) {
        returnError("couldn't delete that entry", 400);
    } else {
        // if user exists delete him
        $res = $db->deleteUser($id);
        if ($res == TRUE) {
            returnData("deleted successfully", 202);
        }
    }
}

function updateUser($id, $parameters)
{
    global $db;
    // check first if user exists
    $res = $db->selectUser($id);
    $exists = $res->fetch_assoc();
    if (empty($exists)) {
        returnError("resource not found", 404);
    } else {
        // user exists .. update his data
        $db->updateUser($id, $parameters["first_name"], $parameters["email"], $parameters["gender"], $parameters["receive_emails"]);
        returnData("updated successfully", 205);
    }
}

function insertUser($parameters)
{
    global $db;
    // insert a new user
    $res =$db->insertUser($parameters["first_name"], $parameters["email"], $parameters["gender"], $parameters["receive_emails"]);
    if($res == TRUE){
        return true;
    }
}
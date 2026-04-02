<?php
session_start();
require "./assets/config.php";

class propertyWithoutPrefix
{
    public bool $hasPrefix;
    public string $propertyName;
}


function trimPropertyPrefix($key, $prefix): propertyWithoutPrefix
{
    $result = new propertyWithoutPrefix();
    //Check is is prefix
    if (strpos($key, $prefix) === 0) {
        //Remove prefix
        $key = substr($key, strlen($prefix));
        $result->hasPrefix = true;
    }
    $result->propertyName = $key;
    return $result;
}

//Check if these parameters are present
if (!isset($_POST["action"]) || !isset($_POST["table"])) {
    http_response_code(400);
    echo "Invalid usage of function - missing parameters";
    die();
}

//SQL design - action and tables
switch ($_POST["action"]) {
    case 'update':
        switch ($_POST["table"]) {
            case "users":
                //Check if values set
                if (!isset($_POST["email"]) || !isset($_POST["name"]) || !isset($_POST["surname"]) || !isset($_POST["id"])) {
                    http_response_code(400);
                    echo "Invalid usage of function - missing table column parameters";
                    die();
                }

                //Make SQL Update
                $stmt = $conn->prepare("UPDATE users SET email=?, name=?, surname=? WHERE id_users=?");
                $stmt->bind_param("sssi", $_POST["email"], $_POST["name"], $_POST["surname"], $_POST["id"]);
                if ($stmt->execute()) {
                    http_response_code(201);
                    echo "Entry updated.";
                    die();
                } else {
                    http_response_code(400);
                    echo "Entry could not be updated.";
                    die();
                };
            case "schools":
                //Check if values set
                if (!isset($_POST["name"]) || !isset($_POST["address"]) || !isset($_POST["id"])) {
                    http_response_code(400);
                    echo "Invalid usage of function - missing table column parameters";
                    die();
                }

                //Make SQL Update
                $stmt = $conn->prepare("UPDATE schools SET name=?, address=? WHERE id_schools=?");
                $stmt->bind_param("ssi", $_POST["name"], $_POST["address"], $_POST["id"]);
                if ($stmt->execute()) {
                    http_response_code(201);
                    echo "Entry updated.";
                    die();
                } else {
                    http_response_code(400);
                    echo "Entry could not be updated.";
                    die();
                };
            default:
                http_response_code(400);
                echo "Invalid usage of function - invalid table parameter";
                die();
        }
    default:
        http_response_code(400);
        echo "Invalid usage of function - invalid action parameter";
        die();
} ?>
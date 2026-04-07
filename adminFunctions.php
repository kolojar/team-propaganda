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
    case 'getSchools': {
        //Check if query tag present -> ALTER TABLE schools ADD FULLTEXT(name); ALTER TABLE schools ADD FULLTEXT(address);
        if (!isset($_POST["query"])) {
            http_response_code(400);
            echo "Invalid usage of function - missing table column parameters";
            die();
        }

        //Make SQL Query
        $stmt = $conn->prepare("SELECT schools.id_schools, schools.name, schools.address,
        -- Get score (search order)
        (
            -- Find best name match using FULLTEXT search
            MATCH(schools.name) AGAINST(? IN NATURAL LANGUAGE MODE) * 2 +
             -- Find best address match using FULLTEXT search
            MATCH(schools.address) AGAINST(? IN NATURAL LANGUAGE MODE) * 2 +
            -- Find best parital name match
            (schools.name LIKE ?) +
            -- Find best parital address match
            (schools.address LIKE ?)
        ) AS score
        FROM schools
        WHERE 
        -- Do matching again for each entry for comparsion
        MATCH(schools.name) AGAINST(? IN NATURAL LANGUAGE MODE) OR MATCH(schools.address) AGAINST(? IN NATURAL LANGUAGE MODE) OR schools.name LIKE ? OR schools.address LIKE ?
        -- Order by score and get 20 best
        ORDER BY score DESC LIMIT 20;");

        //Execute SQL
        $stmt->bind_param("ssssssss", $_POST["query"], $_POST["query"], $_POST["query"], $_POST["query"], $_POST["query"], $_POST["query"], $_POST["query"], $_POST["query"]);
        if (!$stmt->execute()) {
            http_response_code(400);
            echo "Entry could not be fetched";
            die();
        }
        if (!$stmt->store_result()) {
            http_response_code(400);
            echo "Entry could not be fetched";
            die();
        }

        //Fetch all schools
        $jsonRecords = [];
        for ($i = 0; $i < $stmt->num_rows; $i++) {
            $stmt->bind_result($id, $name, $address,$_);
            $stmt->fetch();
            $jsonRecords[] = [
                "id" => $id,
                "name" => $name,
                "address" => $address,
            ];
        }

        //Generate JSON
        http_response_code(201);
        echo json_encode($jsonRecords);
        die();
    }
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
                }
                ;
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
                }
                ;
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
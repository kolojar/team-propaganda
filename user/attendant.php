<?php
session_start();
require "../assets/config.php";
require "./userFunctions.php";

if (isset($_POST["action"])) {
    if ($_POST["action"] == "update") {
        //Check if values set
        if (!isset($_POST["name"]) || !isset($_POST["surname"]) || !isset($_POST["school"]) || !isset($_POST["id"])) {
            http_response_code(400);
            echo "Invalid usage of function - missing table column parameters";
            die();
        }

        //Make SQL Update
        echoCheckIfParentMatches($conn, $_POST["id"]);
        $stmt = $conn->prepare("UPDATE attendants_teamPropaganda SET name=?, surname=?, id_schools=? WHERE id_attendants=?");
        $stmt->bind_param("ssii", $_POST["name"], $_POST["surname"], $_POST["school"], $_POST["id"]);
        if ($stmt->execute()) {
            http_response_code(201);
            echo "Entry updated.";
            die();
        } else {
            http_response_code(400);
            echo "Entry could not be updated.";
            die();
        }
    } else if ($_POST["action"] == "delete") {
        //Check if values set
        if (!isset($_POST["id"])) {
            http_response_code(400);
            echo "Invalid usage of function - missing table column parameters";
            die();
        }

        //Security check
        $stmt = $conn->prepare("SELECT COUNT(id_attendants) FROM registered_attendants_teamPropaganda WHERE id_attendants = ?;");
        $stmt->bind_param("i", $_POST["id"]);
        if (!$stmt->execute()) {
            http_response_code(400);
            echo "Entry could not be CHECKED.";
            die();
        }
        $stmt->store_result();
        $stmt->bind_result($count);
        $stmt->fetch();
        if ($count > 0) {
            http_response_code(400);
            echo "has subevents";
            die();
        }

        //Make SQL Update
        echoCheckIfParentMatches($conn, $_POST["id"]);
        $stmt = $conn->prepare("DELETE FROM attendants_teamPropaganda WHERE id_attendants=?");
        $stmt->bind_param("i", $_POST["id"]);
        if ($stmt->execute()) {
            http_response_code(201);
            echo "Entry deleted.";
            die();
        } else {
            http_response_code(400);
            echo "Entry could not be deleted.";
            die();
        }
    } else {
        http_response_code(400);
        echo "Invalid usage of function - missing action parameter";
        die();
    }
}
<?php
session_start();
if (!isset($_SESSION["userId"])) {
    header("Location: ./index.php");
    exit();
}
require "../assets/config.php";
require "./userFunctions.php";

if (isset($_POST["action"])) {
    if ($_POST["action"] == "update") {
        //Check if values set
        if (!isset($_POST["seats"], $_POST["id"], $_POST["name"], $_POST["description"])) {
            http_response_code(400);
            echo "Neplatné použití funkce - chybí parametr";
            die();
        }
        $stmt = $conn->prepare("UPDATE sites_teamPropaganda NATURAL JOIN presentations_teamPropaganda SET seats=?, name=?, description=? WHERE id_sites=?");
        $stmt->bind_param("sssi", $_POST["seats"], $_POST["name"], $_POST["description"], $_POST["id"]);
        if ($stmt->execute()) {
            http_response_code(201);
            echo "Úspěšně zapsáno do databáze.";
            die();
        } else {
            http_response_code(400);
            echo "Data se nepodařilo zapsat do databáze.";
            die();
        }
    } else if ($_POST["action"] == "delete") {
        if (!isset($_POST["id"])) {
            http_response_code(400);
            echo "Neplatné použití funkce - chybí parametr";
            die();
        }
        if (!$conn->query("DELETE presentations_teamPropaganda, sites_teamPropaganda FROM presentations_teamPropaganda NATURAL JOIN sites_teamPropaganda WHERE id_sites = " . $_POST["id"])) {
            http_response_code(400);
            echo "Data se nepodařilo smazat z databáze.";
            die();
        } else {
            http_response_code(201);
            echo "Úspěšně smazáno z databáze.";
            die();
        }
    } else {
        http_response_code(400);
        echo "Neplatné použití funkce - neplatná akc";
        die();
    }
}

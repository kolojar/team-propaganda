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
        if (!isset($_POST["electricity"]) || !isset($_POST["seats"]) || !isset($_POST["id"])) {
            http_response_code(400);
            echo "Neplatné použití funkce - chybí parametr";
            die();
        }
        $stmt = $conn->prepare("UPDATE sites_teamPropaganda SET electricity=?, seats=? WHERE id_sites=?");
        $stmt->bind_param("ssi", $_POST["electricity"], $_POST["seats"], $_POST["id"]);
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
        if (!$conn->query("DELETE FROM sites_teamPropaganda WHERE id_sites=" . $_POST["id"])) {
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

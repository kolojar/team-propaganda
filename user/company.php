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
        if (!isset($_POST["name"]) || !isset($_POST["short_info"]) || !isset($_POST["long_info"]) || !isset($_POST["id"])) {
            http_response_code(400);
            echo "Neplatné použití funkce - chybí parametr";
            die();
        }
        $stmt = $conn->prepare("UPDATE companies_teamPropaganda SET name=?, short_info=?, long_info=? WHERE id_companies=?");
        $stmt->bind_param("sssi", $_POST["name"], $_POST["short_info"], $_POST["long_info"], $_POST["id"]);
        if ($stmt->execute()) {
            http_response_code(201);
            echo "Entry updated.";
            die();
        } else {
            http_response_code(400);
            echo "Entry could not be updated.";
            die();
        }
    } else {
        http_response_code(400);
        echo "Neplatné použití funkce - neplatná akc";
        die();
    }
}
if (isset($_FILES["files"])) {
    $fcont = file_get_contents($_FILES["files"]["tmp_name"][0]);
    $stmt = $conn->prepare("UPDATE companies_teamPropaganda SET icon=? WHERE id_companies=?");
    $stmt->bind_param("si", $fcont, $_POST["id"]);
    if ($stmt->execute()) {
        echo "Soubor uložen.";
        die;
    } else {
        http_response_code(400);
        echo "Soubor se nepovedlo uložit.";
        die;
    }
}

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
        if (!isset($_POST["name"], $_POST["short_info"], $_POST["long_info"], $_POST["id"])) {
            http_response_code(400);
            echo "Neplatné použití funkce - chybí parametr";
            die();
        }
        $stmt = $conn->prepare("UPDATE companies_teamPropaganda SET name=?, short_info=?, long_info=? WHERE id_companies=?");
        $stmt->bind_param("sssi", $_POST["name"], $_POST["short_info"], $_POST["long_info"], $_POST["id"]);
        if ($stmt->execute()) {
            http_response_code(201);
            echo "Úspěšně zapsáno do databáze.";
            die();
        } else {
            http_response_code(400);
            echo "Data se nepodařilo zapsat do databáze.";
            die();
        }
    } else if ($_POST["action"] == "getFields") {
        echo json_encode($conn->query("SELECT * FROM fields_teamPropaganda")->fetch_all(MYSQLI_ASSOC));
    } else if ($_POST["action"] == "addFields") {
        if (!isset($_POST["id"], $_POST["fields"])) {
            http_response_code(400);
            echo "Neplatné použití funkce - chybí parametr";
            die;
        }
        $fields = json_decode($_POST["fields"]);
        $insert = [];
        foreach ($fields as $field) {
            $insert[] = "(" . $_POST["id"] . ", " . $field . ")";
        }
        if (!$conn->query("DELETE FROM companies_fields_teamPropaganda WHERE id_companies = " . $_POST["id"])) {
            http_response_code(400);
            echo "Data se nepodařilo upravit v databázi.";
            die;
        }

        if (!$conn->query("INSERT INTO companies_fields_teamPropaganda (id_companies, id_fields) VALUES " . join(", ", $insert))) {
            http_response_code(400);
            echo "Data v databázi se nepodařilo upravit.";
            die;
        }
        echo "Úspěšně zapsáno do databáze.";
        die;
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

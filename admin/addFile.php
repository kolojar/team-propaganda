<?php
require "../assets/config.php";
session_start();

if (isset($_FILES["files"])) {
    if (isset($_POST["place"])) {
        if (move_uploaded_file($_FILES["files"]["tmp_name"][0], "../files/" . $_POST["place"] . "/" . $_POST["id"] . "." . explode(".", $_POST["place"])[1])) {
            echo "Soubor úspěšně nahrán";
            exit;
        } else {
            http_response_code(400);
            echo "Soubor se nepodařilo nahrát";
            exit;
        }
    }
    if (file_exists("../files/" . $_FILES["files"]["name"][0])) {
        http_response_code(400);
        echo "Soubor již existuje.";
        exit;
    }
    if (move_uploaded_file($_FILES["files"]["tmp_name"][0], "../files/" . $_FILES["files"]["name"][0])) {
        if (!$conn->query("INSERT INTO `files_teamPropaganda`(`name`, `isNILE`) VALUES ('" . $_FILES["files"]["name"][0] . "', " . $_POST["isNILE"] . ")")) {
            http_response_code(400);
            echo "Soubor se nepodařilo zapsat do databáze.";
            die;
        }
        echo "Soubor úspěšně nahrán.";
        exit;
    } else {
        http_response_code(400);
        echo "Soubor se nepodařilo nahrát.";
        exit;
    }
} else if (isset($_POST["name"])) {
    if (is_dir("../files/" . $_POST["name"])) {
        http_response_code(400);
        echo "Složka s tímto názvem již existuje.";
        exit;
    }
    if (mkdir("../files/" . $_POST["name"])) {
        if (!$conn->query("INSERT INTO `files_teamPropaganda`(`name`, `isNILE`, `isDir`) VALUES (" . $_FILES["files"]["name"][0] . ", " . $_POST["isNILE"] . ",1)")) {
            http_response_code(400);
            echo "Složku se nepodařilo zapsat do databáze.";
            die;
        }
        echo "Složka vytvořena úspěšně.";
        exit;
    } else {
        http_response_code(400);
        echo "Nepodařilo se vytvořit složku.";
        exit;
    }
} else {
    http_response_code(400);
    echo "Nebyla poslána žádná data.";
    exit;
}

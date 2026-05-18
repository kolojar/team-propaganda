<?php
require "../assets/config.php";
session_start();
//echo $_POST["rmdir"] . "<br>";
if (isset($_POST["name"]) && file_exists($_POST["name"])) {
    if (isset($_POST["isNILE"])) {
        //sql delete
        if (!$conn->query("DELETE FROM `files_teamPropaganda` WHERE name = " . $_POST["name"])) {
            http_response_code(400);
            echo "Nepodařilo se smazat data z databáze.";
            die;
        }
    }
    if (unlink($_POST["name"])) {
        echo "Soubor smazán úspěšně";
        exit;
    } else {
        http_response_code(400);
        echo "Soubor se nepodařilo smazat";
        exit;
    }
} else if (isset($_POST["rmdir"]) && is_dir($_POST["rmdir"])) {
    $files = array_diff(scandir($_POST["rmdir"]), array('.', '..'));
    if (!$conn->query("DELETE FROM `files_teamPropaganda` WHERE name = " . $_POST["name"])) {
        http_response_code(400);
        echo "Nepodařilo se smazat data z databáze.";
        die;
    }
    foreach ($files as $file) {
        unlink($_POST["rmdir"] . "/$file");
    }
    rmdir($_POST["rmdir"]);
} else {
    http_response_code(400);
    echo "Nebyla poslána žádná data.";
    exit;
}

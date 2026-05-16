<?php
require "../assets/config.php";
session_start();

if (isset($_GET["file"])) {
    $file = $_GET["file"];
    if (count(explode("/", $file)) > 1) {
        header("Content-Type: " . mime_content_type("./files/$file"));
        header('Content-Disposition: filename="' . explode("/", $file)[0] . '"');
        readfile("./files/$file");
    } else {
        header("Content-Type: " . mime_content_type("./files/$file"));
        header('Content-Disposition: filename="' . $file . '"');
        readfile("./files/$file");
    }
} else {
    http_response_code(400);
    echo "Nebyla poslána žádná data.";
    die;
}

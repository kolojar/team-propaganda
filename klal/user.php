<?php
require "../assets/config.php";
session_start();
if (!isset($_SESSION["userId"])) {
    if (isset($_SESSION["login"])) {
        $_SESSION["userId"] = $conn->query("SELECT id_users FROM users_teamPropaganda WHERE email = " . $_SESSION["login"]);
        $_SESSION["login"] = null;
    } else if (isset($_SESSION["signup"])) {
        $stmt = $comm->prepare("INSERT INTO users_teamPropaganda (name, surname, email, id_schools) VALUES (?, ?, ?, ?)");
        $stmt->bind_params("sssi", $_SESSION["name"], $_SESSION["surname"], $_SESSION["signup"], $_SESSION["id_schools"]);
        $stmt->execute();
        $_SESSION["userId"] = $stmt->insert_id;
        $_SESSION["name"] = null;
        $_SESSION["surname"] = null;
        $_SESSION["signup"] = null;
        $_SESSION["id_schools"] = null;
    }
}

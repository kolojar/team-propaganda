<?php
require "../assets/config.php";
require "./adminFunctions.php";
session_start();

if (isset($_SESSION["userId"])) {
    header("Location: ./admin.php");
    die;
}

if (isset($_POST["password"]) && isset($_POST["email"])) {
    $pass = hash("sha256", $_POST["password"]);
    $stmt = $conn->prepare("SELECT id_users FROM `password_user_teamPropaganda` NATURAL JOIN users_teamPropaganda WHERE password = ? AND email = ?");
    $stmt->bind_param("ss", $pass, $_POST["email"]);
    if (!$stmt->execute()) {
        http_response_code(400);
        echo "Nepodařilo se získat data z databáze.";
        die;
    }
    $stmt->store_result();
    $stmt->bind_result($_SESSION["userId"]);
    if (!isset($_SESSION["user"])) {
        http_response_code(400);
        echo "Nesprávný email nebo heslo.";
        die;
    }


    die;
}

?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="../formWebScripts/css/sharedStyle.css">
    <link rel="stylesheet" href="../formWebScripts/css/formStyle.css">
    <link rel="stylesheet" href="../formWebScripts/css/tableStyle.css">
    <link rel="stylesheet" href="../assets/style.css">

</head>

<body>
    <header>
        <?php
        setupTitlebarAdmin($conn, "index.php");
        ?>
    </header>
    <form id="form" action="../admin/index.php" method="post">
        <label for="email">Email:</label><input type="text" name="email"><br>
        <label for="password">Heslo</label><input type="password" name="password"><br>
        <input type="submit">
    </form>
    <script type="module">

    </script>
</body>

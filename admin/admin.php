<?php
session_start();
require "../assets/config.php";
require "./adminFunctions.php";
?>

<!DOCTYPE html>
<html lang="cs">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin panel</title>
    <link rel="stylesheet" href="../formWebScripts/css/formStyle.css">
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body class="pageHolder">
    <header>
        <?php setupTitlebarAdmin($conn, "admin.php") ?>
    </header>
    <main>
        <h1>Hlavní menu</h1>
        <?php

        ?>
    </main>
    <footer>

    </footer>
</body>
<script type="module" src="../formWebScripts/js/formScript.js"></script>

</html>

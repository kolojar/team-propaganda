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
    <meta name="form-locales-main" content="../formWebScripts/locales/">
    <title>Admin panel</title>
    <link rel="stylesheet" href="../formWebScripts/css/formStyle.css">
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body class="pageHolder">
    <header>
        <?php setupTitlebarAdmin($conn, "admin.php") ?>
    </header>
    <main form-toggle-limiter="limit" min="2" max="4">
        <h1>Hlavní menu</h1>
        <form-input type="checkbox"></form-input>
        <form-input type="radio"></form-input>
        <form-toggle label-before="TEST" label-after="@TEST" name="test" is-radio></form-toggle>
        <form-toggle label-before="TEST" label-after="@TEST" name="test" indeterminate is-radio></form-toggle>
        <span>A</span>
        <form-toggle label-before="TEST" label-after="@TEST"  name="test" checked is-radio></form-toggle>

        <form-toggle label-before="TEST" label-after="@TEST" name="limit"></form-toggle>
        <form-toggle label-before="TEST" label-after="@TEST" indeterminate name="limit"></form-toggle>
        <form-toggle label-before="TEST" label-after="@TEST" name="limit" checked></form-toggle>
        <form-toggle label-before="TEST" label-after="@TEST" name="limit" checked></form-toggle>
        <form-toggle label-before="TEST" label-after="@TEST" name="limit" checkeds></form-toggle>
        <?php

        ?>
    </main>
    <footer>

    </footer>
</body>
<script type="module" src="../formWebScripts/js/formScript.js"></script>
<script type="module" src="admin.js"></script>
</html>

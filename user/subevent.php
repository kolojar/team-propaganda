<?php
session_start();
require '../assets/config.php';
require './userFunctions.php';
?>
<!DOCTYPE html>
<html lang='cz'>

<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Uživatelský panel</title>
    <link rel='stylesheet' href='../formWebScripts/css/sharedStyle.css'>
    <link rel='stylesheet' href='../formWebScripts/css/formStyle.css'>
    <link rel='stylesheet' href='../assets/style.css'>
    <link rel='stylesheet' href='./user.css'>
</head>

<body class="pageHolder">
    <header>
        <?php setupTitlebarUser($conn) ?>
    </header>
    <main>
        <?php
        //Security check
        if (!isset($_GET["variableSymbol"])) {
            echo "<h1>Nebyl zadán platní variabilní symbol!</h1>";
            echo "<a href='./'><button class='formButton purkynkaButton'>Zpět na domovskou stránku</button></a>";
            die();
        }
        if (!checkIfParentMatches2($conn, $_GET["variableSymbol"])) {
            echo "<h1>Nejste rodičem tohoto zájemce!</h1>";
            echo "<a href='./'><button class='formButton purkynkaButton'>Zpět na domovskou stránku</button></a>";
            die();
        }
        ?>
    </main>
    <script type='module' src='../formWebScripts/js/formScript.js'></script>
    <script src='./subevent.js' type='module'></script>
</body>
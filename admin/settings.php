<?php
session_start();
require "../assets/config.php";
require "./adminFunctions.php";
require "../assets/settingsManager.php";

if (isset($_POST["action"])) {
    if ($_POST["action"] == "set") {
        //Check variables
        if (!isset($_POST["key"]) || !isset($_POST["value"])) {
            http_response_code(400);
            echo "Neplatné použití funkce - chybí parametr";
            die();
        }

        //Do update
        if (!updateJsonSetting($_POST["key"], $_POST["value"])) {
            http_response_code(400);
            echo "Nelze změnit nastavení.";
            die();
        }
        echo "OK";
        die();
    } else {
        http_response_code(400);
        echo "Neplatné použití funkce - neplatná akce";
        die();
    }
}
?>

<!DOCTYPE html>
<html lang="cs">

<head>
    <meta charset="UTF-8">
    <meta name="form-icons-main-db" content="../formWebScripts/formIcons.json">
    <meta name="form-icons-db" content="../assets/formIcons.json">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="form-locales-main" content="../formWebScripts/locales/">
    <title>Seznam škol</title>

    <link rel="stylesheet" href="../formWebScripts/css/formStyle.css">
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body class="pageHolder">
    <header>
        <?php $result = setupTitlebarAdmin($conn, "settings.php") ?>
    </header>
    <main>
        <h1>Správa nastavení</h1>
        <table>
            <tr>
                <th>Akce</th>
                <th>Název nastavení</th>
                <th>Hodnota nastavení</th>
            </tr>
            <?php
            $settings = loadJsonSettings();
            foreach ($settings as $key => $value) {
                echo "<tr>";
                echo "<td class='formButtonBoxTable'><button class='purkynkaButton editSettingBtn' btn='$key' setting-value='$value' setting-key='$key' form-icon='!edit'></button></td>";
                echo "<td>$key</td>";
                echo "<td>$value</td>";
                echo "</tr>";
            }
            ?>
        </table>
    </main>
    <footer>

    </footer>
    <script type="module" src="./settings.js"></script>
</body>
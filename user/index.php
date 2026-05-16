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
    <style>
        ul {
            margin-top: 0px;
        }
    </style>
</head>

<body>
    <header>
        <?php setupTitlebarUser($conn) ?>
    </header>
    <main>
        <fieldset id="userInfo">
            <legend>Informace o Vás</legend>
            <?php
            $_SESSION["userId"] = 6;
            //Get name of current user
            $stmt = $conn->prepare("SELECT name, surname FROM users_teamPropaganda WHERE id_users=?");
            $stmt->bind_param("i", $_SESSION["userId"]);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($name, $surname);
            $stmt->fetch();

            echo "<form-input class='validate' value-id='name' label='Jméno:' type='text' do-change-check='true' value='$name' original-value='$name'></form-input>";
            echo "<form-input class='validate' value-id='surname' label='Přijmení:' type='text' do-change-check='true' value='$surname' original-value='$surname'></form-input>";
            ?>
            <div class='formButtonBoxHolder'>
                <div class='formButtonBox formJustifyLeft'>
                    <button class='formButton purkynkaButton' id='btnChangeEmail'>Převést účet na jiný Email</button>
                </div>
                <div class='formButtonBox formJustifyRight'>
                    <button class='formButton purkynkaButton btnCancel'>Zrušit provedené změny</button>
                    <button class='formButton purkynkaButton btnSave'>Uložit změny</button>
                </div>
            </div>
        </fieldset>

        <?php
        //Get attendants of current user
        $stmt = $conn->prepare("SELECT a.id_attendants, a.name, a.surname, a.id_schools, s.name, s.address FROM attendants_teamPropaganda a JOIN schools_teamPropaganda s ON  a.id_schools = s.id_schools WHERE a.id_parent = ?;");
        $stmt->bind_param("i", $_SESSION["userId"]);
        $stmt->execute();
        $stmt->store_result();
        for ($i = 0; $i < $stmt->num_rows; $i++) {
            $stmt->bind_result($id, $name, $surname, $schoolId, $schoolName, $schoolAddress);
            $stmt->fetch();
            echo "<br>
                <fieldset class='attendantInfo' attendant='$id'>
                <legend>Informace o zájemci: $name $surname</legend>
                <form-input value-id='name' label='Jméno:' class='validate' type='text' do-change-check='true' value='$name' original-value='$name'></form-input>
                <form-input value-id='surname' label='Přijmení:' class='validate' type='text' do-change-check='true' value='$surname' original-value='$surname'></form-input>
                <form-input value-id='school' label='Základní škola:' class='validate schoolValue' type='select' do-change-check='true' original-value='$schoolName → $schoolAddress' value='$schoolName → $schoolAddress' is-case-sensitive-list='false'></form-input>
                <span>Přihlášené akce - kliknutím zobrazíte podrobnosti:</span>
                <ul>";
            echo "</ul>
                <div class='formButtonBoxHolder'>
                <div class='formButtonBox formJustifyLeft'>
                    <button class='formButton purkynkaButton'>Přihlásit na další akce</button>
                </div>
                <div class='formButtonBox formJustifyRight'>
                    <button class='formButton purkynkaButton btnCancel'>Zrušit provedené změny</button>
                    <button class='formButton purkynkaButton btnSave'>Uložit změny</button>
                </div>
                </div>
                </fieldset>";
        }
        ?>
    </main>
    <footer>

    </footer>
    <script type='module' src='../formWebScripts/js/formScript.js'></script>
    <script src='./index.js' type='module'></script>
</body>

</html>
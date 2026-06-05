<?php
session_start();
require "../assets/config.php";
require "./adminFunctions.php";

if (isset($_POST["action"])) {
    if ($_POST["action"] == "update") {
        //Check if values set
        if (!isset($_POST["name"]) || !isset($_POST["surname"]) || !isset($_POST["school"]) || !isset($_POST["id"])) {
            http_response_code(400);
            echo "Neplatné použití funkce - chybí parametr";
            die();
        }

        //Make SQL Update
        $stmt = $conn->prepare("UPDATE attendants_teamPropaganda SET name=?, surname=?, id_schools=? WHERE id_attendants=?");
        if ($stmt->bind_param("ssii", $_POST["name"], $_POST["surname"], $_POST["school"], $_POST["id"]) && $stmt->execute() && $stmt->close()) {
            http_response_code(201);
            echo "Zájemce upraven.";
            die();
        } else {
            http_response_code(400);
            echo "Zájemce nemohl být upraven.";
            die();
        }
    } else if ($_POST["action"] == "delete") {
        //Check if values set
        if (!isset($_POST["id"])) {
            http_response_code(400);
            echo "Neplatné použití funkce - chybí parametr";
            die();
        }

        //Make SQL Update
        $stmt = $conn->prepare("DELETE FROM unregistered_attendants_teamPropaganda WHERE id_registered_attendants=?");
        if ($stmt->bind_param("i", $_POST["id"]) && $stmt->execute() && $stmt->close()) {
            http_response_code(201);
            echo "Zájemce odstraněn.";
            die();
        } else {
            http_response_code(400);
            echo "Zájemce nemohl být odstraněn.";
            die();
        }
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
    <title>Zájemce</title>

    <link rel="stylesheet" href="../formWebScripts/css/formStyle.css">
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body class="pageHolder">
    <header>
        <?php
        setupTitlebarAdmin($conn, "attendant.php")
            ?>
    </header>
    <main>
        <?php
        //Get attendant info
        $stmt = $conn->prepare("SELECT a.name, a.surname, a.id_parent, a.id_schools, u.name, u.surname, u.email, s.name, s.address FROM attendants_teamPropaganda a JOIN users_teamPropaganda u ON a.id_parent = u.id_users JOIN schools_teamPropaganda s ON a.id_schools = s.id_schools WHERE a.id_attendants = ?;");
        if (!$stmt->bind_param("i", $_GET["attendant"]) || !$stmt->execute() || !$stmt->store_result() || $stmt->num_rows != 1 || !$stmt->bind_result($name, $surname, $parentId, $schoolId, $parentName, $parentSurname, $parentEmail, $schoolName, $schoolAddress) || !$stmt->fetch() || !$stmt->close()) {
            echo "<h1>Nelze získat informace o zájemci.</h1>";
            echo "<a href='./admin.php'><button class='purkynkaButton'>Zpět na hlavní stránku</button></a>";
            die();
        }

        //Print HTML
        echo "<h1>Informace o zájemci: $name $surname</h1>";
        echo "<form-input tabindex='1' icon='!userName' label='Křestní jméno:' class='validate' do-change-check type='text' value-id='name' original-value='$name' value='$name' placeholder='$name'></form-input>";
        echo "<form-input tabindex='2' icon='!userSurname' label='Přijmení:' class='validate' do-change-check type='text' value-id='surname' original-value='$surname' value='$surname' placeholder='$surname'></form-input>";
        //echo "<form-input label='Email:' class='attendantValidate' do-change-check type='email' id='email' original-value='$email' value='$email' placeholder='$email'></form-input>";
        echo "<p>Zákonný zástupce: $parentName $parentSurname</p>";
        echo "<p>Email zákonného zástupce: <a href='./sendMail.php?uid=$parentId&isNILE=0'>$parentEmail</a></p>";
        //echo "<p>Základní škola: <a id='schoolIdHolder' schoolId='$schoolId' href='?view=school&school=$schoolId'>$schoolName → $schoolAddress</a> <button class='formButton formWarnColor' id='attendantBtnChangeSchool'>Změnit školu</button></p>";
        echo "<form-input tabindex='3' icon='!school' id='school' label='Základní škola:' class='validate' type='select' do-change-check value-id='school' original-value='$schoolName → $schoolAddress' value='$schoolName → $schoolAddress'></form-input>";
        echo "<div class='formButtonBoxHolder'>";
        echo "<div class='formButtonBox'>";
        echo "<button tabindex='4' class='formButton purkynkaButton btnSave' form-icon='!save'></button>";
        echo "<button tabindex='5' class='formButton purkynkaButton btnCancel' form-icon='!dontSave'></button>";
        echo "<a tabindex='-1' href='./attendants.php'><button tabindex='6' class='formButton purkynkaButton' form-icon='!listTable'><span>Zpět na seznam zájemců</span></button></a>";
        echo "<a tabindex='-1' href='./school.php?school=$schoolId'><button tabindex='7' class='formButton purkynkaButton' form-icon='!school'><span>Zobrazit informace o škole</span></button></a>";
        echo "<a tabindex='-1' href='./user.php?user=$parentId'><button tabindex='8' class='formButton purkynkaButton' form-icon='!parentInfo'><span>Zobrazit informace o zákonném zástupci</span></button></a>";
        echo "</div>";
        echo "</div>";
        ?>
    </main>
    <footer>

    </footer>
</body>
<script type="module" src="../formWebScripts/js/formScript.js"></script>
<script type='module' src='./attendant.js'></script>

</html>
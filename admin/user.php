<?php
session_start();
require "../assets/config.php";
require "./adminFunctions.php";

if (isset($_POST["action"])) {
    if ($_POST["action"] == "update") {
        //Check if values set
        if (!isset($_POST["email"]) || !isset($_POST["name"]) || !isset($_POST["surname"]) || !isset($_POST["school"]) || !isset($_POST["id"])) {
            http_response_code(400);
            echo "Invalid usage of function - missing table column parameters";
            die();
        }

        //Make SQL Update
        $stmt = $conn->prepare("UPDATE users SET email=?, name=?, surname=?, id_schools=? WHERE id_users=?");
        $stmt->bind_param("sssii", $_POST["email"], $_POST["name"], $_POST["surname"], $_POST["school"], $_POST["id"]);
        if ($stmt->execute()) {
            http_response_code(201);
            echo "Entry updated.";
            die();
        } else {
            http_response_code(400);
            echo "Entry could not be updated.";
            die();
        }
    } else {
        http_response_code(400);
        echo "Invalid usage of function - missing action parameter";
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
    <title>Zájemce</title>
    <link rel="stylesheet" href="../formWebScripts/css/sharedStyle.css">
    <link rel="stylesheet" href="../formWebScripts/css/formStyle.css">
    <link rel="stylesheet" href="../formWebScripts/css/tableStyle.css">
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body class="pageHolder">
    <header>
        <?php setupTitlebarAdmin($conn,"user.php") ?>
        <datalist id="userRoles">
            <option label="Správce systému" value="admin"></option>
            <option label="Účetní" value="accountant"></option>
            <option label="Uživatel" value="user"></option>
        </datalist>
    </header>
    <main>
        <?php
        //Get user info
        $id = $_GET["user"];
        $stmt = $conn->prepare("SELECT name, surname, email,role, isNILE, lastLogin FROM users_teamPropaganda WHERE id_users = ?;");
        $stmt->bind_param("i", $_GET["user"]);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($name, $surname,$email,$role,$isNILE, $lastLogin);
        $stmt->fetch();
        $lastLoginFormat = new DateTime($lastLogin)->format(STANDARD_CZECH_DATETIME_FORMAT_FULL);

        //Print HTML
        echo "<h1>Informace o uživateli: $name $surname</h1>";
        echo "<form-input icon='!userName' label='Křestní jméno:' class='validate' do-change-check='true' type='text' value-id='name' original-value='$name' value='$name' placeholder='$name'></form-input>";
        echo "<form-input icon='!userSurname' label='Přijmení:' class='validate' do-change-check='true' type='text' value-id='surname' original-value='$surname' value='$surname' placeholder='$surname'></form-input>";
        echo "<p class='allowSelect'>Email: <a class='allowSelect' href='./sendMail.php?uid=$id&isNILE=$isNILE'>$email</a></p>";
        //echo "<form-input label='Email:' class='attendantValidate' do-change-check='true' type='email' id='email' original-value='$email' value='$email' placeholder='$email'></form-input>";
        //echo "<p>Základní škola: <a id='schoolIdHolder' schoolId='$schoolId' href='?view=school&school=$schoolId'>$schoolName → $schoolAddress</a> <button class='formButton formWarnColor' id='attendantBtnChangeSchool'>Změnit školu</button></p>";
        echo "<form-input icon='!userRole' list='userRoles' is-strict-list='true' label='Role:' class='validate' type='select' do-change-check='true' value-id='role' original-value='$role' value='$role' is-case-sensitive-list='false'></form-input>";
        echo "<p>Naposledy přihlášen: $lastLoginFormat</p>";
        echo "<div class='formButtonBoxHolder'>";
        echo "<div class='formButtonBox'>";
        echo "<button id='btnSave' class='purkynkaButton' form-icon='!save'></button>";
        echo "<button id='btnCancel' class='purkynkaButton' form-icon='!dontSave'></button>";
        echo "<a href='./users.php'><button class='purkynkaButton' form-icon='!listTable'><span>Zpět na seznam uživatelů</span></button></a>";
        echo "<a href='./attendants.php?parent=$id'><button class='purkynkaButton' form-icon='!highlightUsers'><span>Zvýraznit přidružené zájemce</span></button></a>";
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
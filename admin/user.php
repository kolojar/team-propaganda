<?php
session_start();
require "../assets/config.php";
require "./adminFunctions.php";

function echoCheckAdminDelete(mysqli $conn, userRoleType $roleType)
{
    if ($roleType->role != userRole::ADMIN && $roleType->type != userType::GENERIC) {
        return;
    }
    $stmt = $conn->prepare("SELECT COUNT(id_users) FROM users_teamPropaganda WHERE role='ADMIN' AND type='Generic';");
    if (!$stmt->execute() || !$stmt->store_result() || !$stmt->bind_result($count) || !$stmt->fetch() || !$stmt->close()) {
        http_response_code(400);
        echo "Nelze zjisit stav uživatelů.";
        die();
    }
    if ($count <= 1) {
        http_response_code(400);
        echo "Nelze odebrat posledního obecného správce systému.";
        die();
    }
}

if (isset($_POST["action"])) {
    if ($_POST["action"] == "update") {
        //Check if values set
        if (!isset($_POST["name"]) || !isset($_POST["surname"]) || !isset($_POST["role"]) || !isset($_POST["type"]) || !isset($_POST["email"]) || !isset($_POST["id"])) {
            http_response_code(400);
            echo "Neplatné použití funkce - chybí parametr";
            die();
        }

        //Security check
        $roleType = getUserRoleType($conn, $_POST["id"]);
        if (($roleType->role != userRole::{$_POST["role"]} && $roleType->role != userRole::ADMIN) || ($roleType->type != userType::{$_POST["type"]} && $roleType->type == userType::GENERIC)) {
            echoCheckAdminDelete($conn, $roleType);
        }

        //Security check
        $newRole = userRole::{$_POST["role"]};
        $roleType = getUserRoleType($conn, $_SESSION["userId"]);
        if ($roleType->role != $newRole && $roleType->role != userRole::ADMIN) {
            http_response_code(400);
            echo "Na použití této funkce nemáte oprávnění.";
            die();
        }
        $newType = userType::{$_POST["type"]};
        if ($roleType->type != $newType && $roleType->type != userType::GENERIC) {
            http_response_code(400);
            echo "Na použití této funkce nemáte oprávnění.";
            die();
        }

        //Make SQL Update
        $stmt = $conn->prepare("UPDATE users_teamPropaganda SET email=?, name=?, surname=?, role=?, type=? WHERE id_users=?");
        if ($stmt->bind_param("sssssi", $_POST["email"], $_POST["name"], $_POST["surname"], $_POST["role"], $_POST["type"], $_POST["id"]) && $stmt->execute() && $stmt->close()) {
            http_response_code(201);
            echo "Uživatel upraven.";
            die();
        } else {
            http_response_code(400);
            echo "Nebylo možno upravit uživatele.";
            die();
        }
    } else if ($_POST["action"] == "insert") {
        //Check if values set
        if (!isset($_POST["name"]) || !isset($_POST["surname"]) || !isset($_POST["role"]) || !isset($_POST["type"]) || !isset($_POST["email"])) {
            http_response_code(400);
            echo "Neplatné použití funkce - chybí parametr";
            die();
        }

        //Security check
        $newRole = userRole::{$_POST["role"]};
        $roleType = getUserRoleType($conn, $_SESSION["userId"]);
        if ($roleType->role != $newRole && $roleType->role != userRole::ADMIN) {
            http_response_code(400);
            echo "Na použití této funkce nemáte oprávnění.";
            die();
        }
        $newType = userType::{$_POST["type"]};
        if ($roleType->type != $newType && $roleType->type != userType::GENERIC) {
            http_response_code(400);
            echo "Na použití této funkce nemáte oprávnění.";
            die();
        }

        //Make SQL Insert
        $stmt = $conn->prepare("INSERT INTO users_teamPropaganda(email, name, surname, type, role, last_login) VALUES (?,?,?,?,?,CURRENT_TIMESTAMP())");
        if ($stmt->bind_param("sssss", $_POST["email"], $_POST["name"], $_POST["surname"], $_POST["type"], $_POST["role"]) && $stmt->execute() && $stmt->close()) {
            http_response_code(201);
            echo "Uživatel vytvořen.";
            die();
        } else {
            http_response_code(400);
            echo "Uživatel nemohl být vytvořen.";
            die();
        }
    } else if ($_POST["action"] == "delete") {
        //Check if values set
        if (!isset($_POST["id"])) {
            http_response_code(400);
            echo "Neplatné použití funkce - chybí parametr";
            die();
        }

        //Get helper values
        $stmt = $conn->prepare("SELECT role, type FROM users_teamPropaganda WHERE id_users=?");
        if (!$stmt->bind_param("i", $_POST["id"]) || !$stmt->execute() || !$stmt->store_result() || !$stmt->bind_result($role, $type) || !$stmt->fetch() || !$stmt->close()) {
            http_response_code(400);
            echo "Nelze získat informace o uživateli.";
            $stmt->close();
            die();
        }
        logToConsole($_POST["id"] . $role . $type);

        //Security check
        $roleType = getUserRoleType($conn, $_POST["id"]);
        if (($roleType->role != userRole::{$role} && $roleType->role != userRole::ADMIN) || ($roleType->type != userType::{$type} && $roleType->type == userType::GENERIC)) {
            echoCheckAdminDelete($conn, $roleType);
        }

        //Make SQL Update
        $stmt = $conn->prepare("DELETE FROM users_teamPropaganda WHERE id_users=?");
        if ($stmt->bind_param("i", $_POST["id"]) && $stmt->execute() && $stmt->close()) {
            http_response_code(201);
            echo "Uživatel odstraněn.";
            die();
        } else {
            http_response_code(400);
            echo "Uživatel nemohl být odstraněn.";
            $stmt->close();
            die();
        }
    } else {
        http_response_code(400);
        echo "Neplatné použití funkce - Neplatná akce";
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
    <title>Uživatel</title>
    <link rel="stylesheet" href="../formWebScripts/css/formStyle.css">
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body class="pageHolder">
    <header>
        <?php $result = setupTitlebarAdmin($conn, "user.php") ?>
    </header>
    <main>
        <?php
        //List all roles
        echo "<datalist id='roles'>";
        if ($result->roleType->role == userRole::ADMIN) {
            foreach (userRole::cases() as $key => $value) {
                $name = $value->name;
                $valueValue = $value->value;
                echo "<option label='$valueValue' value='$name'></option>";
            }
        } else {
            $name = $result->roleType->role->name;
            $valueValue = $result->roleType->role->value;
            echo "<option label='$valueValue' value='$name'></option>";
        }
        echo "</datalist>";

        //List all types
        echo "<datalist id='types'>";
        if ($result->roleType->type == userType::GENERIC) {
            foreach (userType::cases() as $key => $value) {
                $name = $value->name;
                $valueValue = $value->value;
                echo "<option label='$valueValue' value='$name'></option>";
            }
        } else {
            $name = $result->roleType->type->name;
            $valueValue = $result->roleType->type->value;
            echo "<option label='$valueValue' value='$name'></option>";
        }
        echo "</datalist>";

        //Get user info
        $id = $_GET["user"];
        $name = "";
        $surname = "";
        $email = "";
        $role = "";
        $type = "";
        $lastLogin = new DateTime()->format('Y-m-d H:i:s');
        $exists = "true";
        if (isset($_GET["newUser"])) {
            echo "<h1>Vytvořit nového uživatele</h1>";
            $exists = "false";
        } else {
            $stmt = $conn->prepare("SELECT name, surname, email,role, type, last_login FROM users_teamPropaganda WHERE id_users = ?;");
            if (!$stmt->bind_param("i", $_GET["user"]) || !$stmt->execute() || !$stmt->store_result() || $stmt->num_rows != 1 || !$stmt->bind_result($name, $surname, $email, $role, $type, $lastLogin) || !$stmt->fetch() || !$stmt->close()) {
                echo "<h1>Nelze získat informace o uživateli.</h1>";
                echo "<a href='./admin.php'><button class='purkynkaButton'>Zpět na hlavní stránku</button></a>";
                die();
            }
            $lastLoginFormat = new DateTime($lastLogin)->format(STANDARD_CZECH_DATETIME_FORMAT_FULL);
            echo "<h1>Informace o uživateli: $name $surname</h1>";
        }

        //Print HTML
        echo "<form-input icon='!userName' label='Křestní jméno:' class='validate' do-change-check type='text' value-id='name' original-value='$name' value='$name' placeholder='$name'></form-input>";
        echo "<form-input icon='!userSurname' label='Přijmení:' class='validate' do-change-check type='text' value-id='surname' original-value='$surname' value='$surname' placeholder='$surname'></form-input>";
        //echo "<p class='allowSelect'>Email: <a class='allowSelect' href='./sendMail.php?uid=$id&isNILE=$isNILE'>$email</a></p>";
        echo "<form-input icon='!email' label='Email:' class='validate' do-change-check type='email' value-id='email' original-value='$email' value='$email' placeholder='$email'></form-input>";
        //echo "<p>Základní škola: <a id='schoolIdHolder' schoolId='$schoolId' href='?view=school&school=$schoolId'>$schoolName → $schoolAddress</a> <button class='formButton formWarnColor' id='attendantBtnChangeSchool'>Změnit školu</button></p>";
        echo "<form-input icon='!userRole' list='roles' is-strict-list='true' label='Role:' class='validate' type='select' do-change-check value-id='role' original-value='$role' value='$role' ></form-input>";
        echo "<form-input icon='!userRole' list='types' is-strict-list='true' label='Typ:' class='validate' type='select' do-change-check value-id='type' original-value='$type' value='$type' ></form-input>";
        if ($exists == "true") {
            echo "<p>Naposledy přihlášen: $lastLoginFormat</p>";
        }
        echo "<div class='formButtonBoxHolder'>";
        echo "<div class='formButtonBox'>";
        echo "<button class='purkynkaButton btnSave' form-icon='!save' exists='$exists'></button>";
        echo "<button class='purkynkaButton btnCancel' form-icon='!dontSave'></button>";
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
<script type='module' src='./user.js'></script>

</html>
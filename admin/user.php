<?php
session_start();
require "../assets/config.php";
require "./adminFunctions.php";
require "../assets/sharedFunctions.php";

function echoCheckAdminDelete(mysqli $conn, $role)
{
    if ($role != "admin") {
        return;
    }
    $stmt = $conn->prepare("SELECT COUNT(id_users) FROM users_teamPropaganda WHERE role='admin';");
    if (!$stmt->execute() || !$stmt->store_result() || !$stmt->bind_result($count) || !$stmt->fetch() || !$stmt->close()) {
        http_response_code(400);
        echo "Nelze zjisit stav uživatelů.";
        die();
    }
    if ($count <= 1) {
        http_response_code(400);
        echo "Nelze odebrat posledního správce systému.";
        die();
    }
}

if (isset($_POST["action"])) {
    if ($_POST["action"] == "update") {
        //Check if values set
        if (!isset($_POST["name"]) || !isset($_POST["surname"]) || !isset($_POST["role"]) || !isset($_POST["email"]) || !isset($_POST["id"])) {
            http_response_code(400);
            echo "Neplatné použití funkce - chybí parametr";
            die();
        }

        //Security check
        $role = getUserRole($conn, $_POST["id"]);
        if ($role != $_POST["role"]) {
            echoCheckAdminDelete($conn, $role);
        }

        //Make SQL Update
        $stmt = $conn->prepare("UPDATE users_teamPropaganda SET email=?, name=?, surname=?, role=? WHERE id_users=?");
        if ($stmt->bind_param("ssssi", $_POST["email"], $_POST["name"], $_POST["surname"], $_POST["role"], $_POST["id"]) && $stmt->execute() && $stmt->close()) {
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
        if (!isset($_POST["name"]) || !isset($_POST["surname"]) || !isset($_POST["role"]) || !isset($_POST["email"])) {
            http_response_code(400);
            echo "Neplatné použití funkce - chybí parametr";
            die();
        }

        //Make SQL Insert
        $stmt = $conn->prepare("INSERT INTO users_teamPropaganda(email, name, surname, isNILE, role, lastLogin) VALUES (?,?,?,0,?,CURRENT_TIMESTAMP())");
        if ($stmt->bind_param("ssss", $_POST["email"], $_POST["name"], $_POST["surname"], $_POST["role"]) && $stmt->execute() && $stmt->close()) {
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

        //Security check
        $role = getUserRole($conn, $_POST["id"]);
        if ($role != $_POST["role"]) {
            echoCheckAdminDelete($conn, $role);
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
            die();
        }
    } else {
        http_response_code(400);
        echo "Neplatné použití funkce - neplatná akc";
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
    <title>Uživatel</title>
    <link rel="stylesheet" href="../formWebScripts/css/formStyle.css">
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body class="pageHolder">
    <header>
        <?php setupTitlebarAdmin($conn, "user.php") ?>
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
        $name = "";
        $surname = "";
        $email = "";
        $role = "";
        $isNILE = 0;
        $lastLogin = new DateTime()->format('Y-m-d H:i:s');
        $exists = "true";
        if (isset($_GET["newUser"])) {
            echo "<h1>Vytvořit nového uživatele</h1>";
            $exists = "false";
        } else {
            $stmt = $conn->prepare("SELECT name, surname, email,role, isNILE, lastLogin FROM users_teamPropaganda WHERE id_users = ?;");
            if (!$stmt->bind_param("i", $_GET["user"]) || !$stmt->execute() || !$stmt->store_result() || $stmt->num_rows != 1 || !$stmt->bind_result($name, $surname, $email, $role, $isNILE, $lastLogin) || !$stmt->fetch() || !$stmt->close()) {
                echo "<h1>Nelze získat informace o uživateli.</h1>";
                echo "<a href='./admin.php'><button class='purkynkaButton'>Zpět na hlavní stránku</button></a>";
                die();
            }
            $lastLoginFormat = new DateTime($lastLogin)->format(STANDARD_CZECH_DATETIME_FORMAT_FULL);
            echo "<h1>Informace o uživateli: $name $surname</h1>";
        }

        //Print HTML
        echo "<form-input icon='!userName' label='Křestní jméno:' class='validate' do-change-check='true' type='text' value-id='name' original-value='$name' value='$name' placeholder='$name'></form-input>";
        echo "<form-input icon='!userSurname' label='Přijmení:' class='validate' do-change-check='true' type='text' value-id='surname' original-value='$surname' value='$surname' placeholder='$surname'></form-input>";
        //echo "<p class='allowSelect'>Email: <a class='allowSelect' href='./sendMail.php?uid=$id&isNILE=$isNILE'>$email</a></p>";
        echo "<form-input icon='!email' label='Email:' class='validate' do-change-check='true' type='email' value-id='email' original-value='$email' value='$email' placeholder='$email'></form-input>";
        //echo "<p>Základní škola: <a id='schoolIdHolder' schoolId='$schoolId' href='?view=school&school=$schoolId'>$schoolName → $schoolAddress</a> <button class='formButton formWarnColor' id='attendantBtnChangeSchool'>Změnit školu</button></p>";
        echo "<form-input icon='!userRole' list='userRoles' is-strict-list='true' label='Role:' class='validate' type='select' do-change-check='true' value-id='role' original-value='$role' value='$role' is-case-sensitive-list='false'></form-input>";
        echo "<p>Naposledy přihlášen: $lastLoginFormat</p>";
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
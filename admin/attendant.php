<?php
session_start();
require "../assets/config.php";
if (isset($_POST["action"])) {
    if ($_POST["action"] == "update") {
        //Check if values set
        if (!isset($_POST["email"]) || !isset($_POST["name"]) || !isset($_POST["surname"]) || !isset($_POST["id"])) {
            http_response_code(400);
            echo "Invalid usage of function - missing table column parameters";
            die();
        }

        //Make SQL Update
        $stmt = $conn->prepare("UPDATE users SET email=?, name=?, surname=?, id_schools=? WHERE id_users=?");
        $stmt->bind_param("sssii", $_POST["email"], $_POST["name"], $_POST["surname"], $_POST["school_id"], $_POST["id"]);
        if ($stmt->execute()) {
            http_response_code(201);
            echo "Entry updated.";
            die();
        } else {
            http_response_code(400);
            echo "Entry could not be updated.";
            die();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="cs">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zájemce</title>
    <link rel="stylesheet" href="../formWebScripts/css/sharedStyle.css">
    <link rel="stylesheet" href="../formWebScripts/css/formStyle.css">
    <link rel="stylesheet" href="../formWebScripts/css/tableStyle.css">
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body>
    <header style="padding-left: 4px; padding-right: 4px; margin-top: 0px; padding-top: 1px; padding-bottom: 0px;" class="formInfoColor">
        <h1>Akce: <?php echo $_SESSION["adminSubEventId"] ?></h1>
        <div class="formButtonBoxHolder">
            <div class="formButtonBox formJustifyLeft">
                <a href="./admin.php"><button class="formButton formOkColor">Hlavní menu</button></a>
                <a href="./attendants.php"><button class="formButton formOkColor">Zájemci</button></a>
                <a href="./classrooms.php"><button class="formButton formOkColor">Učebny</button></a>
                <a href="./schools.php"><button class="formButton formOkColor">Školy</button></a>
                <a href="./messages.php"><button class="formButton formOkColor">Zprávy</button></a>
                <a href="./payments.php"><button class="formButton formOkColor">Platby</button></a>
            </div>
            <div class="formButtonBox formJustifyRight">
                <a href="./changeEvent.php"><button class="formButton formWarnColor">Změnit událost</button></a>
                <a href="./logout.php"><button class="formButton formErrorColor">Odhlásit se</button></a>
            </div>
        </div>
    </header>
    <main>
        <?php
        //Get attendant info
        $stmt = $conn->prepare("SELECT name,surname,email FROM users WHERE id_users=? LIMIT 1");
        $stmt->bind_param("i", $_GET["user"]);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($name, $surname, $email);
        $stmt->fetch();

        //Get attendant's school info
        $stmt = $conn->prepare("SELECT schools.id_schools, schools.name, schools.address FROM users JOIN schools ON users.id_schools = schools.id_schools WHERE users.id_users = ? LIMIT 1");
        $stmt->bind_param("i", $_GET["user"]);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($schoolId, $schoolName, $schoolAddress);
        $stmt->fetch();

        //Print HTML
        echo "<h1>Informace o zájemci: $name $surname</h1>";
        echo "<form-input label='Křestní jméno:' class='attendantValidate' do-change-check='true' type='text' id='attendantName' original-value='$name' value='$name' placeholder='$name'></form-input>";
        echo "<br>";
        echo "<form-input label='Přijmení:' class='attendantValidate' do-change-check='true' type='text' id='attendantSurname' original-value='$surname' value='$surname' placeholder='$surname'></form-input>";
echo "<br>";       
        echo "<form-input label='Email:' class='attendantValidate' do-change-check='true' type='email' id='attendantEmail' original-value='$email' value='$email' placeholder='$email'></form-input>";
        echo "<p>Zákonný zástupce: <b><?php echo '?' ?></b></p>";
        //echo "<p>Základní škola: <a id='schoolIdHolder' schoolId='$schoolId' href='?view=school&school=$schoolId'>$schoolName → $schoolAddress</a> <button class='formButton formWarnColor' id='attendantBtnChangeSchool'>Změnit školu</button></p>";
        echo "<br>";
        echo "<form-input label='Základní škola:' class='attendantValidate' type='select' do-change-check='true' id='attendantSchool' original-value='$schoolName → $schoolAddress' value='$schoolName → $schoolAddress' is-case-sensitive-list='false' style='width: 100%'></form-input>";
        echo "<div class='formButtonBoxHolder'>";
        echo "<div class='formButtonBox'>";
        echo "<button id='attendantBtnSave' class='formButton formOkColor'>Uložit změny</button>";
        echo "<button id='attendantBtnCancel' class='formButton formErrorColor'>Zrušit změny</button>";
        echo "<a href='./attendants.php'><button class='formButton formInfoColor'>Zpět na seznam zájemců</button></a>";
        echo "<a href='./school.php?school=$schoolId'><button class='formButton formInfoColor'>Zobrazit informace o škole</button></a>";
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
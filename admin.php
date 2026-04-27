<?php
session_start();
require "./assets/config.php";
function GetUserName(int $id): string
{
    //Request user
    global $conn;
    $stmt = $conn->prepare("SELECT name,surname FROM users WHERE id_users=? LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($name, $surname);
    $stmt->fetch();
    return $name . " " . $surname;
}
?>

<!DOCTYPE html>
<html lang="cs">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin panel</title>
    <link rel="stylesheet" href="./formWebScripts/css/sharedStyle.css">
    <link rel="stylesheet" href="./formWebScripts/css/formStyle.css">
    <link rel="stylesheet" href="./formWebScripts/css/tableStyle.css">
    <link rel="stylesheet" href="./assets/style.css">
</head>

<body>
    <header style="padding-left: 4px; padding-right: 4px; margin-top: 0px; padding-top: 1px; padding-bottom: 0px;"
        class="formInfoColor">
        <h1>Akce: <?php
        echo $_SESSION["adminSubEventId"]
            ?></h1>
        <div class="formButtonBoxHolder">
            <div class="formButtonBox formJustifyLeft">
                <a href="?"><button class="formButton formOkColor">Hlavní menu</button></a>
                <a href="?view=attendants"><button class="formButton formOkColor">Zájemci</button></a>
                <a href="?view=classrooms"><button class="formButton formOkColor">Učebny</button></a>
                <a href="?view=schools"><button class="formButton formOkColor">Školy</button></a>
                <a href="?view=messages"><button class="formButton formOkColor">Zprávy</button></a>
                <a href="?view=payments"><button class="formButton formOkColor">Platby</button></a>
            </div>
            <div class="formButtonBox formJustifyRight">
                <a href="?view=changeEvent"><button class="formButton formWarnColor">Změnit událost</button></a>
                <a href="./logout.php"><button class="formButton formErrorColor">Odhlásit se</button></a>
            </div>
        </div>
    </header>
    <main>
        <?php
        if ($_GET["view"] == "attendants") {
            echo "<h1>Zájemci</h1>";
            echo "<table class='styledTable styledTableAuto'>
                <tr>
                    <th>Akce</th>
                    <th>Jméno a přijmení</th>
                    <th>Email</th>
                    <th>Zákonný zástupce</th>
                    <th>Zaplaceno</th>
                    <th>Učebna</th>
                    <th>Základní škola</th>
                </tr>";

            //Request users
            $stmt = $conn->prepare("SELECT id_users, name,surname, email  password FROM users");
            $stmt->execute();
            $stmt->store_result();

            //List all users in table
            for ($i = 0; $i < $stmt->num_rows; $i++) {
                $stmt->bind_result($id, $name, $surname, $email);
                $stmt->fetch();

                //Get school info
                $schoolGet = $conn->prepare("SELECT schools.id_schools, schools.name, schools.address FROM users JOIN schools ON users.id_schools = schools.id_schools WHERE users.id_users = ? LIMIT 1");
                $schoolGet->bind_param("i", $id);
                $schoolGet->execute();
                $schoolGet->store_result();
                $schoolGet->bind_result($schoolId, $schoolName, $schoolAddress);
                $schoolGet->fetch();

                $highlightSchoolClass = "";
                if (isset($_GET["school"]) && $_GET["school"] = $schoolId) {
                    $highlightSchoolClass = "trHighlight";
                }

                //Put in table
                echo "<tr class='clickHighlightRow $highlightSchoolClass'>
                        <td>
                            <a href='?view=attendant&user=$id'><button class='formButton formWarnColor'>Upravit</button></a>
                            <button class='formButton formErrorColor deleteUserButton' userId=$id userName='$name $surname'>Odstranit</button>
                        </td>
                        <td>$name $surname</td>
                        <td>$email</td>
                        <td class='parentOfUserCell'>?</td>
                        <td>NE</td>
                        <td>?</td>
                        <td>$schoolName → $schoolAddress</td>
                    </tr>";
            }
            echo "</table>
            <script type='module' src='./adminAttendants.js'></script>";
        } else if ($_GET["view"] == "classrooms") {
            echo "<h1>Všechny dostupné učebny v databázi</h1>";
            echo "<table class='styledTable styledTableAuto'>
                <tr>
                    <th>Akce</th>
                    <th>Číslo učebny</th>
                    <th>Název učebny</th>
                    <th>Počet míst k sezení</th>
                    <th>Je aktivní</th>
                    <th>Poznámka</th>
                </tr>";

            //Request classrooms
            $stmt = $conn->prepare("SELECT id_classrooms, name,placesToSit, isFunctional,  note FROM classrooms");
            $stmt->execute();
            $stmt->store_result();

            //List all classrooms in table
            for ($i = 0; $i < $stmt->num_rows; $i++) {
                $stmt->bind_result($id, $name, $placesToSit, $isFunctional, $note);
                $stmt->fetch();
                echo "<tr class='clickHighlightRow'>
                        <td>
                            <a href='?view=classroom&classroom=$id'><button class='formButton formWarnColor'>Upravit</button></a>
                            <button class='formButton formErrorColor deleteClassroomButton' classroomId=$id classroomName='$name'>Odstranit</button>
                        </td>
                        <td>$id</td>
                        <td>$name</td>
                        <td>$placesToSit</td>
                        <td>$isFunctional</td>
                        <td>$note</td>
                    </tr>";
            }
            echo "</table>";

            //Add buttons
            echo "<a href='?view=classroom&newClassroom=1'><button class='formButton formWarnColor'>Vytvořit učebnu</button></a>";
        } else if ($_GET["view"] == "classroom") {
            //Get info of classroom    
            $id=$_GET["classroom"];
            $name = "";
            $placesToSit = "";
            $isFunctional = 1;
            $isFunctionalString = $isFunctional == 1 ? "true" : "false";
            $note = "";
            $exists = "true";
            if (isset($_GET["newClassroom"])) {
                echo "<h1>Vytvořit novou učebnu</h1>";
                $exists = "false";
            } else {
                $stmt = $conn->prepare("SELECT name, placesToSit, isFunctional, note FROM `classrooms` WHERE id_classrooms = ?;");
                $stmt->bind_param("s", $id);
                $stmt->execute();
                $stmt->store_result();
                $stmt->bind_result($name, $placesToSit, $isFunctional, $note);
                echo "<h1>Informace o učebně: </h1>";
            }

            //Create HTML
            echo "<p>Číslo učebny:</p><form-input class='classroomValidate' do-change-check='$exists' type='text' id='classroomNumber' original-value='$id' value='$id' placeholder='$id'></form-input>";
            echo "<p>Název učebny:</p><form-input class='classroomValidate' do-change-check='$exists' type='text' id='classroomName' original-value='$name' value='$name' placeholder='$name'></form-input>";
            echo "<p>Počet míst k sezení:</p><form-input class='classroomValidate' do-change-check='$exists' type='number' id='classroomPlacesToSit' original-value='$placesToSit' value='$placesToSit' placeholder='$placesToSit'></form-input>";
            echo "<br><form-toggle labelBefore='Je učebna aktivní: ' offColorClass='formErrorColor' onColorClass='formOkColor' value='$isFunctionalString'></form-toggle><br>";
            echo "<p>Poznámka:</p><form-input class='classroomValidate' do-change-check='$exists' type='textarea' id='classroomNote' original-value='$note' value='$note' placeholder='$note'></form-input>";
            echo "<div class='formButtonBoxHolder'>";
            echo "<div class='formButtonBox'>";
            echo "<button id='classroomBtnSave' exists='$exists' class='formButton formOkColor'>Uložit změny</button>";
            echo "<button id='classroomBtnCancel' exists='$exists' class='formButton formErrorColor'>Zrušit změny</button>";
            echo "<a href='?view=classrooms'><button class='formButton formInfoColor'>Zpět na seznam učeben</button></a>";
            echo "</div>";
            echo "</div>";
            echo "<script type='module' src='./adminClassroom.js'></script>";
        } else if ($_GET["view"] == "messages") {
            ?>
                        <h1>Zprávy</h1>
            <?php
        } else if ($_GET["view"] == "directMessages") {
            ?>
                            <h1>Přímé zprávy s [NAME]</h1>
                            <h2>Poslat zprávu</h2>
                            <h2>Historie zpráv</h2>
            <?php
        } else if ($_GET["view"] == "attendant") {
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
            echo "<p>Křestní jméno:</p><form-input class='attendantValidate' do-change-check='true' type='text' id='attendantName' original-value='$name' value='$name' placeholder='$name'></form-input>";
            echo "<p>Přijmení:</p><form-input class='attendantValidate' do-change-check='true' type='text' id='attendantSurname' original-value='$surname' value='$surname' placeholder='$surname'></form-input>";
            echo "<p>Email:</p><form-input class='attendantValidate' do-change-check='true' type='email' id='attendantEmail' original-value='$email' value='$email' placeholder='$email'></form-input>";
            echo "<p>Zákonný zástupce: <b><?php echo '?' ?></b></p>";
            //echo "<p>Základní škola: <a id='schoolIdHolder' schoolId='$schoolId' href='?view=school&school=$schoolId'>$schoolName → $schoolAddress</a> <button class='formButton formWarnColor' id='attendantBtnChangeSchool'>Změnit školu</button></p>";
            echo "<p>Základní škola:</p>";
            echo "<form-input class='attendantValidate' type='select' do-change-check='true' id='attendantSchool' original-value='$schoolName → $schoolAddress' value='$schoolName → $schoolAddress' is-case-sensitive-list='false' style='width: 100%'></form-input>";
            echo "<div class='formButtonBoxHolder'>";
            echo "<div class='formButtonBox'>";
            echo "<button id='attendantBtnSave' class='formButton formOkColor'>Uložit změny</button>";
            echo "<button id='attendantBtnCancel' class='formButton formErrorColor'>Zrušit změny</button>";
            echo "<a href='?view=attendants'><button class='formButton formInfoColor'>Zpět na seznam zájemců</button></a>";
            echo "<a href='?view=school&school=$schoolId'><button class='formButton formInfoColor'>Zobrazit informace o škole</button></a>";
            echo "</div>";
            echo "</div>";
            echo "<script type='module' src='./adminAttendant.js'></script>";
        } else if ($_GET["view"] == "schools") {
            //Print HTML
            echo "<h1>Školy</h1>";
        } else if ($_GET["view"] == "school") {
            //Get school info
            $stmt = $conn->prepare("SELECT schools.name, schools.address FROM schools WHERE schools.id_schools = ? LIMIT 1");
            $stmt->bind_param("i", $_GET["school"]);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($name, $address);
            $stmt->fetch();

            //Print HTML
            echo "<h1>Informace o škole: $name → $address</h1>";
            echo "<p>Název:</p>";
            echo "<form-input style='width: 100%' class='schoolValidate'  do-change-check='true' type='text' id='schoolName' value='$name' original-value='$name' placeholder='$name'></form-input>";
            echo "<p>Adresa:</p>";
            echo "<form-input style='width: 100%' class='schoolValidate'  do-change-check='true' type='text' id='schoolAddress' value='$address' original-value='$address' placeholder='$address'></form-input>";
            echo "<div class='formButtonBoxHolder'>";
            echo "<div class='formButtonBox'>";
            echo "<button id='schoolBtnSave' class='formButton formOkColor'>Uložit změny</button>";
            echo "<button id='schoolBtnCancel' class='formButton formErrorColor'>Zrušit změny</button>";
            echo "<a href='?view=schools'><button class='formButton formInfoColor'>Zpět na seznam škol</button></a>";
            echo "</div>";
            echo "</div>";
            echo "<script type='module' src='./adminSchool.js'></script>";
        } else if ($_GET["view"] == "school") {

        } else {
            ?>
                                            <h1>Hlavní menu</h1>
            <?php
        }
        ?>
    </main>
    <footer>

    </footer>
</body>
<script type="module" src="./formWebScripts/js/formScript.js"></script>

</html>
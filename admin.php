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

<body>
    <header style="padding-left: 4px; padding-right: 4px; margin-top: 0px; padding-top: 1px; padding-bottom: 0px;" class="formInfoColor">
        <h1>test</h1>
        <div class="formButtonBoxHolder">
            <div class="formButtonBox formJustifyLeft">
                <a href="?"><button class="formButton formOkColor">Hlavní menu</button></a>
                <a href="?view=attendants"><button class="formButton formOkColor">Zájemci</button></a>
                <a href="?view=classrooms"><button class="formButton formOkColor">Učebny</button></a>
                <a href="?view=schools"><button class="formButton formOkColor">Školy</button></a>
                <a href="?view=messages"><button class="formButton formOkColor">Zprávy</button></a>
            </div>
            <div class="formButtonBox formJustifyRight">
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

                $highlightSchoolClass  = "";
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
            ?>
                <h1>Učebny</h1>
            <?php
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
            echo "<p>Křestní jméno: <form-input class='attendantValidate' type='text' id='attendantName' value='$name' placeholder='$name'></form-input></p>";
            echo "<p>Přijmení: <form-input class='attendantValidate' type='text' id='attendantSurname' value='$surname' placeholder='$surname'></form-input></p>";
            echo "<p>Email: <form-input class='attendantValidate' type='email' id='attendantEmail' value='$email' placeholder='$email'></form-input></p>";
            echo "<p>Zákonný zástupce: <b><?php echo '?' ?></b></p>";
            echo "<p>Základní škola: <a href='?view=school&school=$schoolId'>$schoolName → $schoolAddress</a> <button class='formButton formWarnColor'>Změnit školu</button></p>";
            echo "<div class='formButtonBoxHolder'>";
            echo "<div class='formButtonBox'>";
            echo "<button id='attendantBtnSave' class='formButton formOkColor'Uložit změny</button>";
            echo "<button id='attendantBtnCancel' class='formButton formErrorColor'>Zrušit změny</button>";
            echo "<a href='?view=attendants'><button class='formButton formInfoColor'>Zpět na seznam zájemců</button></a>";
            echo "</div>";
            echo "</div>";
            echo "<script type='module' src='./adminAttendant.js'></script>";
        } else if ($_GET["view"] == "schools") {
            //Print HTML
            echo "<h1>Školy</h1>";
            echo "<table class='styledTable styledTableAuto'>";
            echo "<tr>";
            echo "<th>Akce</th>";
            echo "<th>Název</th>";
            echo "<th>Počet zájemců</th>";
            echo "<th>Adresa</th>";
            echo "</tr>";

            //Request schools with users
            $stmt = $conn->prepare("SELECT schools.id_schools, COUNT(schools.id_schools), schools.name, schools.address FROM users JOIN schools ON users.id_schools = schools.id_schools GROUP BY schools.id_schools;");
            $stmt->execute();
            $stmt->store_result();

             //List all users in table
            for ($i = 0; $i < $stmt->num_rows; $i++) {
                $stmt->bind_result($id, $count, $name, $address);
                $stmt->fetch();

                //Put in table
                echo "<tr class='clickHighlightRow'>
                        <td>
                            <a href='?view=school&school=$id'><button class='formButton formWarnColor'>Upravit</button></a>
                            <a href='?view=attendants&school=$id'><button class='formButton formInfoColor'>Zvýraznit zájemce</button></a>
                        </td>
                        <td>$name</td>
                        <td>$count</td>
                        <td>$address</td>
                    </tr>";
            }
            echo "</table>
            <script type='module' src='./adminAttendants.js'></script>";
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
            echo "<p>Název: <form-input class='schoolValidate' type='text' valueId='schoolName' initialValue='$name' placeholder='$name'></form-input></p>";
            echo "<p>Adresa: <form-input class='schoolValidate' type='text' valueId='schoolAddress' initialValue='$address' placeholder='$address'></form-input></p>";
            echo "<div class='formButtonBoxHolder'>";
            echo "<div class='formButtonBox'>";
            echo "<button id='schoolBtnSave' class='formButton formOkColor'>Uložit změny</button>";
            echo "<button id='schoolBtnCancel' class='formButton formErrorColor'>Zrušit změny</button>";
            echo "<a href='?view=schools'><button class='formButton formInfoColor'>Zpět na seznam škol</button></a>";
            echo "</div>";
            echo "</div>";
            echo "<script type='module' src='./adminSchool.js'></script>";
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
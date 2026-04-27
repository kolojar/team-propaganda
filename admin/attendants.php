<?php
session_start();
require "../assets/config.php";
?>

<!DOCTYPE html>
<html lang="cs">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zájemci</title>
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
        <h1>Zájemci</h1>
        <table class='styledTable styledTableAuto'>
            <tr>
                <th>Akce</th>
                <th>Jméno a přijmení</th>
                <th>Email</th>
                <th>Zákonný zástupce</th>
                <th>Zaplaceno</th>
                <th>Učebna</th>
                <th>Základní škola</th>
            </tr>
            <?php
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
                            <a href='./attendant.php?user=$id'><button class='formButton formWarnColor'>Upravit</button></a>
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
            ?>
        </table>
    </main>
    <footer>

    </footer>
</body>
<script type="module" src="../formWebScripts/js/formScript.js"></script>
<script type='module' src='./attendants.js'></script>

</html>
<?php
session_start();
require "../assets/config.php";
require "./adminFunctions.php";
?>

<!DOCTYPE html>
<html lang="cs">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Všichni zájemci</title>
    <link rel="stylesheet" href="../formWebScripts/css/sharedStyle.css">
    <link rel="stylesheet" href="../formWebScripts/css/formStyle.css">
    <link rel="stylesheet" href="../formWebScripts/css/tableStyle.css">
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body class="pageHolder">
    <header>
        <?php setupTitlebar($conn,"attendants.php") ?>
    </header>
    <main>
        <h1>Seznam všech zájemců</h1>
        <table class='styledTable styledTableAuto'>
            <tr>
                <th>Akce</th>
                <th>Jméno a přijmení</th>
                <th>Zákonný zástupce</th>
                <th>Email zákonného zástupce</th>
                <th>Zaplaceno</th>
                <th>Učebna</th>
                <th>Základní škola</th>
            </tr>
            <?php
            ////Get highlighted schools
            //$highlightSchools = [];
            //if(isset($_GET['schools'])) {
            //    $highlightSchools = explode(',',$_GET["schools"]);
            //}

            //Request users
            $stmt = $conn->prepare(
                "SELECT id_attendants, name,surname, id_parent, id_schools FROM attendants_teamPropaganda",
            );
            $stmt->execute();
            $stmt->store_result();

            //List all users in table
            for ($i = 0; $i < $stmt->num_rows; $i++) {
                $stmt->bind_result($id, $name, $surname, $parentId, $schoolId);
                $stmt->fetch();

                //Get school info
                $schoolGet = $conn->prepare("SELECT schools.name, schools.address FROM schools_teamPropaganda WHERE schools.id_schools = ? LIMIT 1");
                $schoolGet->bind_param("i", $schoolId);
                $schoolGet->execute();
                $schoolGet->store_result();
                $schoolGet->bind_result($schoolName, $schoolAddress);
                $schoolGet->fetch();

                //Get parent info
                $parentGet = $conn->prepare("SELECT name, surname, email FROM users_teamPropaganda WHERE id_users = ? LIMIT 1");
                $parentGet->bind_param("i", $parentId);
                $parentGet->execute();
                $parentGet->store_result();
                $parentGet->bind_result($parentName, $parentSurname, $parentEmail);
                $parentGet->fetch();

                $highlightSchoolClass = "";
                if (isset($_GET["school"]) && $_GET["school"] == $schoolId) {
                    $highlightSchoolClass = "trHighlight";
                }

                //Put in table
                echo "<tr class='clickHighlightRow $highlightSchoolClass'>
                        <td class='formButtonBoxTable'>
                            <a href='./attendant.php?attendant=$id'><button class='formButton formWarnColor'>Upravit</button></a>
                            <button class='formButton formErrorColor deleteUserButton' userId=$id userName='$name $surname'>Odstranit</button>
                        </td>
                        <td>$name $surname</td>
                        <td>$parentName $parentSurname</td>
                        <td><a href='mailto:$parentEmail'>$parentEmail</td>
                        <td>NE</td>
                        <td>?</td>
                        <td>$schoolName → $schoolAddress</td>
                    </tr>";
            }
            ?>
            <h1></h1>
        </table>
    </main>
    <footer>

    </footer>
</body>
<script type="module" src="../formWebScripts/js/formScript.js"></script>
<script type='module' src='./sharedScripts.js'></script>

</html>

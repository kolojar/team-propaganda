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
    <title>Seznam škol</title>
    <link rel="stylesheet" href="../formWebScripts/css/sharedStyle.css">
    <link rel="stylesheet" href="../formWebScripts/css/formStyle.css">
    <link rel="stylesheet" href="../formWebScripts/css/tableStyle.css">
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body class="pageHolder">
    <header>
        <?php setupTitlebar($conn,"schools.php") ?>
    </header>
    <main>
        <h1>Školy, které mají nahlášené zájemce</h1>
        <table class='styledTable styledTableAuto'>
            <tr>
                <th>Akce</th>
                <th>Počet zájemců</th>
                <th>Název</th>
                <th>Adresa</th>
            </tr>
            <?php
            //Request schools with student
            $stmt = $conn->prepare("SELECT s.id_schools, s.name, s.address, COUNT(a.id_attendants), GROUP_CONCAT(a.id_attendants) FROM attendants a JOIN registered_attendants ra ON a.id_attendants = ra.id_attendants JOIN schools s ON s.id_schools = a.id_attendants WHERE ra.id_events = ?;");
            $stmt->bind_param("i",$_COOKIE["adminEventId"]);
            $stmt->execute();
            $stmt->store_result();

            //List all schools with students in table
            for ($i = 0; $i < $stmt->num_rows; $i++) {
                $stmt->bind_result($id, $name, $address, $count, $users);
                $stmt->fetch();
                echo "<tr class='clickHighlightRow'>
                        <td>
                            <a href='./school.php?school=$id'><button class='formButton formWarnColor'>Upravit</button></a>
                            <a href='./attendants.php?school=$id'><button class='formButton formInfoColor'>Zvýraznit zájemce</button></a>
                        </td>
                        <td>$count</td>
                        <td>$name</td>
                        <td>$address</td>
                    </tr>";
            }
            ?>
        </table>
        <h1>Seznam všech škol</h1>
        <a href="./school.php?newSchool=1"><button class="formButton formWarnColor">Přidat novou školu</button></a>
        <a href="./schoolsAll.php"><button class="formButton formOkColor">Zobrazit všechny školy</button></a>
    </main>
    <footer>

    </footer>
</body>
<script type="module" src="../formWebScripts/js/formScript.js"></script>

</html>
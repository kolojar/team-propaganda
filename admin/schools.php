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
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body class="pageHolder">
    <header>
        <?php setupTitlebarAdmin($conn, "schools.php") ?>
    </header>
    <main>
        <?php
        $isFirst = true;
        //Request schools with student
        $stmt = $conn->prepare("SELECT s.id_schools, s.name, s.address, COUNT(a.id_attendants), GROUP_CONCAT(a.id_attendants) FROM attendants_teamPropaganda a JOIN registered_attendants_teamPropaganda ra ON a.id_attendants = ra.id_attendants JOIN schools_teamPropaganda s ON s.id_schools = a.id_schools WHERE ra.id_events = ?;");
        $stmt->bind_param("i", $_COOKIE["adminEventId"]);
        $stmt->execute();
        $stmt->store_result();

        //List all schools with students in table
        for ($i = 0; $i < $stmt->num_rows; $i++) {
            $stmt->bind_result($id, $name, $address, $count, $users);
            $stmt->fetch();
            //HTML Header
            if ($isFirst) {
                if ($count == 0) {
                    echo "<h1>Žádní zájemci nejsou k dispozici</h1>";
                    continue;
                }
                echo "<h1>Školy, které mají nahlášené zájemce</h1>
                      <table>
                          <tr>
                              <th>Akce</th>
                              <th>Počet zájemců</th>
                              <th>Název</th>
                              <th>Adresa</th>
                          </tr>";
                $isFirst = false;
            }

            //HTML data
            echo "<tr class='clickHighlightRow'>
                        <td class='formButtonBoxTable'>
                            <a href='./school.php?school=$id'><button class='formButton formButtonInline purkynkaButton'>Upravit</button></a>
                            <a href='./attendants.php?school=$id'><button class='formButton formButtonInline purkynkaButton'>Zvýraznit zájemce</button></a>
                        </td>
                        <td>$count</td>
                        <td>$name</td>
                        <td>$address</td>
                    </tr>";
        }
        ?>
        </table>
        <h1>Seznam všech škol</h1>
        <a href="./school.php?newSchool=1"><button class="formButton purkynkaButton">Přidat novou školu</button></a>
        <a href="./schoolsAll.php"><button class="formButton purkynkaButton">Zobrazit všechny školy</button></a>
    </main>
    <footer>

    </footer>
</body>
<script type="module" src="../formWebScripts/js/formScript.js"></script>

</html>
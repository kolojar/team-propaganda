<?php
session_start();
require "../assets/config.php";
require "./adminFunctions.php";
?>

<!DOCTYPE html>
<html lang="cs">

<head>
    <meta charset="UTF-8">
    <meta name="form-icons-main-db" content="../formWebScripts/formIcons.json">
    <meta name="form-icons-db" content="../assets/formIcons.json">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seznam škol</title>

    <link rel="stylesheet" href="../formWebScripts/css/formStyle.css">

    <link rel="stylesheet" href="../assets/style.css">
</head>

<body class="pageHolder">
    <header>
        <?php setupTitlebarAdmin($conn, "schoolsAll.php") ?>
    </header>
    <main>
        <?php
        //Request schools with student
        $stmt = $conn->prepare("SELECT s.id_schools, s.name, s.address, COUNT(a.id_attendants), GROUP_CONCAT(a.id_attendants) FROM attendants_teamPropaganda a RIGHT JOIN schools_teamPropaganda s ON s.id_schools = a.id_attendants GROUP BY s.id_schools;");
        if (!$stmt->execute() || !$stmt->store_result()) {
            echo "<h1>Nelze získat informace o školách.</h1>";
            echo "<a href='./admin.php'><button class='purkynkaButton'>Zpět na hlavní stránku</button></a>";
            die();
        }
        echo "<h1>Seznam všech škol</h1>";
        echo "<a href='./school.php?newSchool=1'><button class='purkynkaButton' form-icon='!add'><span>Přidat novou školu</span></button></a><br>";
        echo "<i>Poznámka: Nekteré školy není možné smazat, jelikož mají nahlášené zájemce.</i>";
        echo "<table>";
        echo "<tr>";
        echo "<th>Akce</th>";
        echo "<th>Počet zájemců</th>";
        echo "<th>Název</th>";
        echo "<th>Adresa</th>";
        echo "</tr>";

        //List all schools with students in table
        for ($i = 0; $i < $stmt->num_rows; $i++) {
            if (!$stmt->bind_result($id, $name, $address, $count, $users) || !$stmt->fetch()) {
                $id = null;
                $name = "CHYBA";
                $address = "CHYBA";
                $count = "CHYBA";
                $users = "";
            }
            echo "<tr class='clickHighlightRow'>
                        <td class='formButtonBoxTable'>
                            <a href='./school.php?school=$id'><button class='formButton formButtonInline purkynkaButton' form-icon='!edit'></button></a> ";
            if ($count > 0) {
                echo "<a href='./attendants.php?school=$id'><button class='formButton formButtonInline purkynkaButton' form-icon='!highlightUsers'></button></a>";
            } else {
                echo "<button class='formButton formButtonInline purkynkaButton btnTableDelete' school=$id form-icon='!delete'></button>";
            }
            echo "</td>
                        <td>$count</td>
                        <td>$name</td>
                        <td>$address</td>
                    </tr>";
        }
        echo "</table>";
        $stmt->close()
            ?>
    </main>
    <footer>

    </footer>
</body>
<script type="module" src="../formWebScripts/js/formScript.js"></script>
<script type="module" src="./schoolsAll.js"></script>

</html>
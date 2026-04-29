<?php
session_start();
require "../assets/config.php";
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
        <h1>Seznam všech škol</h1>
        <button id="btnPageNext" class="formButton formInfoColor"><<</button>
        <form-input label="Číslo stránky: " type="number" id="pageNumber"></form-input>
        <button id="btnPageNext" class="formButton formInfoColor">>></button>
        <form-input label="Počet položek na stránku: " type="number" id="itemsPerPage"></form-input>
        <i>Poznámka: Nekteré školy není možné smazat, jelikož mají nahlášené zájemce.</i>
        <table class='styledTable styledTableAuto'>
            <tr>
                <th>Akce</th>
                <th>Počet zájemců</th>
                <th>Název</th>
                <th>Adresa</th>
            </tr>
            <?php
            //Request schools with student
            $stmt = $conn->prepare("SELECT schools.id_schools, schools.name, schools.address,COUNT(users.id_users), GROUP_CONCAT(users.id_users) FROM users RIGHT JOIN schools ON users.id_schools = schools.id_schools GROUP BY schools.id_schools LIMIT ?,?;");
            $stmt->bind_param("ii",isset($_GET["page"]) ? )
            $stmt->execute();
            $stmt->store_result();

            //List all schools with students in table
            for ($i = 0; $i < $stmt->num_rows; $i++) {
                $stmt->bind_result($id, $name, $address, $count, $users);
                $stmt->fetch();
                echo "<tr class='clickHighlightRow'>
                        <td>
                            <a href='./school.php?school=$id'><button class='formButton formWarnColor'>Upravit</button></a>";
                if($count > 0) {
                    echo "<a href='./attendants.php?school=$id'><button class='formButton formInfoColor'>Zvýraznit zájemce</button></a>";
                } else {
                    echo "<button class='formButton formErrorColor btnTableDelete' school=$id>Odstranit</button>";
                }
                echo "</td>
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
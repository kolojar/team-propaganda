<?php
session_start();
require "../assets/config.php";
?>

<!DOCTYPE html>
<html lang="cs">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin panel</title>
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
        <h1>Všechny dostupné učebny v databázi</h1>
        <table class='styledTable styledTableAuto'>
            <tr>
                <th>Akce</th>
                <th>Název učebny</th>
                <th>Počet míst k sezení</th>
                <th>Je aktivní</th>
                <th>Poznámka</th>
            </tr>
            <?php
            //Request classrooms
            $stmt = $conn->prepare("SELECT id_classrooms, name,placesToSit, isFunctional,  note FROM classrooms");
            $stmt->execute();
            $stmt->store_result();

            //List all classrooms in table
            for ($i = 0; $i < $stmt->num_rows; $i++) {
                $stmt->bind_result($id, $name, $placesToSit, $isFunctional, $note);
                $stmt->fetch();
                $isFunctionalString = $isFunctional == 1 ? "Ano" : "Ne";
                echo "<tr class='clickHighlightRow'>
                        <td>
                            <a href='./classroom.php?classroom=$id'><button class='formButton formWarnColor'>Upravit</button></a>
                            <button class='formButton formErrorColor btnTableDelete' classroom=$id classroomName='$name'>Odstranit</button>
                        </td>
                        <td>$name</td>
                        <td>$placesToSit</td>
                        <td>$isFunctionalString</td>
                        <td>$note</td>
                    </tr>";
            }
            ?>
        </table>
        <a href='./classroom.php?newClassroom=1'><button class='formButton formWarnColor'>Vytvořit učebnu</button></a>
    </main>
    <footer>

    </footer>
</body>
<script type="module" src="../formWebScripts/js/formScript.js"></script>
<script type="module" src="./classrooms.js"></script>
</html>
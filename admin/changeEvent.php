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

<body class="pageHolder">
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
        //Get event ID
        $eventId = "";
        if (isset($_COOKIE["adminEventId"])) {
            $eventId = $_COOKIE["adminEventId"];
        } else if (isset($_COOKIE["adminSubEventId"])) {
            $stmtGetId = $conn->prepare("SELECT id_events FROM subevents WHERE id_subevents=? LIMIT 1;");
            $stmtGetId->bind_param("i", $_COOKIE["adminSubEventId"]);
            $stmtGetId->execute();
            $stmtGetId->store_result();
            $stmtGetId->bind_result($eventId);
            $stmtGetId->fetch();
        }

        //Get event name
        setcookie("adminEventId", $eventId);
        $eventName = "";
        $stmtGetName = $conn->prepare("SELECT name FROM events WHERE id_events=? LIMIT 1;");
        $stmtGetName->bind_param("i", $eventId);
        $stmtGetName->execute();
        $stmtGetName->store_result();
        $stmtGetName->bind_result($eventName);
        $stmtGetName->fetch();

        if ($eventName != "") {
            echo "<h1>Dostupné podudálosti pro událost: $eventName</h1>";
            echo "<table class='styledTable styledTableAuto'>";
            echo "<tr>";
            echo "<th>Akce</th>";
            echo "<th>Datum</th>";
            echo "<th>Čas zahájení</th>";
            echo "<th>Čas ukončení</th>";
            echo "<th>Počet přihlášených</th>";
            echo "</tr>";
            echo "</table>";
        }
        echo "<h1>Dostupné události</h1>";
        echo "<table class='styledTable styledTableAuto'>";
        echo "<tr>";
        echo "<th>Akce</th>";
        echo "<th>Název</th>";
        echo "<th>Druh</th>";
        echo "<th>Je aktivní</th>";
        echo "<th>Je registrace aktivní</th>";
        echo "</tr>";

        //Request classrooms
        $stmt = $conn->prepare("SELECT id_events, name, type, active_since, active_until, registration_open, registration_close FROM events;");
        $stmt->execute();
        $stmt->store_result();

        //List all classrooms in table
        for ($i = 0; $i < $stmt->num_rows; $i++) {
            $stmt->bind_result($id, $name,$type, $activeSince, $activeUntil, $registrationOpen, $registrationClose);
            $stmt->fetch();
            echo "<tr class='clickHighlightRow'>
                        <td>
                            <a href='./classroom.php?classroom=$id'><button class='formButton formWarnColor'>Upravit</button></a>
                            <button class='formButton formErrorColor btnTableDelete' classroom=$id classroomName='$name'>Odstranit</button>
                        </td>
                        <td>$name</td>
                        <td>$type</td>
                        <td>?</td>
                        <td>?</td>
                    </tr>";
        }
        ?>
        </table>
        <a href='./event.php?newEvent=1'><button class='formButton formWarnColor'>Vytvořit událost</button></a>
    </main>
    <footer>

    </footer>
</body>
<script type="module" src="../formWebScripts/js/formScript.js"></script>
<script type="module" src="./classrooms.js"></script>

</html>
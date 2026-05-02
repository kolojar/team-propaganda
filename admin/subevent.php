<?php
session_start();
require "../assets/config.php";
require "./adminFunctions.php";
if (isset($_POST["action"])) {
    if ($_POST["action"] == "update") {
        //Check if values set
        if (!isset($_POST["date"]) || !isset($_POST["start_time"]) || !isset($_POST["end_time"]) || !isset($_POST["id"])) {
            http_response_code(400);
            echo "Invalid usage of function - missing table column parameters";
            die();
        }

        //Make SQL Update
        $stmt = $conn->prepare("UPDATE subevents SET date=?,start_time=?,end_time=? WHERE id_subevents=?");
        $stmt->bind_param("sssi", $_POST["date"], $_POST["start_time"], $_POST["end_time"], $_POST["id"]);
        if ($stmt->execute()) {
            http_response_code(201);
            echo "Entry updated.";
            die();
        } else {
            http_response_code(400);
            echo "Entry could not be updated.";
            die();
        }
    } else if ($_POST["action"] == "insert") {
        //Check if values set
        if (!isset($_POST["date"]) || !isset($_POST["start_time"]) || !isset($_POST["end_time"]) || !isset($_POST["id_events"])) {
            http_response_code(400);
            echo "Invalid usage of function - missing table column parameters";
            die();
        }

        //Make SQL Insert
        $stmt = $conn->prepare("INSERT INTO subevents(id_events,date,start_time, end_time) VALUES (?,?,?,?)");
        $stmt->bind_param("isss", $_POST["id_events"], $_POST["date"], $_POST["start_time"], $_POST["end_time"]);
        if ($stmt->execute()) {
            http_response_code(201);
            echo "Entry created.";
            die();
        } else {
            http_response_code(400);
            echo "Entry could not be created.";
            die();
        }
    } else if ($_POST["action"] == "delete") {
        //Check if values set
        if (!isset($_POST["id"])) {
            http_response_code(400);
            echo "Invalid usage of function - missing table column parameters";
            die();
        }

        //Make SQL Delete
        $stmt = $conn->prepare("DELETE FROM subevents WHERE id_subevents=?");
        $stmt->bind_param("i", $_POST["id"]);
        if ($stmt->execute()) {
            http_response_code(201);
            echo "Entry deleted.";
            die();
        } else {
            http_response_code(400);
            echo "Entry could not be deleted.";
            die();
        }
    } else {
        http_response_code(400);
        echo "Invalid usage of function - invalid action";
        die();
    }
}
?>

<!DOCTYPE html>
<html lang="cs">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Podudálost</title>
    <link rel="stylesheet" href="../formWebScripts/css/sharedStyle.css">
    <link rel="stylesheet" href="../formWebScripts/css/formStyle.css">
    <link rel="stylesheet" href="../formWebScripts/css/tableStyle.css">
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body class="pageHolder">
    <header style="padding-left: 4px; padding-right: 4px; margin-top: 0px; padding-top: 1px; padding-bottom: 0px;" class="formInfoColor">
        <h1>Akce: <?php echo setupTitlebarAction($conn)?></h1>
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
                <a href="./events.php"><button class="formButton formWarnColor">Změnit událost</button></a>
                <a href="./logout.php"><button class="formButton formErrorColor">Odhlásit se</button></a>
            </div>
        </div>
    </header>
    <main>
        <?php
        $eventId = "";
        $dateDB = new DateTime("now", new DateTimeZone("Europe/Prague"))->format('Y-m-d');
        $startTimeDB = new DateTime("now", new DateTimeZone("Europe/Prague"))->format('H:i:s');
        $endTimeDB = new DateTime("now", new DateTimeZone("Europe/Prague"))->format('H:i:s');
        $exists = "true";
        if (isset($_GET["newSubevent"])) {
            echo "<h1>Vytvořit novou podudálost</h1>";
            $exists = "false";
            $eventId = $_GET["event"];
        } else {
            //Get subevent info
            $stmt = $conn->prepare("SELECT id_events, date, start_time, end_time FROM subevents WHERE id_subevents = ?;");
            $stmt->bind_param("i", $_GET["subevent"]);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($eventId, $dateDB, $startTimeDB, $endTimeDB);
            $stmt->fetch();

            //Get event info
            $stmt2 = $conn->prepare("SELECT name FROM events WHERE id_events = ?;");
            $stmt2->bind_param("i", $eventId);
            $stmt2->execute();
            $stmt2->store_result();
            $stmt2->bind_result($name);
            $stmt2->fetch();
            echo "<h1>Informace o události: $name → $dateDB </h1>";
        }
        $date = DateTime::createFromFormat('Y-m-d', $dateDB)->format("Y-m-d");
        $startTime = DateTime::createFromFormat('H:i:s', $startTimeDB)->format("H:i");
        $endTime = DateTime::createFromFormat('H:i:s', $endTimeDB)->format("H:i");
        echo "<form-input label='K události:' style='display: none' type='hidden' class='subeventValidate' original-value='$eventId' id='id_events' value='$eventId'></form-input>";
        //$isFunctionalString = $isFunctional == 1 ? "true" : "false";
        
        //Create HTML
        echo "<form-input label='Datum konání podudálosti:' class='subeventValidate' do-change-check='$exists' type='date' id='date' original-value='$date' value='$date'></form-input>";
        echo "<br>";
        echo "<form-input label='Zahájení události:' class='subeventValidate' do-change-check='$exists' type='time' id='start_time' original-value='$startTime' value='$startTime'></form-input>";
        echo "<br>";
        echo "<form-input label='Konec události:' class='subeventValidate' do-change-check='$exists' type='time' id='end_time' original-value='$endTime' value='$endTime'></form-input>";
        echo "<br>";
        echo "<div class='formButtonBoxHolder'>";
        echo "<div class='formButtonBox'>";
        echo "<button id='btnSave' exists='$exists' class='formButton formOkColor'>Uložit změny</button>";
        echo "<button id='btnCancel' exists='$exists' class='formButton formErrorColor'>Zrušit změny</button>";
        echo "<a href='./events.php'><button class='formButton formInfoColor'>Zpět na seznam události</button></a>";
        echo "</div>";
        echo "</div>";
        ?>
    </main>
    <footer>

    </footer>
</body>
<script type="module" src="../formWebScripts/js/formScript.js"></script>
<script type='module' src='./subevent.js'></script>

</html>
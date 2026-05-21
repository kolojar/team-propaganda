<?php
session_start();
require "../assets/config.php";
require "./adminFunctions.php";
if (isset($_POST["action"])) {
    if ($_POST["action"] == "update") {
        //Check if values set
        if (!isset($_POST["date"]) || !isset($_POST["start_time"]) || !isset($_POST["end_time"]) || !isset($_POST["id"])) {
            http_response_code(400);
            echo "Neplatné použití funkce - chybí parametr";
            die();
        }

        //Make SQL Update
        $stmt = $conn->prepare("UPDATE subevents_teamPropaganda SET date=?,start_time=?,end_time=? WHERE id_subevents=?");
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
            echo "Neplatné použití funkce - chybí parametr";
            die();
        }

        //Make SQL Insert
        $stmt = $conn->prepare("INSERT INTO subevents_teamPropaganda(id_events,date,start_time, end_time) VALUES (?,?,?,?)");
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
            echo "Neplatné použití funkce - chybí parametr";
            die();
        }

        //Make SQL Delete
        $stmt = $conn->prepare("DELETE FROM subevents_teamPropaganda WHERE id_subevents=?");
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
    } else if ($_POST["action"] == "addClassroom") {
        //Check if values set
        if (!isset($_POST["id"]) || !isset($_POST["classroom"])) {
            http_response_code(400);
            echo "Neplatné použití funkce - chybí parametr";
            die();
        }

        //Make SQL Insert
        $stmt = $conn->prepare("INSERT IGNORE INTO classrooms_subevents_teamPropaganda(id_classrooms, id_subevents) VALUES (?,?)");
        $stmt->bind_param("ii", $_POST["classroom"], $_POST["id"]);
        if ($stmt->execute()) {
            if ($stmt->affected_rows == 0) {
                http_response_code(400);
                echo "Učebna již přidána.";
                die();
            }
            http_response_code(201);
            echo "Entry created.";
            die();
        } else {
            http_response_code(400);
            echo "Entry could not be created.";
            die();
        }
    } else if ($_POST["action"] == "removeClassroom") {
        //Check if values set
        if (!isset($_POST["id"]) || !isset($_POST["classroom"])) {
            http_response_code(400);
            echo "Neplatné použití funkce - chybí parametr";
            die();
        }

        //Make SQL Delete
        $stmt = $conn->prepare("DELETE FROM classrooms_subevents_teamPropaganda WHERE id_subevents=? AND id_classrooms=?");
        $stmt->bind_param("ii", $_POST["id"],$_POST["classroom"]);
        if ($stmt->execute()) {
            http_response_code(201);
            echo "Entry deleted.";
            die();
        } else {
            http_response_code(400);
            echo "Entry could not be deleted.";
            die();
        }
    }else {
        http_response_code(400);
        echo "Neplatné použití funkce - neplatná akce";
        die();
    }
}
?>

<!DOCTYPE html>
<html lang="cs">

<head>
    <meta charset="UTF-8">
    <meta name="form-icons-main-db" content="../formWebScripts/formIcons.json">
    <meta name="form-icons-db" content="../assets/formIcons.json">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Podudálost</title>
    <link rel="stylesheet" href="../formWebScripts/css/formStyle.css">
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body class="pageHolder">
    <header>
        <?php setupTitlebarAdmin($conn, "subevent.php") ?>
    </header>
    <main>
        <?php
        $eventId = "";
        $dateDB = new DateTime("now", new DateTimeZone("Europe/Prague"))->format('Y-m-d');
        $startTimeDB = new DateTime("now", new DateTimeZone("Europe/Prague"))->format('H:i:s');
        $endTimeDB = new DateTime("now", new DateTimeZone("Europe/Prague"))->format('H:i:s');
        $exists = "true";
        $attendantsCount = 0;
        if (isset($_GET["newSubevent"])) {
            echo "<h1>Vytvořit novou podudálost</h1>";
            $exists = "false";
            $eventId = $_GET["event"];
        } else {
            //Get subevent info
            $stmt = $conn->prepare("SELECT s.id_events, s.date, s.start_time, s.end_time, COUNT(ra.id_attendants) FROM subevents_teamPropaganda s LEFT JOIN registered_attendants_teamPropaganda ra ON s.id_events = ra.id_events AND ra.paid IS NOT NULL WHERE s.id_subevents = ?;");
            $stmt->bind_param("i", $_GET["subevent"]);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($eventId, $dateDB, $startTimeDB, $endTimeDB, $attendantsCount);
            $stmt->fetch();

        }
        //Get event info
        $stmt2 = $conn->prepare("SELECT name,registration_close,active_until FROM events_teamPropaganda WHERE id_events = ?;");
        $stmt2->bind_param("i", $eventId);
        $stmt2->execute();
        $stmt2->store_result();
        $stmt2->bind_result($name, $registrationCloseDB, $activeUntilDB);
        $stmt2->fetch();
        if (!isset($_GET["newSubevent"])) {
            $dateFormated = new DateTime($dateDB)->format(STANDARD_CZECH_DATE_FORMAT_FULL);
            echo "<h1>Informace o události: $name → $dateFormated </h1>";
            echo "<i>Nedoporučuje se upravovat již proběhlé události, mohl by nastat chaos.</i><br>";
        }

        //Format dates
        $date = DateTime::createFromFormat('Y-m-d', $dateDB)->format("Y-m-d");
        $startTime = DateTime::createFromFormat('H:i:s', $startTimeDB)->format("H:i");
        $endTime = DateTime::createFromFormat('H:i:s', $endTimeDB)->format("H:i");
        $registrationClose = DateTime::createFromFormat('Y-m-d H:i:s', $registrationCloseDB)->format("Y-m-d");
        $registrationCloseTime = DateTime::createFromFormat('Y-m-d H:i:s', $registrationCloseDB)->format("H:i");
        $activeUntil = DateTime::createFromFormat('Y-m-d H:i:s', $activeUntilDB)->format("Y-m-d");
        $activeUntilTime = DateTime::createFromFormat('Y-m-d H:i:s', $activeUntilDB)->format("H:i");
        echo "<form-input label='K události:' style='display: none' type='hidden' class='subeventValidate' original-value='$eventId' id='id_events' value='$eventId'></form-input>";
        //$isFunctionalString = $isFunctional == 1 ? "true" : "false";
        
        //Create HTML
        echo "<fieldset>";
        echo "<legend>Nastavení podudálosi</legend>";
        echo "<form-input label='Datum konání podudálosti:' class='subeventValidate' do-change-check='$exists' type='date' value-id='date'  id='date' original-value='$date' value='$date' min='$registrationClose' max='$activeUntil' minTime='$registrationCloseTime' maxTime='$activeUntilTime'></form-input>";
        echo "<form-input label='Zahájení události:' class='subeventValidate' do-change-check='$exists' type='time' value-id='start_time' id='start_time' original-value='$startTime' value='$startTime'></form-input>";
        echo "<form-input label='Konec události:' class='subeventValidate' do-change-check='$exists' type='time' value-id='end_time' id='end_time' original-value='$endTime' value='$endTime'></form-input>";
        
        //Select classrooms
        $placesToSitTotal = 0;
        $placesToSitUsedTotal = 0;
        if ($exists == "true") {
            //Get info about active classrooms for event
            $stmt3 = $conn->prepare("SELECT cs.id_classrooms, c.name, c.places_to_sit, GROUP_CONCAT(ap.variable_symbol), COUNT(ap.variable_symbol) FROM classrooms_subevents_teamPropaganda cs JOIN classrooms_teamPropaganda c ON cs.id_classrooms = c.id_classrooms LEFT JOIN attendants_presence_teamPropaganda ap ON ap.id_subevents = cs.id_subevents AND ap.id_classrooms = cs.id_classrooms WHERE cs.id_subevents = ?;");
            $stmt3->bind_param("i", $_GET["subevent"]);
            $stmt3->execute();
            $stmt3->store_result();
            $echoHeader = true;

            //Echo HTML info
            if ($stmt3->num_rows > 0) {
                for ($i = 0; $i < $stmt3->num_rows; $i++) {
                    $stmt3->bind_result($idClassroom, $classroomName, $placesToSit, $variableSymbols, $placesToSitUsed);
                    $stmt3->fetch();
                    if ($echoHeader) {
                        if ($idClassroom == null) {
                            continue;
                        }
                        echo "<p>Aktivní učebny k této podudálosti:</p>";
                        echo "<ul>";
                        $echoHeader = false;
                    }
                    $placesToSitTotal += $placesToSit;
                    $placesToSitUsedTotal += $placesToSitUsed;
                    echo "<li>";
                    echo "<span>$classroomName → $placesToSit míst, obsazeno: $placesToSitUsed</span>";
                    echo "<button class='purkynkaButton deleteClassroom' form-icon='!delete' classroom='$idClassroom'></button>";
                    echo "</li>";
                }
                echo "</ul>";
                }
            if ($echoHeader) {
                echo "<p>Žádné aktivní učebny.</p>";
            }
        } else {
            echo "<p>Učebny je možné nastavit až po vytvoření.</p>";
        }

        //Calculate needed places
        if($placesToSitUsedTotal > $attendantsCount) {
            $attendantsCount = $placesToSitUsedTotal;
        }
        $freePlaces = $placesToSitTotal - $attendantsCount;
        if($freePlaces >= 0) {
            echo "<p id='freeSpacesCount' ok='1'>Počet volných míst v učebnách: " . $freePlaces . "</p>";
        } else {
            echo "<p id='freeSpacesCount' ok='0'>Na událost je nedostatečný počet míst v učebnách: " . abs($freePlaces) . "</p>";
        }

        //Echo HTML buttons
        echo "<div class='formButtonBoxHolder'>";
        echo "<div class='formButtonBox'>";
        echo "<button exists='$exists' class='formButton purkynkaButton btnSave'>Uložit změny</button>";
        echo "<button exists='$exists' class='formButton purkynkaButton btnCancel'>Zrušit změny</button>";
        if ($exists == "true") {
            echo "<button id='addClassroom' class='formButton purkynkaButton'>Přidat učebnu</button>";
            echo "<button id='copyClassroom' class='formButton purkynkaButton'>Kopírovat nastavení učeben z jiné podudálosti</button>";
        }
        echo "<a href='./events.php'><button class='formButton purkynkaButton'>Zpět na seznam události</button></a>";
        echo "</div>";
        echo "</div></fieldset>";
        ?>
    </main>
    <footer>

    </footer>
</body>
<script type="module" src="../formWebScripts/js/formScript.js"></script>
<script type='module' src='./subevent.js'></script>

</html>
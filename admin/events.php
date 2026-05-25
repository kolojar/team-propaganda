<?php
session_start();
require "../assets/config.php";
require "./adminFunctions.php";

if (isset($_POST["action"])) {
    if ($_POST["action"] == "getRelatedSubevents") {
        //Make SQL Select
        $stmt = $conn->prepare("SELECT s1.id_subevents, s1.date FROM subevents_teamPropaganda s1 JOIN subevents_teamPropaganda s2 ON s1.id_events = s2.id_events WHERE s2.id_subevents = ? ORDER BY s1.date;");
        if (!$stmt->bind_param("i", $_POST["id"]) || !$stmt->execute() || !$stmt->store_result()) {
            http_response_code(400);
            echo "Entry could not be fetched";
            die();
        }

        //Fetch all subevents
        $jsonRecords = [];
        for ($i = 0; $i < $stmt->num_rows; $i++) {
            if ($stmt->bind_result($id, $date) && $stmt->fetch()) {
                $jsonRecords[] = [
                    "id" => $id,
                    "date" => $date,
                ];
            }
        }

        //Generate JSON
        $stmt->close();
        http_response_code(201);
        echo json_encode($jsonRecords);
        die();
    } else {
        http_response_code(400);
        echo "Neplatné použití funkce - neplatná akce";
        die();
    }
}
if (isset($_GET["action"])) {
    if ($_GET["action"] == "clearEvent") {
        setEventId("");
        setSubeventId("");
        header("Location: ./events.php");
        die();
    } else if ($_GET["action"] == "clearSubevent") {
        setSubeventId("");
        header("Location: ./events.php");
        die();
    } else {
        http_response_code(400);
        echo "Neplatné použití funkce - neplatná akce";
        die();
    }
}
if (isset($_GET["selectEvent"])) {
    setEventId($_GET["selectEvent"]);
    setSubeventId("");
    header("Location: ./events.php");
    die();
}
if (isset($_GET["selectSubevent"])) {
    setSubeventId($_GET["selectSubevent"]);
    header("Location: ./events.php");
    die();
}
?>

<!DOCTYPE html>
<html lang="cs">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="form-icons-main-db" content="../formWebScripts/formIcons.json">
    <meta name="form-icons-db" content="../assets/formIcons.json">
    <title>Změnit událost</title>
    <link rel="stylesheet" href="../formWebScripts/css/formStyle.css">
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body class="pageHolder">
    <header>
        <?php $result = setupTitlebarAdmin($conn, "events.php") ?>
    </header>
    <main>
        <?php
        //Get event ID
        $eventId = "";
        $resultSubeventId = $result->subeventId;
        if (isset($_COOKIE["adminEventId"])) {
            $eventId = $_COOKIE["adminEventId"];
        } else if (isset($_COOKIE["adminSubEventId"])) {
            $stmt = $conn->prepare("SELECT id_events FROM subevents_teamPropaganda WHERE id_subevents=?;");
            if (!$stmt->bind_param("i", $resultSubeventId) || !$stmt->execute() || !$stmt->store_result() || $stmt->num_rows != 1 || !$stmt->bind_result($eventId) || !$stmt->fetch() || !$stmt->close()) {
                echo "<h1>Chyba při načítání událostí a podudálostí</h1>";
                die();
            }
        }

        //Get event name
        $eventName = "";
        $stmt = $conn->prepare("SELECT name FROM events_teamPropaganda WHERE id_events=? LIMIT 1;");
        if ($stmt->bind_param("i", $eventId) && $stmt->execute() && $stmt->store_result() && $stmt->bind_result($eventName) && $stmt->fetch()) {
            //Request subevents
            $stmt->close();
            $stmt = $conn->prepare("SELECT id_subevents, date, start_time, end_time FROM subevents_teamPropaganda WHERE id_events=?;");
            if (!$stmt->bind_param("i", $eventId) || !$stmt->execute() || !$stmt->store_result()) {
                echo "<h1>Nelze získat seznam podudálostí pro zájemce o studium.</h1>";
            } else if ($stmt->num_rows == 0) {
                echo "<h1>Nejsou k dispozici žádné podudálosti  pro zájemce o studium.</h1>";
            } else {
                //Generate HTML
                echo "<h1>Termíny události (podudálosti) pro zájemce o studium: $eventName</h1>";
                echo "<table>";
                echo "<tr>";
                echo "<th>Akce</th>";
                echo "<th>Datum</th>";
                echo "<th>Čas zahájení</th>";
                echo "<th>Čas ukončení</th>";
                echo "</tr>";

                //List all subevents in table
                for ($i = 0; $i < $stmt->num_rows; $i++) {
                    if (!$stmt->bind_result($id, $date, $startTime, $endTime) || !$stmt->fetch()) {
                        $id = null;
                        $date = "CHYBA";
                        $startTime = "CHYBA";
                        $endTime = "CHYBA";
                    } else {
                        $date = DateTime::createFromFormat('Y-m-d', $date)->format(STANDARD_CZECH_DATE_FORMAT_FULL);
                        $startTime = DateTime::createFromFormat('H:i:s', $startTime)->format(STANDARD_CZECH_TIME_FORMAT_FULL);
                        $endTime = DateTime::createFromFormat('H:i:s', $endTime)->format(STANDARD_CZECH_TIME_FORMAT_FULL);
                    }
                    echo "<tr class='clickHighlightRow'>";
                    echo "<td class='formButtonBoxTable'>";
                    echo "<a href='./events.php?selectSubevent=$id'><button form-icon='!openView' class='purkynkaButton'><span>Otevřít podpohled</span></button></a>";
                    if ($result->role == "admin") {
                        echo "<a href='./subevent.php?subevent=$id'><button form-icon='!edit' class='purkynkaButton'></button></a>";
                        echo "<button form-icon='!delete' class='purkynkaButton btnTableDelete' subevent=$id></button>";
                    }
                    echo "</td>";
                    echo "<td>$date</td>";
                    echo "<td>$startTime</td>";
                    echo "<td>$endTime</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }

            //Echo buttons
            echo "<div class='formButtonBoxHolder'>";
            echo "<div class='formButtonBox'>";
            if ($result->role == "admin") {
                echo "<a href='./subevent.php?newSubevent=1&event=$eventId'><button form-icon='!add' class='purkynkaButton'><span>Vytvořit podudálost</span></button></a>";
            }
            if($resultSubeventId != null) {
                echo "<a href='./events.php?action=clearSubevent'><button  form-icon='!closeView' class='purkynkaButton'><span>Zavřít podpohled</span></button></a>";
            }
            echo "<a href='./events.php?action=clearEvent'><button form-icon='!closeView' class='purkynkaButton'><span>Zavřít pohled</span></button></a>";
            echo "</div>";
            echo "</div>";
            $stmt->close();
        } else {
            $stmt->close();
        }

        //Request events
        $stmt = $conn->prepare("SELECT id_events, name, type, active_since, active_until, registration_open, registration_close FROM events_teamPropaganda;");
        if (!$stmt->execute() || !$stmt->store_result()) {
            echo "<h1>Nelze získat seznam událostí pro zájemce o studium.</h1>";
        } else if ($stmt->num_rows == 0) {
            echo "<h1>Nejsou k dispozici žádné události pro zájemce o studium.</h1>";
        } else {
            echo "<h1>Dostupné události pro zájemce o studium</h1>";
            echo "<table>";
            echo "<tr>";
            echo "<th>Akce</th>";
            echo "<th>Název</th>";
            echo "<th>Druh</th>";
            echo "<th>Je aktivní</th>";
            echo "<th>Je registrace aktivní</th>";
            echo "<th>Počet přihlášených zájemců</th>";
            echo "</tr>";

            //List all events in table
            for ($i = 0; $i < $stmt->num_rows; $i++) {
                if (!$stmt->bind_result($id, $name, $type, $activeSince, $activeUntil, $registrationOpen, $registrationClose) || !$stmt->fetch()) {
                    $id = null;
                    $name = "CHYBA";
                    $type = "CHYBA";
                    $activeSince = "CHYBA";
                    $activeUntil = "CHYBA";
                    $registrationOpen = "CHYBA";
                    $registrationClose = "CHYBA";
                    $isActive = "CHYBA";
                    $isRegistrationActive = "CHYBA";
                } else {
                    $currentDate = new DateTime();
                    $activeSinceDate = new DateTime($activeSince);
                    $activeUntilDate = new DateTime($activeUntil);
                    $registrationOpenDate = new DateTime($registrationOpen);
                    $registrationCloseDate = new DateTime($registrationClose);

                    $isActive = "Ne";
                    $isRegistrationActive = "Ne";
                    if ($currentDate >= $activeSinceDate && $currentDate <= $activeUntilDate) {
                        $isActive = "Ano";
                    }
                    if ($currentDate >= $registrationOpen && $currentDate <= $registrationClose) {
                        $isRegistrationActive = "Ano";
                    }
                }
                echo "<tr class='clickHighlightRow'>";
                echo "<td class='formButtonBoxTable'>";
                echo "<a href='./events.php?selectEvent=$id'><button form-icon='!openView' class='purkynkaButton'><span>Otevřít pohled</span></button></a>";
                if ($result->role == "admin") {
                    echo "<a href='./event.php?event=$id'><button form-icon='!edit' class='purkynkaButton'></button></a>";
                    echo "<button form-icon='!delete' class='purkynkaButton btnTableDelete' event=$id></button>";
                }
                echo "</td>";
                echo "<td>$name</td>";
                echo "<td>$type</td>";
                echo "<td>$isActive</td>";
                echo "<td>$isRegistrationActive</td>";
                echo "<td>?</td>";
                echo "</tr>";
            }
            echo "</table>";

            //Echo buttons
            $stmt->close();
            echo "<div class='formButtonBoxHolder'>";
            echo "<div class='formButtonBox'>";
            if ($result->role == "admin") {
                echo "<a href='./event.php?newEvent=1'><button form-icon='!add' class='purkynkaButton'><span>Vytvořit událost pro zájemce o studium</span></button></a>";
            }
            echo "</div>";
            echo "</div>";
        }

        //Request company days
        $stmt = $conn->prepare("SELECT id_company_days, name, date, active_since,active_until,registration_open,registration_close FROM company_days_teamPropaganda;");
        if (!$stmt->execute() || !$stmt->store_result()) {
            echo "<h1>Nelze získat seznam dnů firem.</h1>";
        } else if ($stmt->num_rows == 0) {
            echo "<h1>Nejsou k dispozici dny firem.</h1>";
        } else {
            echo "<h1>Dostupné dny firem</h1>";
            echo "<table>";
            echo "<tr>";
            echo "<th>Akce</th>";
            echo "<th>Název</th>";
            echo "<th>Datum konání</th>";
            echo "<th>Je aktivní</th>";
            echo "<th>Je registrace aktivní</th>";
            echo "<th>Počet přihlášených firem</th>";
            echo "</tr>";

            //List all events in table
            for ($i = 0; $i < $stmt->num_rows; $i++) {
                if (!$stmt->bind_result($id, $name, $date, $activeSince, $activeUntil, $registrationOpen, $registrationClose) || !$stmt->fetch()) {
                    $id = null;
                    $name = "CHYBA";
                    $date = "CHYBA";
                    $activeSince = "CHYBA";
                    $activeUntil = "CHYBA";
                    $registrationOpen = "CHYBA";
                    $registrationClose = "CHYBA";
                    $isActive = "CHYBA";
                    $isRegistrationActive = "CHYBA";
                } else {
                    $currentDate = new DateTime();
                    $activeSinceDate = new DateTime($activeSince);
                    $activeUntilDate = new DateTime($activeUntil);
                    $registrationOpenDate = new DateTime($registrationOpen);
                    $registrationCloseDate = new DateTime($registrationClose);
                    $date = new DateTime($date)->format(STANDARD_CZECH_DATE_FORMAT_FULL);

                    $isActive = "Ne";
                    $isRegistrationActive = "Ne";
                    if ($currentDate >= $activeSinceDate && $currentDate <= $activeUntilDate) {
                        $isActive = "Ano";
                    }
                    if ($currentDate >= $registrationOpen && $currentDate <= $registrationClose) {
                        $isRegistrationActive = "Ano";
                    }
                }
                echo "<tr class='clickHighlightRow'>";
                echo "<td class='formButtonBoxTable'>";
                echo "<a href='./events.php?selectCompanyDay=$id'><button form-icon='!openView' class='purkynkaButton'><span>Otevřít pohled</span></button></a>";
                if ($result->role == "admin") {
                    echo "<a href='./companyDay.php?companyDay=$id'><button form-icon='!edit' class='purkynkaButton'></button></a>";
                    echo "<button form-icon='!delete' class='purkynkaButton btnTableDelete' companyDay=$id></button>";
                }
                echo "</td>";
                echo "<td>$name</td>";
                echo "<td>$date</td>";
                echo "<td>$isActive</td>";
                echo "<td>$isRegistrationActive</td>";
                echo "<td>?</td>";
                echo "</tr>";
            }
            echo "</table>";

            //Echo buttons
            $stmt->close();
            echo "<div class='formButtonBoxHolder'>";
            echo "<div class='formButtonBox'>";
            if ($result->role == "admin") {
                echo "<a href='./companyDay.php?newCompanyDay=1'><button form-icon='!add' class='purkynkaButton'><span>Vytvořit den firem</span></button></a>";
            }
            echo "</div>";
            echo "</div>";
        }
        ?>

    </main>
    <footer>

    </footer>
</body>
<script type="module" src="../formWebScripts/js/formScript.js"></script>
<script type="module" src="./events.js"></script>

</html>
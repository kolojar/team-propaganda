<?php
session_start();
require "../assets/config.php";
require "./adminFunctions.php";

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
        echo "Invalid usage of function - invalid action";
        die();
    }
}
if (isset($_GET["selectEvent"])) {
    setEventId($_GET["selectEvent"]);
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
    <title>Admin panel</title>
    <link rel="stylesheet" href="../formWebScripts/css/sharedStyle.css">
    <link rel="stylesheet" href="../formWebScripts/css/formStyle.css">
    <link rel="stylesheet" href="../formWebScripts/css/tableStyle.css">
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body class="pageHolder">
    <header>
        <?php $result = setupTitlebar($conn, "events.php") ?>
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
            echo "</tr>";

            //Request subevents
            $stmt = $conn->prepare("SELECT id_subevents, date, start_time, end_time FROM subevents WHERE id_events=?;");
            $stmt->bind_param("i", $eventId);
            $stmt->execute();
            $stmt->store_result();

            //List all subevents in table
            for ($i = 0; $i < $stmt->num_rows; $i++) {
                $stmt->bind_result($id, $date, $startTime, $endTime);
                $stmt->fetch();
                $date = DateTime::createFromFormat('Y-m-d', $date)->format(STANDARD_CZECH_DATE_FORMAT_FULL);
                echo "<tr class='clickHighlightRow'>";
                echo "<td>";
                echo "<div class='formButtonBox'>";
                echo "<a href='./events.php?selectSubevent=$id'><button class='formButton formInfoColor'>Otevřít podpohled</button></a>";
                if($result->role == "admin") {
                echo "<a href='./subevent.php?subevent=$id'><button class='formButton formWarnColor'>Upravit</button></a>";
                echo "<button class='formButton formErrorColor btnTableDelete' subevent=$id>Odstranit</button>";
                }
                echo "</div>";
                echo "</td>";
                echo "<td>$date</td>";
                echo "<td>$startTime</td>";
                echo "<td>$endTime</td>";
                echo "</tr>";
            }
            echo "</table>";
            echo "<div class='formButtonBoxHolder'>";
            echo "<div class='formButtonBox'>";
            if ($result->role == "admin") {
                echo "<a href='./subevent.php?newSubevent=1&event=$eventId'><button class='formButton formWarnColor'>Vytvořit podudálost</button></a>";
            }
            echo "<a href='./events.php?action=clearSubevent'><button class='formButton formErrorColor'>Zavřít podpohled</button></a>";
            echo "</div>";
            echo "</div>";
        }
        echo "<h1>Dostupné události</h1>";
        echo "<table class='styledTable styledTableAuto'>";
        echo "<tr>";
        echo "<th>Akce</th>";
        echo "<th>Název</th>";
        echo "<th>Druh</th>";
        echo "<th>Je aktivní</th>";
        echo "<th>Je registrace aktivní</th>";
        echo "<th>Počet přihlášených</th>";
        echo "</tr>";

        //Request events
        $stmt = $conn->prepare("SELECT id_events, name, type, active_since, active_until, registration_open, registration_close FROM events;");
        $stmt->execute();
        $stmt->store_result();

        //List all events in table
        for ($i = 0; $i < $stmt->num_rows; $i++) {
            $stmt->bind_result($id, $name, $type, $activeSince, $activeUntil, $registrationOpen, $registrationClose);
            $stmt->fetch();
            $currentDate = new DateTime();
            $activeSinceDate = new DateTime($activeSince);
            $activeUntilDate = new DateTime($activeUntil);
            $isActive = "Ne";
            $isRegistrationActive = "Ne";
            if($currentDate >= $activeSinceDate && $currentDate <= $activeUntilDate) {
                $isActive = "Ano";
            }
            $registrationOpenDate = new DateTime($registrationOpen);
            $registrationCloseDate = new DateTime($registrationClose);
            if($currentDate >= $registrationOpen && $currentDate <= $registrationClose) {
                $isRegistrationActive = "Ano";
            }
            echo "<tr class='clickHighlightRow'>";
            echo "<td>";
            echo "<div class='formButtonBox'>";
            echo "<a href='./events.php?selectEvent=$id'><button class='formButton formInfoColor'>Otevřít pohled</button></a>";
            if($result->role == "admin") {
            echo "<a href='./event.php?event=$id'><button class='formButton formWarnColor'>Upravit</button></a>";
            echo "<button class='formButton formErrorColor btnTableDelete' event=$id>Odstranit</button>";
            }
            echo "</td>";
            echo "</div>";
            echo "<td>$name</td>";
            echo "<td>$type</td>";
            echo "<td>$isActive</td>";
            echo "<td>$isRegistrationActive</td>";
            echo "<td>?</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "<div class='formButtonBoxHolder'>";
        echo "<div class='formButtonBox'>";
        if ($result->role == "admin") {
            echo "<a href='./event.php?newEvent=1'><button class='formButton formWarnColor'>Vytvořit událost</button></a>";
        }
        echo "<a href='./events.php?action=clearEvent'><button class='formButton formErrorColor'>Zavřít pohled</button></a>";
        echo "</div>";
        echo "</div>";
        ?>

    </main>
    <footer>

    </footer>
</body>
<script type="module" src="../formWebScripts/js/formScript.js"></script>
<script type="module" src="./events.js"></script>

</html>
<?php
session_start();
require '../assets/config.php';
require './userFunctions.php';
require '../assets/sharedFunctions.php';

if (isset($_POST["action"])) {
    if ($_POST["action"] == "addPayment") {
        //Check if values set
        if (!isset($_POST["variable_symbol"]) || !isset($_POST["bank_account"])) {
            http_response_code(400);
            echo "Invalid usage of function - missing table column parameters";
            die();
        }

        //Make SQL Update
        $stmt = $conn->prepare("UPDATE registered_attendants_teamPropaganda SET user_paid=CURRENT_TIMESTAMP(), bank_account=? WHERE variable_symbol=?");
        $stmt->bind_param("si", $_POST["bank_account"], $_POST["variable_symbol"]);
        if ($stmt->execute()) {
            http_response_code(201);
            echo "Entry updated.";
            die();
        } else {
            http_response_code(400);
            echo "Entry could not be updated.";
            die();
        }
    } else if ($_POST["action"] == "unregisterFromEvent") {
        //Check if values set
        if (!isset($_POST["id"]) || !isset($_POST["reason"])) {
            http_response_code(400);
            echo "Invalid usage of function - missing table column parameters";
            die();
        }

        //Security check
        $stmt = $conn->prepare("SELECT a.id_parent, e.registration_close FROM registered_attendants_teamPropaganda ra JOIN attendants_teamPropaganda a ON ra.id_attendants = a.id_attendants JOIN events_teamPropaganda e ON ra.id_events = e.id_events WHERE ra.variable_symbol = ?;");
        $stmt->bind_param("i", $_POST["id"]);
        if (!$stmt->execute()) {
            http_response_code(400);
            echo "Entry could not be CHECKED.";
            die();
        }
        $stmt->store_result();
        $stmt->bind_result($userIdCheck, $registrationClose);
        $stmt->fetch();
        if ($userIdCheck != $_SESSION["userId"]) {
            http_response_code(400);
            echo "Invalid usage of function - current user is not parent";
            die();
        }
        if (new DateTime($registrationClose) < new DateTime()) {
            http_response_code(400);
            echo "Invalid usage of function - cannot unregister after time limit";
            die();
        }

        //Get SQL info
        $stmt = $conn->prepare("SELECT id_attendants, id_events, bank_account,registered,paid FROM registered_attendants_teamPropaganda WHERE variable_symbol = ?");
        $stmt->bind_param("i", $_POST["id"]);
        if (!$stmt->execute()) {
            http_response_code(400);
            echo "Entry could not be SELECTed.";
            die();
        }
        $stmt->store_result();
        $stmt->bind_result($attendantId, $eventId, $bankAccount, $registered, $paid);
        $stmt->fetch();

        //Insert SQL entry
        $stmt = $conn->prepare("INSERT INTO unregistered_attendants_teamPropaganda(variable_symbol, id_attendants, id_events, bank_account, registered, paid, reason) VALUES (?,?,?,?,?,?,?)");
        $stmt->bind_param("issssss", $_POST["id"], $attendantId, $eventId, $bankAccount, $registered, $paid, $_POST["reason"]);
        if (!$stmt->execute()) {
            http_response_code(400);
            echo "Entry could not be INSERTed.";
            die();
        }

        //Delete SQL entry
        $stmt = $conn->prepare("DELETE FROM registered_attendants_teamPropaganda WHERE variable_symbol = ?");
        $stmt->bind_param("i", $_POST["id"]);
        if (!$stmt->execute()) {
            http_response_code(400);
            echo "Entry could not be DELETEd.";
            die();
        } else {
            http_response_code(201);
            echo "Entry moved.";
            die();
        }
    } else {
        http_response_code(400);
        echo "Invalid usage of function - missing action parameter";
        die();
    }
}
?>
<!DOCTYPE html>
<html lang='cz'>

<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Uživatelský panel</title>
    <link rel='stylesheet' href='../formWebScripts/css/sharedStyle.css'>
    <link rel='stylesheet' href='../formWebScripts/css/formStyle.css'>
    <link rel='stylesheet' href='../assets/style.css'>
    <link rel='stylesheet' href='./user.css'>
    <style>
        ol {
            list-style-type: none;
            counter-reset: event-counter;
        }

        ol li {
            counter-increment: event-counter;
        }

        ol li::before {
            content: counter(event-counter) ". událost: ";
        }
    </style>
</head>

<body class="pageHolder">
    <header>
        <?php setupTitlebarUser($conn) ?>
    </header>
    <main>
        <?php
        //Security check
        if (!isset($_GET["variableSymbol"])) {
            echo "<h1>Nebyl zadán platní variabilní symbol!</h1>";
            echo "<a href='./'><button class='formButton purkynkaButton'>Zpět na domovskou stránku</button></a>";
            die();
        }
        if (!checkIfParentMatches2($conn, $_GET["variableSymbol"])) {
            echo "<h1>Nejste rodičem tohoto zájemce!</h1>";
            echo "<a href='./'><button class='formButton purkynkaButton'>Zpět na domovskou stránku</button></a>";
            die();
        }
        ?>
        <fieldset id='eventInfo'>
            <legend>Informace o akci</legend>
            <?php
            //Get info from DB
            $stmt = $conn->prepare("SELECT  ra.id_attendants, ra.id_events, ra.registered, ra.user_paid, ra.paid, e.name, e.description, e.registration_close, e.price, ra.id_classrooms, c.name, s.id_subevents,s.date,s.start_time,s.end_time FROM registered_attendants_teamPropaganda ra JOIN events_teamPropaganda e ON ra.id_events = e.id_events JOIN classrooms_teamPropaganda c ON ra.id_classrooms = c.id_classrooms LEFT JOIN (SELECT id_subevents, date, start_time, end_time FROM subevents_teamPropaganda WHERE (date = CURRENT_DATE() AND (start_time >= CURRENT_TIME() OR (start_time <= CURRENT_TIME() AND end_time >= CURRENT_TIME()))) OR date > CURRENT_DATE() ORDER BY date ASC, start_time ASC LIMIT 1) s ON 1=1 WHERE variable_symbol=?;");
            $stmt->bind_param("i", $_GET["variableSymbol"]);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($attendantId, $eventId, $registered, $userPaid, $paid, $eventName, $eventDescription, $registrationClose, $price, $classroomId, $classroomName, $subeventIdNext, $subeventDate, $subeventStart, $subeventEnd);
            $stmt->fetch();

            //Format times
            $registeredFormated = new DateTime($registered)->format(STANDARD_CZECH_DATETIME_FORMAT_FULL);
            $registrationCloseDate = new DateTime($registrationClose);
            $registrationCloseFormated = $registrationCloseDate->format(STANDARD_CZECH_DATETIME_FORMAT_FULL);

            //Put to HTML
            echo "<p>Název akce: $eventName</p>";
            echo "<p>Popis akce: $eventDescription</p>";
            echo "<p>Přihlášen od: $registeredFormated</p>";
            echo "<p>Do kdy se lze odhlásit: $registrationCloseFormated</p>";
            if ($subeventIdNext != null) {
                $subeventDateFormated = new DateTime($subeventDate)->format(STANDARD_CZECH_DATE_FORMAT_FULL);
                $subeventStartFormated = new DateTime($subeventStart)->format(STANDARD_CZECH_TIME_FORMAT_FULL);
                echo "<p><b>Příští událost: $subeventDateFormated v: $subeventStartFormated v učebně: $classroomName</b></p>";
            }

            //Disable remove from action button
            $disabledRemove = "";
            if ($registrationCloseDate < new DateTime()) {
                $disabledRemove = "disabled";
            }

            //Put to HTML
            echo "<div class='formButtonBoxHolder'>";
            echo "    <div class='formButtonBox formJustifyLeft'>";
            echo "        <button class='formButton purkynkaButton' $disabledRemove id='btnRemoveAttendant'>Ohlásit zájemce z akce</button>";
            echo "    </div>";
            echo "</div>";
            ?>
        </fieldset><br>
        <fieldset id='paymentInfo'>
            <legend>Informace o platbě</legend>
            <?php
            //Sort payment info
            echo "<p>Stav zaplacení: ";
            if ($paid != null) {
                echo "OK</p>";
                $paidFormated = new DateTime($paid)->format(STANDARD_CZECH_DATETIME_FORMAT_FULL);
                echo "<p>Datum zaplacení: $paidFormated";
            } else {
                if ($userPaid != null) {
                    echo "Čeká na zpracování</p>";
                    $userPaidFormated = new DateTime($userPaid)->format(STANDARD_CZECH_DATETIME_FORMAT_FULL);
                    echo "<p>Datum odeslání platby: $userPaidFormated";
                } else {
                    echo "Nezaplaceno</p>";
                    echo "<p>Zaplaťte co nejdříve, ideálně do: $registrationCloseFormated";
                }
            }
            echo "</p>";

            //Disable remove from action button
            $disabledRemove2 = "";
            $variableSymbolFormated = str_pad($_GET["variableSymbol"], 10, "0", STR_PAD_LEFT);
            if ($userPaid != null) {
                $disabledRemove2 = "disabled";
                echo "<p>Variabilní symbol: <span class='fontMono'>$variableSymbolFormated</span></p>";
            }

            //Put to HTML
            echo "<div class='formButtonBoxHolder'>";
            echo "    <div class='formButtonBox formJustifyLeft'>";
            echo "        <button class='formButton purkynkaButton' $disabledRemove2 id='btnPay' variableSymbol='$variableSymbolFormated' price='$price'>Zaplatit</button>";
            echo "    </div>";
            echo "</div>";
            ?>
            <p><i>Poznámka: Prosíme o trpělivost, jelikož peníze mohou někdy cestovat několik dní.</i></p>
        </fieldset><br>
        <fieldset id='subeventsInfo'>
            <legend>Události</legend>
            <p>Proběhlé události se zobrazují ve formátu: DATUM → OD - DO = DOCHÁZKA - kliknutím na modrý název zobrazíte podrobnosti a materiály:</p>
            <ol>
                <?php
                //Get all subevents
                $variableSymbol = $_GET["variableSymbol"];
                $stmt = $conn->prepare("SELECT s.id_subevents,s.date, s.start_time, s.end_time,ap.present FROM subevents_teamPropaganda s LEFT JOIN attendants_presence_teamPropaganda ap ON s.id_subevents = ap.id_subevents WHERE s.id_events = ? AND (ap.variable_symbol = ? OR ap.variable_symbol IS NULL);");
                $stmt->bind_param("ii", $eventId, $variableSymbol);
                $stmt->execute();
                $stmt->store_result();
                for ($i = 0; $i < $stmt->num_rows; $i++) {
                    $stmt->bind_result($subeventId, $date, $startTime, $endTime, $present);
                    $stmt->fetch();

                    //Format dates of subevents
                    $dateFormated = new DateTime($date)->format(STANDARD_CZECH_DATE_FORMAT_FULL);
                    $startTimeFormated = new DateTime($startTime)->format(STANDARD_CZECH_TIME_FORMAT_FULL);
                    $endTimeFormated = new DateTime($endTime)->format(STANDARD_CZECH_TIME_FORMAT_FULL);
                    $currentDate = new DateTime();

                    //Sort presence
                    $presence = $present == 1 ? "Přítomen" : "Nepřítomen";
                    if ($present == null) {
                        $startCombined = DateTime::createFromFormat(STANDARD_CZECH_DATETIME_FORMAT_FULL, $dateFormated . ' ' . $startTimeFormated);
                        $endCombined = DateTime::createFromFormat(STANDARD_CZECH_DATETIME_FORMAT_FULL, $dateFormated . ' ' . $endTimeFormated);
                        if ($startCombined > $currentDate) {
                            $presence = "Událost ještě neproběhla.";
                        } else if ($currentDate >= $startCombined && $currentDate <= $endCombined) {
                            $presence = "Událost probíhá, docházka zatím nezapsána.";
                        } else {
                            $presence = "Docházka neznámá";
                        }
                    }

                    //Put to HTML
                    echo "<li><a href='./subevent.php?subeventId=$subeventId&variableSymbol=$variableSymbol'>$dateFormated → $startTimeFormated - $endTimeFormated</a> = $presence</li>";
                }
                ?>
            </ol>
        </fieldset>
    </main>
    <script type='module' src='../formWebScripts/js/formScript.js'></script>
    <script src='./event.js' type='module'></script>
</body>
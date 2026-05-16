<?php
session_start();
require '../assets/config.php';
require './userFunctions.php';
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
            $stmt = $conn->prepare("SELECT  ra.id_attendants, ra.id_events, ra.registered, ra.user_paid, ra.paid, e.name, e.description, e.registration_close, e.price, ra.id_classrooms, c.name, s.id_subevents,s.date,s.start_time,s.end_time FROM registered_attendants_teamPropaganda ra JOIN events_teamPropaganda e ON ra.id_events = e.id_events JOIN classrooms_teamPropaganda c ON ra.id_classrooms = c.id_classrooms LEFT JOIN (SELECT id_subevents, date, start_time, end_time FROM subevents_teamPropaganda WHERE (date = CURRENT_DATE() AND (start_time >= CURRENT_TIME() OR (start_time <= CURRENT_TIME() AND end_time >= CURRENT_TIME()))) OR date > CURRENT_DATE() ORDER BY date ASC, start_time ASC LIMIT 1) s ON 1=1 WHERE variable_symbol=?;");
            $stmt->bind_param("i", $_GET["variableSymbol"]);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($attendantId, $eventId, $registered, $userPaid, $paid,$eventName, $eventDescription, $registrationClose, $price, $classroomId, $classroomName, $subeventId, $subeventDate, $subeventStart, $subeventEnd);
            $stmt->fetch();
            echo "<p>Název akce: $eventName</p>";
            echo "<p>Popis akce: $eventDescription</p>";
            echo "<p>Přihlášen od: $registered</p>";
            echo "<p>Do kdy se lze odhlásit: $registrationClose</p>";
            if($subeventId != null) {
                echo "<p><b>Příští událost: $subeventDate v(e): $subeventStart v učebně: $classroomName</b></p>";
            }
            ?>
            <div class='formButtonBoxHolder'>
                <div class='formButtonBox formJustifyLeft'>
                    <button class='formButton purkynkaButton'>Ohlásit zájemce z akce</button>
                </div>
            </div>
        </fieldset><br>
        <fieldset id='paymentInfo'>
            <legend>Informace o platbě</legend>
            <p>Stav zaplacení: OK + DATUM / NEOK</p>
            <div class='formButtonBoxHolder'>
                <div class='formButtonBox formJustifyLeft'>
                    <button class='formButton purkynkaButton'>Zaplatit</button>
                </div>
            </div>
            <p><i>Poznámka: Prosíme o trpělivost, jelikož peníze mohou někdy cestovat několik dní.</i></p>
        </fieldset><br>
        <fieldset id='subeventsInfo'>
            <legend>Události</legend>
            <p>Proběhlé události se zobrazují ve formátu: DATUM - OD → DO = DOCHÁZKA - kliknutím na modrý název zobrazíte podrobnosti a materiály:</p>
            <ol>
                <li><a href=''>DATUM</a> → OD - DO = DOCHÁZKA</li>
                <li>B</li>
            </ol>
        </fieldset>
    </main>
    <script type='module' src='../formWebScripts/js/formScript.js'></script>
    <script src='./subevent.js' type='module'></script>
</body>
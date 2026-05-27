<?php
session_start();
if (!isset($_SESSION["userId"])) {
    header("Location: ./index.php");
    exit();
}
require '../assets/config.php';
require './userFunctions.php';
require '../assets/sharedFunctions.php';

if (isset($_POST["action"])) {
    if ($_POST["action"] == "addPayment") {
        //Check if values set
        if (!isset($_POST["variable_symbol"]) || !isset($_POST["bank_account"])) {
            http_response_code(400);
            echo "Neplatné použití funkce - chybí parametr";
            die();
        }

        //Security check
        $stmt = $conn->prepare("SELECT e.price FROM events_teamPropaganda e JOIN registered_attendants_teamPropaganda ra ON e.id_events = ra.id_events WHERE ra.variable_symbol = ?;");
        if (!$stmt->bind_param("i", $_POST["variable_symbol"]) || !$stmt->execute()) {
            http_response_code(400);
            echo "Entry could not be CHECKED.";
            die();
        }
        $res = $stmt->get_result()->fetch_assoc();
        //logToConsole($res["price"]);
        //$stmt->fetch();
        if ($res["price"] == 0) {
            http_response_code(400);
            echo "Událost nelze zaplatit, je zdarma.";
            $stmt->close();
            die();
        }
        $stmt->close();

        //Make SQL Update
        $stmt = $conn->prepare("UPDATE registered_attendants_teamPropaganda SET user_paid=CURRENT_TIMESTAMP(), bank_account=? WHERE variable_symbol=?");
        if (        $stmt->bind_param("si", $_POST["bank_account"], $_POST["variable_symbol"]) && $stmt->execute() && $stmt->close()) {
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
            echo "Neplatné použití funkce - chybí parametr";
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
        $res = $stmt->get_result()->fetch_assoc();
        //$stmt->fetch();
        if ($res["id_parent"] != $_SESSION["userId"]) {
            http_response_code(400);
            echo "Invalid usage of function - current user is not parent";
            die();
        }
        if (new DateTime($res["registration_close"]) < new DateTime()) {
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
    } else if ($_POST["action"] == "rmcd") {
        if (!isset($_POST["id"]) || !isset($_POST["idCD"])) {
            http_response_code(400);
            echo "Neplatné použití funkce - chybí parametr";
            die();
        }
        if (
            $conn->query("DELETE FROM sites_teamPropaganda WHERE id_companies = " . $_POST["id"] . " and id_company_days = " . $_POST["idCD"]) &&
            $conn->query("DELETE FROM company_days_companies_teamPropaganda WHERE id_companies = " . $_POST["id"] . " and id_company_days = " . $_POST["idCD"])
        ) {
            echo "Odhlášení proběhlo úspěšně.";
            die;
        } else {
            http_response_code(400);
            echo "Nepodařilo se smazat data z databáze.";
            die;
        }
    } else if ($_POST["action"] == "addSite") {
        if (!isset($_POST["id"]) || !isset($_POST["idCD"])) {
            http_response_code(400);
            echo "Neplatné použití funkce - chybí parametr";
            die();
        }

        if ($conn->query("INSERT INTO sites_teamPropaganda (id_company_days, id_companies) VALUES (" . $_POST["idCD"] . ", " . $_POST["id"] . ")")) {
            echo "Data zapsána do databáze úspěšně.";
            die;
        } else {
            http_response_code(400);
            echo "Nepodařilo se zapsat data do databáze.";
            die;
        }
    } else {
        http_response_code(400);
        echo "Neplatné použití funkce - neplatná akce";
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
        <?php $result = setupTitlebarUser($conn) ?>
    </header>
    <main>
        <?php
        //Security check
        if (!isset($_GET["variableSymbol"]) && !isset($_GET["cd"])) {
            echo "<h1>Nebyl zadán platní variabilní symbol!</h1>";
            die();
        }
        if (isset($_GET["variableSymbol"]) && !checkIfParentMatches2($conn, $_GET["variableSymbol"])) {
            echo "<h1>Nejste rodičem tohoto zájemce!</h1>";
            die();
        }
        if (isset($_GET["cd"]) && !checkIfCDMatches($_GET["cd"])) {
            echo "<h1>Nejste přihlášen na tento den firem!</h1>";
            die;
        }
        ?>
        <fieldset id='eventInfo'>
            <legend>Informace o akci</legend>
            <?php
            if ($result->type == userType::KLAL) {
                //Get info from DB
                $stmt = $conn->prepare("SELECT ra.*, e.name ename, e.description, e.registration_close, e.price, c.name cname, s.id_subevents,s.date,s.start_time,s.end_time FROM registered_attendants_teamPropaganda ra JOIN events_teamPropaganda e ON ra.id_events = e.id_events LEFT JOIN (SELECT id_subevents, date, start_time, end_time FROM subevents_teamPropaganda WHERE (date = CURRENT_DATE() AND (start_time >= CURRENT_TIME() OR (start_time <= CURRENT_TIME() AND end_time >= CURRENT_TIME()))) OR date > CURRENT_DATE() ORDER BY date ASC, start_time ASC LIMIT 1) s ON 1=1 LEFT JOIN attendants_presence_teamPropaganda ap ON ap.id_subevents = s.id_subevents AND ap.variable_symbol = ra.variable_symbol LEFT JOIN classrooms_teamPropaganda c ON ap.id_classrooms = c.id_classrooms WHERE ra.variable_symbol=?;");
                $stmt->bind_param("i", $_GET["variableSymbol"]);
                $stmt->execute();
                $res = $stmt->get_result()->fetch_assoc();
                //$stmt->bind_result($attendantId, $eventId, $registered, $userPaid, $paid, $eventName, $eventDescription, $registrationClose, $price, $classroomId, $classroomName, $subeventIdNext, $subeventDate, $subeventStart, $subeventEnd);
                //$stmt->fetch();
                //Format times
                $registeredFormated = new DateTime($res["registered"])->format(STANDARD_CZECH_DATETIME_FORMAT_FULL);
                $registrationCloseDate = new DateTime($res["registration_close"]);
                $registrationCloseFormated = $registrationCloseDate->format(STANDARD_CZECH_DATETIME_FORMAT_FULL);

                //Put to HTML
                echo "<p>Název akce: " . $res["ename"] . "</p>";
                echo "<p>Popis akce: " . $res["description"] . "</p>";
                echo "<p>Přihlášen od: $registeredFormated</p>";
                echo "<p>Do kdy se lze odhlásit: $registrationCloseFormated</p>";
                if ($res["id_subevents"] != null) {
                    $subeventDateFormated = new DateTime($res["date"])->format(STANDARD_CZECH_DATE_FORMAT_FULL);
                    $subeventStartFormated = new DateTime($res["start_time"])->format(STANDARD_CZECH_TIME_FORMAT_FULL);
                    echo "<p><b>Příští událost: $subeventDateFormated v: $subeventStartFormated v učebně: " . $res["cname"] . "</b></p>";
                }

                //Disable remove from action button
                $disabledRemove = "";
                if ($registrationCloseDate < new DateTime()) {
                    $disabledRemove = "disabled";
                }

                //Put to HTML
                echo "<div class='formButtonBoxHolder'>";
                echo "    <div class='formButtonBox formJustifyLeft'>";
                echo "        <button class='formButton purkynkaButton' $disabledRemove id='btnRemoveAttendant'>Odhlásit zájemce z akce</button>";
                echo "    </div>";
                echo "</div>";

                ?>
            </fieldset><br>
            <fieldset id='paymentInfo'>
                <legend>Informace o platbě</legend>
                <?php
                //Sort payment info
                if ($res["price"] == 0) {
                    echo "<p>Tato událost je zdarma.</p>";
                } else {
                    echo "<p>Stav zaplacení: ";
                    if ($res["paid"] != null) {
                        echo "OK</p>";
                        $paidFormated = new DateTime($res["paid"])->format(STANDARD_CZECH_DATETIME_FORMAT_FULL);
                        echo "<p>Datum zaplacení: $paidFormated";
                    } else {
                        if ($res["user_paid"] != null) {
                            echo "Čeká na zpracování</p>";
                            $userPaidFormated = new DateTime($res["user_paid"])->format(STANDARD_CZECH_DATETIME_FORMAT_FULL);
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
                    if ($res["user_paid"] != null) {
                        $disabledRemove2 = "disabled";
                        echo "<p>Variabilní symbol: <span class='fontMono'>$variableSymbolFormated</span></p>";
                    }

                    $price = $res['price'];
                    //Put to HTML
                    echo "<div class='formButtonBoxHolder'>";
                    echo "    <div class='formButtonBox formJustifyLeft'>";
                    echo "        <button class='formButton purkynkaButton' $disabledRemove2 id='btnPay' variableSymbol='$variableSymbolFormated' price='$price'>Zaplatit</button>";
                    echo "    </div>";
                    echo "</div>";
                    echo "<p><i>Poznámka: Prosíme o trpělivost, jelikož peníze mohou někdy cestovat několik dní.</i></p>";
                }
                ?>
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
                    $res = $stmt->get_result();
                    if ($res->num_rows > 0) {
                        while ($row = $res->fetch_assoc()) {
                            //Format dates of subevents
                            $dateFormated = new DateTime($row["date"])->format(STANDARD_CZECH_DATE_FORMAT_FULL);
                            $startTimeFormated = new DateTime($row["start_time"])->format(STANDARD_CZECH_TIME_FORMAT_FULL);
                            $endTimeFormated = new DateTime($row["end_time"])->format(STANDARD_CZECH_TIME_FORMAT_FULL);
                            $currentDate = new DateTime();

                            //Sort presence
                            $presence = $row["present"] == 1 ? "Přítomen" : "Nepřítomen";
                            if ($row["present"] == null) {
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
                            echo "<li><a href='./subevent.php?subeventId=" . $row["id_subevents"] . "&variableSymbol=$variableSymbol'>$dateFormated → $startTimeFormated - $endTimeFormated</a> = $presence</li>";
                        }
                    }
                    ?>
                </ol>
                <?php
            } else {
                $cd = $conn->query("SELECT * FROM company_days_teamPropaganda WHERE id_company_days = " . $_GET["cd"])->fetch_assoc();
                $stmt2 = $conn->query("SELECT s.id_sites, s.seats, s.electricity, s.id_presentations, s.floor FROM sites_teamPropaganda s WHERE id_company_days = " . $_GET["cd"] . " and id_companies = " . $_SESSION["companyId"]);

                $registrationCloseDate = new DateTime($cd["registration_close"]);
                $registrationCloseFormated = $registrationCloseDate->format(STANDARD_CZECH_DATETIME_FORMAT_FULL);

                //Disable remove from action button
                $disabledRemove = "";
                if ($registrationCloseDate < new DateTime()) {
                    $disabledRemove = "disabled";
                }

                //Put to HTML
                echo "<p>Datum konání akce: " . new DateTime($cd["date"])->format(STANDARD_CZECH_DATE_FORMAT_FULL) . "</p>";
                echo "<p>Název akce: " . $cd["name"] . "</p>";
                echo "<p>Popis akce: " . $cd["description"] . "</p>";
                echo "<p>Do kdy se lze odhlásit: $registrationCloseFormated</p>";
                echo "<div class='formButtonBoxHolder'>";
                echo "    <div class='formButtonBox formJustifyLeft'>";
                echo "        <button class='formButton purkynkaButton' $disabledRemove id='btnRemoveCD' comp='" . $_SESSION["companyId"] . "'>Odhlásit z tohoto dne firem.</button>";
                echo "    </div>";
                echo "</div>";

                echo "</fieldset><br><fieldset>";
                echo "<legend>Stánky</legend>";
                echo "<div class='formButtonBoxHolder'>";
                echo "    <div class='formButtonBox formJustifyLeft'>";
                echo "        <button class='formButton purkynkaButton' $disabledRemove id='btnAddSite' comp='" . $_SESSION["companyId"] . "'>Přidat stánek</button>";
                echo "    </div>";
                echo "</div>";
                while ($row = $stmt2->fetch_assoc()) {
                    echo "<fieldset>";


                    echo "</fieldset>";
                }
            }
            ?>
        </fieldset><a href='./'><button class='formButton purkynkaButton'>Zpět na domovskou stránku</button></a>
    </main>
    <script type='module' src='../formWebScripts/js/formScript.js'></script>
    <script src='./event.js' type='module'></script>
</body>
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
        if (!isset($_POST["id_registered_attendants"]) || !isset($_POST["bank_account"])) {
            http_response_code(400);
            echo "Neplatné použití funkce - chybí parametr";
            die();
        }

        //Security check
        $stmt = $conn->prepare("SELECT e.price FROM events_teamPropaganda e JOIN registered_attendants_teamPropaganda ra ON e.id_events = ra.id_events WHERE ra.id_registered_attendants = ?;");
        if (!$stmt->bind_param("i", $_POST["id_registered_attendants"]) || !$stmt->execute()) {
            http_response_code(400);
            echo "Nepodařilo se získat data z databáze.";
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
        $stmt = $conn->prepare("UPDATE registered_attendants_teamPropaganda SET user_paid=CURRENT_TIMESTAMP(), bank_account=? WHERE id_registered_attendants=?");
        if ($stmt->bind_param("si", $_POST["bank_account"], $_POST["id_registered_attendants"]) && $stmt->execute() && $stmt->close()) {
            http_response_code(201);
            echo "Data zapsána do databáze.";
            die();
        } else {
            http_response_code(400);
            echo "Nepodařilo se zapsat data do databáze.";
            die();
        }
    } else if ($_POST["action"] == "unregisterFromEvent") {
        //Check if values set
        if (!isset($_POST["attendant"]) ||!isset($_POST["event"]) || !isset($_POST["reason"])) {
            http_response_code(400);
            echo "Neplatné použití funkce - chybí parametr";
            die();
        }

        //Security check
        $stmt = $conn->prepare("SELECT a.id_parent, e.registration_close FROM registered_attendants_teamPropaganda ra JOIN attendants_teamPropaganda a ON ra.id_attendants = a.id_attendants JOIN events_teamPropaganda e ON ra.id_events = e.id_events WHERE ra.id_attendants = ? AND ra.id_events = ?;");
        $stmt->bind_param("ii", $_POST["attendant"],$_POST["event"]);
        if (!$stmt->execute()) {
            http_response_code(400);
            echo "Nepodařilo se získat data z databáze.";
            die();
        }
        $res = $stmt->get_result()->fetch_assoc();
        //$stmt->fetch();
        if ($res["id_parent"] != $_SESSION["userId"]) {
            http_response_code(400);
            echo "Neplatné použití funkce - tento uživatel není rodičem vybraného dítěte.";
            die();
        }
        if (new DateTime($res["registration_close"]) < new DateTime()) {
            http_response_code(400);
            echo "Neplatné použití funkce - nelze odhlásit uživatele po ukončení registrace.";
            die();
        }

        //Get SQL info
        $stmt = $conn->prepare("SELECT id_registered_attendants, bank_account,registered,paid FROM registered_attendants_teamPropaganda WHERE id_attendants = ? AND id_events = ?");
        $stmt->bind_param("i", $_POST["attendant"],$_POST["event"]);
        if (!$stmt->execute()) {
            http_response_code(400);
            echo "Nepodařilo se získat data z databáze.";
            die();
        }
        $stmt->store_result();
        $stmt->bind_result($variableSymbol, $bankAccount, $registered, $paid);
        $stmt->fetch();

        //Insert SQL entry
        $stmt = $conn->prepare("INSERT INTO unregistered_attendants_teamPropaganda(id_registered_attendants, id_attendants, id_events, bank_account, registered, paid, reason) VALUES (?,?,?,?,?,?,?)");
        $stmt->bind_param("issssss", $variableSymbol, $_POST["attendant"],$_POST["event"], $bankAccount, $registered, $paid, $_POST["reason"]);
        if (!$stmt->execute()) {
            http_response_code(400);
            echo "Nepodařilo se zapsat data do databáze.";
            die();
        }

        //Delete SQL entry
        $stmt = $conn->prepare("DELETE FROM registered_attendants_teamPropaganda WHERE id_registered_attendants = ?");
        $stmt->bind_param("i", $variableSymbol);
        if (!$stmt->execute()) {
            http_response_code(400);
            echo "Nepodařilo se smazat data z databáze.";
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
    } else if ($_POST["action"] == "addPres") {
        if (!isset($_POST["id"]) || !isset($_POST["idCD"])) {
            http_response_code(400);
            echo "Neplatné použití funkce - chybí parametr";
            die();
        }
        if (!$conn->query("INSERT INTO presentations_teamPropaganda (id_companies) VALUES (" . $_POST["id"] . ")")) {
            http_response_code(400);
            echo "Nepodařilo se zapsat data do databáze.";
            die;
        }
        if (!$conn->query("INSERT INTO sites_teamPropaganda (id_company_days, id_companies, id_presentations, isClass) VALUES (" . $_POST["idCD"] . ", " . $_POST["id"] . ", " . $conn->insert_id . ", 1)")) {
            http_response_code(400);
            echo "Nepodařilo se zapsat data do databáze.";
            die;
        }
        echo "Data zapsána do databáze úspěšně.";
        die;
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
    <meta name="form-locales-main" content="../formWebScripts/locales/">
    <title>Uživatelský panel</title>
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
        //Get variable symbol
        $variableSymbol = null;
        if (!isset($_GET["cd"])) {
            if (!isset($_GET["attendant"]) || !isset($_GET["event"])) {
                echo "<h1>Nebyly zadány identifikační údaje události.</h1>";
                die();
            }
            $stmt = $conn->prepare("SELECT id_registered_attendants FROM registered_attendants_teamPropaganda WHERE id_attendants = ? AND id_events = ?;");
            if (!$stmt->bind_param("ii", $_GET["attendant"], $_GET["event"]) || !$stmt->execute() || !$stmt->store_result() || !$stmt->bind_result($variableSymbol) || !$stmt->fetch() || !$stmt->close()) {
                $stmt->close();
                echo "<h1>Nelze získat variabilní symbol!</h1>";
                die();
            }
        }

        //Security check
        if ($variableSymbol === null && !isset($_GET["cd"])) {
            echo "<h1>Nebyl zadán platní variabilní symbol!</h1>";
            die();
        }
        if ($variableSymbol !== null && !checkIfParentMatches2($conn, $variableSymbol)) {
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
                $stmt = $conn->prepare("SELECT ra.*, e.id_events, e.name ename, e.description, e.registration_close, e.price, c.name cname, s.id_subevents,s.date,s.start_time,s.end_time FROM registered_attendants_teamPropaganda ra JOIN events_teamPropaganda e ON ra.id_events = e.id_events LEFT JOIN (SELECT id_subevents, date, start_time, end_time FROM subevents_teamPropaganda WHERE (date = CURRENT_DATE() AND (start_time >= CURRENT_TIME() OR (start_time <= CURRENT_TIME() AND end_time >= CURRENT_TIME()))) OR date > CURRENT_DATE() ORDER BY date ASC, start_time ASC LIMIT 1) s ON 1=1 LEFT JOIN attendants_presence_teamPropaganda ap ON ap.id_subevents = s.id_subevents AND ap.id_registered_attendants = ra.id_registered_attendants LEFT JOIN classrooms_teamPropaganda c ON ap.id_classrooms = c.id_classrooms WHERE ra.id_registered_attendants=?;");
                $stmt->bind_param("i", $variableSymbol);
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
                    $variableSymbolFormated = str_pad($variableSymbol, 10, "0", STR_PAD_LEFT);
                    if ($res["user_paid"] != null) {
                        $disabledRemove2 = "disabled";
                        echo "<p>Variabilní symbol: <span class='fontMono'>$variableSymbolFormated</span></p>";
                    }

                    $price = $res['price'];
                    //Put to HTML
                    echo "<div class='formButtonBoxHolder'>";
                    echo "    <div class='formButtonBox formJustifyLeft'>";
                    echo "        <button class='formButton purkynkaButton' $disabledRemove2 id='btnPay' attendant='$attendantId' event='$eventId' price='$price'>Zaplatit</button>";
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
                    $attendantId = $_GET["attendant"];
                    $eventId = $_GET["event"];
                    $stmt = $conn->prepare("SELECT s.id_subevents,s.date, s.start_time, s.end_time,ap.present FROM subevents_teamPropaganda s LEFT JOIN attendants_presence_teamPropaganda ap ON s.id_subevents = ap.id_subevents WHERE s.id_events = ? AND (ap.id_registered_attendants = ? OR ap.id_registered_attendants IS NULL);");
                    $stmt->bind_param("ii", $res["id_events"], $variableSymbol);
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
                            echo "<li><a href='./subevent.php?subeventId=" . $row["id_subevents"] . "&attendant=$attendantId&event=$eventId'>$dateFormated → $startTimeFormated - $endTimeFormated</a> = $presence</li>";
                        }
                    }
                    ?>
                </ol>
                <?php
            } else {
                $cd = $conn->query("SELECT * FROM company_days_teamPropaganda WHERE id_company_days = " . $_GET["cd"])->fetch_assoc();
                $stmt2 = $conn->query("SELECT s.id_sites, s.seats, s.electricity FROM sites_teamPropaganda s WHERE id_presentations IS NULL and id_company_days = " . $_GET["cd"] . " and id_companies = " . $_SESSION["companyId"]);

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
                    echo "<fieldset class='siteInfo' site='" . $row["id_sites"] . "'>";
                    echo "<form-input value-id='seats' $disabledRemove label='Počet osob na stánku:' class='validate' type='number' do-change-check='true' value='" . $row["seats"] . "' original-value='" . $row["seats"] . "'></form-input>";
                    echo "<form-toggle value-id='electricity' $disabledRemove label-before='Potřebuji přístup k zásuvce:' class='validate' type='checkbox' do-change-check='true' original-checked='" . (($row["electricity"] == 1) ? "true" : "false") . "' " . (($row["electricity"] == 1) ? "checked" : "") . "></form-toggle><br>";

                    echo "<button class='formButton purkynkaButton rmSite' $disabledRemove site='" . $row["id_sites"] . "'>Odstranit stánek</button>
                          <div class='formButtonBox formJustifyRight'>
                              <button class='formButton purkynkaButton btnCancel'>Zrušit provedené změny</button>
                              <button class='formButton purkynkaButton btnSave' $disabledRemove>Uložit změny</button>
                          </div>
                          </fieldset>";
                }

                $stmt3 = $conn->query("SELECT s.id_sites, s.seats, s.electricity, p.name, p.description, p.schedule FROM sites_teamPropaganda s NATURAL JOIN presentations_teamPropaganda p WHERE s.id_presentations IS NOT NULL and s.id_company_days = " . $_GET["cd"] . " and s.id_companies = " . $_SESSION["companyId"]);

                echo "</fieldset><br><fieldset>";
                echo "<legend>Prezentace</legend>";
                echo "<div class='formButtonBoxHolder'>";
                echo "    <div class='formButtonBox formJustifyLeft'>";
                echo "        <button class='formButton purkynkaButton' $disabledRemove id='btnAddPres' comp='" . $_SESSION["companyId"] . "'>Přidat prezentaci</button>";
                echo "    </div>";
                echo "</div>";

                while ($row = $stmt3->fetch_assoc()) {
                    echo "<fieldset class='presInfo' site='" . $row["id_sites"] . "'>";
                    echo "<form-input value-id='seats' $disabledRemove label='Počet prezentujících:' class='validate' type='number' do-change-check='true' value='" . $row["seats"] . "' original-value='" . $row["seats"] . "'></form-input>";
                    echo "<form-input value-id='name' $disabledRemove label='Název prezentace:' class='validate' type='text' do-change-check='true' value='" . $row["name"] . "' original-value='" . $row["name"] . "'></form-input>";
                    echo "<form-input value-id='description' $disabledRemove label='Popis prezentace:' class='validate' type='textarea' do-change-check='true' value='" . $row["description"] . "' original-value='" . $row["description"] . "'></form-input>";

                    echo "<button class='formButton purkynkaButton rmPres' $disabledRemove site='" . $row["id_sites"] . "'>Odstranit prezentaci</button>
                          <div class='formButtonBox formJustifyRight'>
                              <button class='formButton purkynkaButton btnCancel'>Zrušit provedené změny</button>
                              <button class='formButton purkynkaButton btnSave' $disabledRemove>Uložit změny</button>
                          </div>
                          </fieldset>";
                }
                echo "</fieldset>";
            }
            ?>
        </fieldset><a href='./'><button class='formButton purkynkaButton'>Zpět na domovskou stránku</button></a>
    </main>
    <script type='module' src='../formWebScripts/js/formScript.js'></script>
    <script src='./event.js' type='module'></script>
</body>
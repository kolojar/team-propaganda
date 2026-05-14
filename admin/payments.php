<?php
session_start();
require "../assets/config.php";
require "./adminFunctions.php";
?>

<!DOCTYPE html>
<html lang="cs">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Platby</title>
    <link rel="stylesheet" href="../formWebScripts/css/sharedStyle.css">
    <link rel="stylesheet" href="../formWebScripts/css/tableStyle.css">
    <link rel="stylesheet" href="../formWebScripts/css/formStyle.css">
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body class="pageHolder">
    <header>
        <?php $result = setupTitlebar($conn, "payments.php") ?>
    </header>
    <main>
        <?php
        ////Get highlighted schools
        //$highlightSchools = [];
        //if(isset($_GET['schools'])) {
        //    $highlightSchools = explode(',',$_GET["schools"]);
        //}
        
        $found = false;

        //Request waiting for refund attendants
        $stmt = $conn->prepare("SELECT ua.variable_symbol, ua.bank_account, ua.registered,ua.paid, ua.unregistered, ua.reason, ua.id_attendants, a.name, a.surname, a.id_parent, u.name, u.surname,u.email, e.price FROM unregistered_attendants_teamPropaganda ua LEFT JOIN attendants_teamPropaganda a ON ua.id_attendants = a.id_attendants LEFT JOIN users_teamPropaganda u ON a.id_parent = u.id_users LEFT JOIN events_teamPropaganda e ON ua.id_events = e.id_events WHERE ua.id_events = ? AND ua.refunded IS NULL AND ua.paid IS NOT NULL;");
        $stmt->bind_param("i", $_COOKIE["adminEventId"]);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $found = true;
            echo "<h1>Zájemci čekající na vrácení peněz - v době odhlášení měli zájemci zaplaceno</h1>
                  <table class='styledTable styledTableAuto'>
                      <tr>
                          <th>Akce</th>
                          <th>Variabilní symbol platby</th>
                          <th>Částka</th>
                          <th>Datum registrace</th>
                          <th>Datum zaplacení</th>
                          <th>Datum odhlášení</th>
                          <th>Jméno a přijmení</th>
                          <th>Zákonný zástupce</th>
                          <th>Email zákonného zástupce</th>
                          <th>Důvod odhlášení</th>
                      </tr>";

            //List all attendants in table
            for ($i = 0; $i < $stmt->num_rows; $i++) {
                $stmt->bind_result($variableSymbol, $bankAccount, $registered, $paid, $unregistered, $reason, $attendantId, $attendantName, $attendantSurname, $parentId, $parentName, $parentSurname, $parentEmail, $eventPrice);
                $stmt->fetch();
                $variableSymbolFormated = str_pad($variableSymbol, 10, "0", STR_PAD_LEFT);
                $attendantFullName = $attendantName . " " . $attendantSurname;
                if ($attendantId == null) {
                    $attendantFullName = "Není k dispozici";
                }
                $parentFullName = $parentName . " " . $parentSurname;
                if ($parentId == null) {
                    $parentFullName = "Není k dispozici";
                    $parentEmail = "Není k dispozici";
                }
                $registered = new DateTime($registered)->format(STANDARD_CZECH_TIME_FORMAT_FULL);
                $paid = new DateTime($paid)->format(STANDARD_CZECH_TIME_FORMAT_FULL);
                $unregistered = new DateTime($unregistered)->format(STANDARD_CZECH_TIME_FORMAT_FULL);

                //Highlight
                //$highlightSchoolClass = "";
                //if (isset($_GET["school"]) && $_GET["school"] == $schoolId) {
                //    $highlightSchoolClass = "trHighlight";
                //}
        
                //Put in table
                echo "<tr class='clickHighlightRow'>
                        <td class='formButtonBoxTable'>
                            <button class='formButton formOkColor btnRefundTable' variableSymbol='$variableSymbol' bankAccount='$bankAccount' price='$eventPrice'>Vrátit platbu</button>
                        </td>
                        <td class='fontMono'>$variableSymbolFormated</td>
                        <td>$eventPrice Kč</td>
                        <td>$registered</td>
                        <td>$paid</td>
                        <td>$unregistered</td>
                        <td>$attendantFullName</td>
                        <td>$parentFullName</td>
                        <td><a href='mailto:$parentEmail'>$parentEmail</td>
                        <td>$reason</td>
                    </tr>";
            }
            echo "</table>";
        }
        ////Get highlighted schools
        //$highlightSchools = [];
        //if(isset($_GET['schools'])) {
        //    $highlightSchools = explode(',',$_GET["schools"]);
        //}
        
        //Request waiting for refund attendants without payment
        $stmt = $conn->prepare("SELECT ua.variable_symbol, ua.bank_account, ua.registered, ua.unregistered, ua.reason, ua.id_attendants, a.name, a.surname, a.id_parent, u.name, u.surname,u.email, e.price FROM unregistered_attendants_teamPropaganda ua LEFT JOIN attendants_teamPropaganda a ON ua.id_attendants = a.id_attendants LEFT JOIN users_teamPropaganda u ON a.id_parent = u.id_users LEFT JOIN events_teamPropaganda e ON ua.id_events = e.id_events WHERE ua.id_events = ? AND ua.refunded IS NULL AND ua.paid IS NULL;");
        $stmt->bind_param("i", $_COOKIE["adminEventId"]);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $found = true;
            echo "<h1>Zájemci čekající na kontolu doručení peněz - v době odhlášení měli zájemci nezaplaceno</h1><i>Pladba může putovat několik dní, takže se doporučuje počkat nějakou dobu, než provedete rozhodnutí.</i>
                  <table class='styledTable styledTableAuto'>
                      <tr>
                          <th>Akce</th>
                          <th>Variabilní symbol platby</th>
                          <th>Částka</th>
                          <th>Datum registrace</th>
                          <th>Datum odhlášení</th>
                          <th>Jméno a přijmení</th>
                          <th>Zákonný zástupce</th>
                          <th>Email zákonného zástupce</th>
                          <th>Důvod odhlášení</th>
                      </tr>";

            //List all attendants in table
            for ($i = 0; $i < $stmt->num_rows; $i++) {
                $stmt->bind_result($variableSymbol, $bankAccount, $registered, $unregistered, $reason, $attendantId, $attendantName, $attendantSurname, $parentId, $parentName, $parentSurname, $parentEmail, $eventPrice);
                $stmt->fetch();
                $variableSymbolFormated = str_pad($variableSymbol, 10, "0", STR_PAD_LEFT);
                $attendantFullName = $attendantName . " " . $attendantSurname;
                if ($attendantId == null) {
                    $attendantFullName = "Není k dispozici";
                }
                $parentFullName = $parentName . " " . $parentSurname;
                if ($parentId == null) {
                    $parentFullName = "Není k dispozici";
                    $parentEmail = "Není k dispozici";
                }
                $registered = new DateTime($registered)->format(STANDARD_CZECH_TIME_FORMAT_FULL);
                $unregistered = new DateTime($unregistered)->format(STANDARD_CZECH_TIME_FORMAT_FULL);

                //Highlight
                //$highlightSchoolClass = "";
                //if (isset($_GET["school"]) && $_GET["school"] == $schoolId) {
                //    $highlightSchoolClass = "trHighlight";
                //}
        
                //Put in table
                echo "<tr class='clickHighlightRow'>
                        <td class='formButtonBoxTable'>
                            <button class='formButton formOkColor btnTableAddPayment' variableSymbol='$variableSymbol' unregistered=1>Platba dorazila</button>
                            <button class='formButton formErrorColor btnRemoveNotPaidTable' variableSymbol='$variableSymbol'>Platba nedorazila</button>
                        </td>
                        <td class='fontMono'>$variableSymbolFormated</td>
                        <td>$eventPrice Kč</td>
                        <td>$registered</td>
                        <td>$unregistered</td>
                        <td>$attendantFullName</td>
                        <td>$parentFullName</td>
                        <td><a href='mailto:$parentEmail'>$parentEmail</td>
                        <td>$reason</td>
                    </tr>";
            }
            echo "</table>";
        }
        
        //Request not paid attendants
        $stmt = $conn->prepare("SELECT ra.registered, ra.variable_symbol,ra.id_attendants, a.name, a.surname, a.id_parent, u.name,u.surname,u.email FROM registered_attendants_teamPropaganda AS ra JOIN attendants_teamPropaganda AS a ON ra.id_attendants = a.id_attendants JOIN users_teamPropaganda AS u ON a.id_parent = u.id_users WHERE ra.paid IS NULL AND ra.id_events = ?;");
        $stmt->bind_param("i", $_COOKIE["adminEventId"]);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $found = true;
            //Echo header
            echo "<h1>Zájemci čekající na zaplacení</h1>
                  <table class='styledTable styledTableAuto'>
                  <tr>
                      <th>Akce</th>
                      <th>Variabilní symbol platby</th>
                      <th>Jméno a přijmení</th>
                      <th>Zákonný zástupce</th>
                      <th>Email zákonného zástupce</th>
                      <th>Datum registrace</th>
                  </tr>";

            //List all attendants in table
            for ($i = 0; $i < $stmt->num_rows; $i++) {
                $stmt->bind_result($registered, $variableSymbol, $attendantId, $attendantName, $attendantSurname, $parentId, $parentName, $parentSurname, $parentEmail);
                $stmt->fetch();
                $variableSymbolFormated = str_pad($variableSymbol, 10, "0", STR_PAD_LEFT);
                $registered = new DateTime($registered)->format(STANDARD_CZECH_TIME_FORMAT_FULL);

                //Put in table
                echo "<tr class='clickHighlightRow'>
                        <td class='formButtonBoxTable'>
                            <button variableSymbol=$variableSymbol class='formButton formOkColor btnTableAddPayment'>Zaplatit</button></a>";
                if ($result->role == "admin") {
                    echo "  <a href='./attendant.php?attendant=$attendantId'><button class='formButton formWarnColor'>Upravit</button></a>
                            <button class='formButton formErrorColor btnUnregisterTable' variableSymbol=$variableSymbol>Odhlásit</button>";
                }
                echo "  </td>
                        <td class='fontMono'>$variableSymbolFormated</td>
                        <td>$attendantName $attendantSurname</td>
                        <td>$parentName $parentSurname</td>
                        <td><a href='mailto:$parentEmail'>$parentEmail</td>
                        <td>$registered</td>
                    </tr>";
            }
            echo "</table>";
        }

        //Request rejected attendants
        if ($result->role == "admin") {
            $stmt = $conn->prepare("SELECT ua.variable_symbol, ua.bank_account,ua.id_attendants, ua.refunded, a.name, a.surname, a.id_parent, u.name, u.surname,u.email, e.price FROM unregistered_attendants_teamPropaganda ua LEFT JOIN attendants_teamPropaganda a ON ua.id_attendants = a.id_attendants LEFT JOIN users_teamPropaganda u ON a.id_parent = u.id_users LEFT JOIN events_teamPropaganda e ON ua.id_events = e.id_events WHERE ua.id_events = ? AND ua.refunded IS NOT NULL;");
            $stmt->bind_param("i", $_COOKIE["adminEventId"]);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $found = true;
                echo "<h1>Zájemci, kterým byly vráceny peníze</h1>
                  <table class='styledTable styledTableAuto'>
                      <tr>
                          <th>Datum vrácení</th>
                          <th>Variabilní symbol platby</th>
                          <th>Částka</th>
                          <th>Jméno a přijmení</th>
                          <th>Zákonný zástupce</th>
                          <th>Email zákonného zástupce</th>
                      </tr>";

                //List all attendants in table
                for ($i = 0; $i < $stmt->num_rows; $i++) {
                    $stmt->bind_result($variableSymbol, $bankAccount, $attendantId, $refunded, $attendantName, $attendantSurname, $parentId, $parentName, $parentSurname, $parentEmail, $eventPrice);
                    $stmt->fetch();
                    $variableSymbolFormated = str_pad($variableSymbol, 10, "0", STR_PAD_LEFT);
                    $attendantFullName = $attendantName . " " . $attendantSurname;
                    if ($attendantId == null) {
                        $attendantFullName = "Není k dispozici";
                    }
                    $parentFullName = $parentName . " " . $parentSurname;
                    if ($parentId == null) {
                        $parentFullName = "Není k dispozici";
                        $parentEmail = "Není k dispozici";
                    }
                    $refunded = new DateTime($refunded)->format(STANDARD_CZECH_TIME_FORMAT_FULL);

                    //Highlight
                    //$highlightSchoolClass = "";
                    //if (isset($_GET["school"]) && $_GET["school"] == $schoolId) {
                    //    $highlightSchoolClass = "trHighlight";
                    //}
        
                    //Put in table
                    echo "<tr class='clickHighlightRow'>
                        <td>$refunded</td>
                        <td class='fontMono'>$variableSymbolFormated</td>
                        <td>$eventPrice Kč</td>
                        <td>$attendantFullName</td>
                        <td>$parentFullName</td>
                        <td><a href='mailto:$parentEmail'>$parentEmail</td>
                    </tr>";
                }
                echo "</table>";
            }
        }

        //Request paid attendants
        if ($result->role == "admin") {
            $stmt = $conn->prepare("SELECT ra.paid, ra.variable_symbol, a.name, a.surname, u.name,u.surname,u.email FROM registered_attendants_teamPropaganda AS ra JOIN attendants_teamPropaganda AS a ON ra.id_attendants = a.id_attendants JOIN users_teamPropaganda AS u ON a.id_parent = u.id_users WHERE ra.paid IS NOT NULL AND ra.id_events = ?;");
            $stmt->bind_param("i", $_COOKIE["adminEventId"]);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $found = true;
                //Echo header
                echo "<h1>Zaplacení zájemci</h1>
                  <table class='styledTable styledTableAuto'>
                      <tr>
                          <th>Datum platby</th>
                          <th>Variabilní symbol platby</th>
                          <th>Jméno a přijmení</th>
                          <th>Zákonný zástupce</th>
                          <th>Email zákonného zástupce</th>
                      </tr>";

                //List all attendants in table
                for ($i = 0; $i < $stmt->num_rows; $i++) {
                    $stmt->bind_result($paid, $variableSymbol, $attendantName, $attendantSurname, $parentName, $parentSurname, $parentEmail);
                    $stmt->fetch();
                    $variableSymbolFormated = str_pad($variableSymbol, 10, "0", STR_PAD_LEFT);
                    $registered = new DateTime($registered)->format(STANDARD_CZECH_TIME_FORMAT_FULL);
                    $paid = new DateTime($paid)->format(STANDARD_CZECH_TIME_FORMAT_FULL);

                    //Highlight
                    $highlightSchoolClass = "";
                    if (isset($_GET["school"]) && $_GET["school"] == $schoolId) {
                        $highlightSchoolClass = "trHighlight";
                    }

                    //Put in table
                    echo "<tr class='clickHighlightRow $highlightSchoolClass'>
                        <td>$paid</td>
                        <td class='fontMono'>$variableSymbolFormated</td>
                        <td>$attendantName $attendantSurname</td>
                        <td>$parentName $parentSurname</td>
                        <td><a href='mailto:$parentEmail'>$parentEmail</td>
                    </tr>";
                }
                echo "</table>";
            }
        }

        if (!$found) {
            echo "<h1>Žádné platby nejsou k dispozici.</h1>";
        }
        ?>
    </main>
    <footer>

    </footer>
</body>
<script type="module" src="../formWebScripts/js/formScript.js"></script>
<script type='module' src='./sharedScripts.js'></script>
<script type='module' src='./payments.js'></script>

</html>
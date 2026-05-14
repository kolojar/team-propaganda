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
    <title>Zájemci</title>
    <link rel="stylesheet" href="../formWebScripts/css/sharedStyle.css">
    <link rel="stylesheet" href="../formWebScripts/css/tableStyle.css">
    <link rel="stylesheet" href="../formWebScripts/css/formStyle.css">
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body class="pageHolder">
    <header>
        <?php $result = setupTitlebar($conn, "attendants.php") ?>
    </header>
    <main>
        <?php
        ////Get highlighted schools
        //$highlightSchools = [];
        //if(isset($_GET['schools'])) {
        //    $highlightSchools = explode(',',$_GET["schools"]);
        //}
        
        $found = false;
        //Request paid attendants
        if ($result->role == "admin") {
            $stmt = $conn->prepare("SELECT ra.registered, ra.paid, ra.id_attendants, a.name, a.surname, a.id_parent, u.name,u.surname,u.email,a.id_schools, s.name,s.address, ra.id_classrooms,c.name FROM registered_attendants AS ra JOIN attendants AS a ON ra.id_attendants = a.id_attendants JOIN users AS u ON a.id_parent = u.id_users JOIN schools AS s ON a.id_schools = s.id_schools JOIN classrooms AS c ON ra.id_classrooms = c.id_classrooms WHERE ra.paid IS NOT NULL AND ra.id_events = ?;");
            $stmt->bind_param("i", $_COOKIE["adminEventId"]);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $found = true;
                //Echo header
                echo "<h1>Registrovaní a zaplacení zájemci</h1>
                  <table class='styledTable styledTableAuto'>
                      <tr>
                          <th>Akce</th>
                          <th>Jméno a přijmení</th>
                          <th>Zákonný zástupce</th>
                          <th>Email zákonného zástupce</th>
                          <th>Učebna</th>
                          <th>Základní škola</th>
                          <th>Datum registrace</th>
                          <th>Datum platby</th>
                      </tr>";

                //List all attendants in table
                for ($i = 0; $i < $stmt->num_rows; $i++) {
                    $stmt->bind_result($registered, $paid, $attendantId, $attendantName, $attendantSurname, $parentId, $parentName, $parentSurname, $parentEmail, $schoolId, $schoolName, $schoolAddress, $classroomId, $classroomName);
                    $stmt->fetch();
                    $registered = new DateTime($registered)->format(STANDARD_CZECH_TIME_FORMAT_FULL);
                    $paid = new DateTime($paid)->format(STANDARD_CZECH_TIME_FORMAT_FULL);

                    //Highlight
                    $highlightSchoolClass = "";
                    if (isset($_GET["school"]) && $_GET["school"] == $schoolId) {
                        $highlightSchoolClass = "trHighlight";
                    }

                    //Put in table
                    echo "<tr class='clickHighlightRow $highlightSchoolClass'>
                        <td>
                            <a href='./attendant.php?attendant=$attendantId'><button class='formButton formWarnColor'>Upravit</button></a>
                            <button class='formButton formErrorColor btnUnregisterTable' variableSymbol=$variableSymbol>Odhlásit</button>
                        </td>
                        <td>$attendantName $attendantSurname</td>
                        <td>$parentName $parentSurname</td>
                        <td><a href='mailto:$parentEmail'>$parentEmail</td>
                        <td>$classroomName</td>
                        <td>$schoolName → $schoolAddress</td>
                        <td>$registered</td>
                        <td>$paid</td>
                    </tr>";
                }
                echo "</table>";
            }
        }
        ////Get highlighted schools
        //$highlightSchools = [];
        //if(isset($_GET['schools'])) {
        //    $highlightSchools = explode(',',$_GET["schools"]);
        //}
        
        //Request not paid attendants
        $stmt = $conn->prepare("SELECT ra.registered,ra.variable_symbol, ra.id_attendants, a.name, a.surname, a.id_parent, u.name,u.surname,u.email,a.id_schools, s.name,s.address, ra.id_classrooms,c.name FROM registered_attendants AS ra JOIN attendants AS a ON ra.id_attendants = a.id_attendants JOIN users AS u ON a.id_parent = u.id_users JOIN schools AS s ON a.id_schools = s.id_schools JOIN classrooms AS c ON ra.id_classrooms = c.id_classrooms WHERE ra.paid IS NULL AND ra.id_events = ?;");
        $stmt->bind_param("i", $_COOKIE["adminEventId"]);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $found = true;
            //Echo header
            echo "<h1>Registrovaní a nezaplacení zájemci</h1>
                  <table class='styledTable styledTableAuto'>
                  <tr>
                      <th>Akce</th>
                      <th>Jméno a přijmení</th>
                      <th>Zákonný zástupce</th>
                      <th>Email zákonného zástupce</th>
                      <th>Učebna</th>
                      <th>Základní škola</th>
                      <th>Datum registrace</th>
                  </tr>";

            //List all attendants in table
            for ($i = 0; $i < $stmt->num_rows; $i++) {
                $stmt->bind_result($registered, $variableSymbol, $attendantId, $attendantName, $attendantSurname, $parentId, $parentName, $parentSurname, $parentEmail, $schoolId, $schoolName, $schoolAddress, $classroomId, $classroomName);
                $stmt->fetch();
                $variableSymbolFormated = str_pad($variableSymbol, 10, "0", STR_PAD_LEFT);
                $registered = new DateTime($registered)->format(STANDARD_CZECH_TIME_FORMAT_FULL);

                //Highlight
                $highlightSchoolClass = "";
                if (isset($_GET["school"]) && $_GET["school"] == $schoolId) {
                    $highlightSchoolClass = "trHighlight";
                }

                //Put in table
                echo "<tr class='clickHighlightRow $highlightSchoolClass'>
                        <td>
                            <a href='./attendant.php?attendant=$attendantId'><button class='formButton formWarnColor'>Upravit</button></a>
                            <button class='formButton formErrorColor btnUnregisterTable' variableSymbol=$variableSymbol>Odhlásit</button>
                        </td>
                        <td>$attendantName $attendantSurname</td>
                        <td>$parentName $parentSurname</td>
                        <td><a href='mailto:$parentEmail'>$parentEmail</td>
                        <td>$classroomName</td>
                        <td>$schoolName → $schoolAddress</td>
                        <td>$registered</td>
                    </tr>";
            }
            echo "</table>";
        }

        //Request rejected attendants
        if ($result->role == "admin") {
            $stmt = $conn->prepare("SELECT ua.variable_symbol, ua.bank_account, ua.registered,ua.paid, ua.unregistered, ua.reason, ua.id_attendants, ua.refunded, a.name, a.surname, a.id_parent, u.name, u.surname,u.email, e.price FROM unregistered_attendants ua LEFT JOIN attendants a ON ua.id_attendants = a.id_attendants LEFT JOIN users u ON a.id_parent = u.id_users LEFT JOIN events e ON ua.id_events = e.id_events WHERE ua.id_events = ?");
            $stmt->bind_param("i", $_COOKIE["adminEventId"]);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $found = true;
                echo "<h1>Odhlášení zájemci</h1>
                  <table class='styledTable styledTableAuto'>
                      <tr>
                          <th>Akce</th>
                          <th>Jméno a přijmení</th>
                          <th>Zákonný zástupce</th>
                          <th>Email zákonného zástupce</th>
                          <th>Důvod odhlášení</th>
                          <th>Datum registrace</th>
                          <th>Datum zaplacení</th>
                          <th>Datum odhlášení</th>
                          <th>Datum vrácení</th>
                      </tr>";

                //List all attendants in table
                for ($i = 0; $i < $stmt->num_rows; $i++) {
                    $stmt->bind_result($variableSymbol, $bankAccount, $registered, $paid, $unregistered, $reason, $attendantId, $refunded, $attendantName, $attendantSurname, $parentId, $parentName, $parentSurname, $parentEmail, $eventPrice);
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
                    $refundedFormated = "Zatím nevráceno";
                    $disableDelete = "";
                    if ($refunded != null) {
                        $refundedFormated = new DateTime($refunded)->format(STANDARD_CZECH_TIME_FORMAT_FULL);
                    } else {
                        $disableDelete = "disabled";
                    }

                    //Highlight
                    //$highlightSchoolClass = "";
                    //if (isset($_GET["school"]) && $_GET["school"] == $schoolId) {
                    //    $highlightSchoolClass = "trHighlight";
                    //}
        
                    //Put in table
                    echo "<tr class='clickHighlightRow'>
                        <td>
                            <button class='formButton formErrorColor btnDeleteTotalTable' $disableDelete variableSymbol='$variableSymbol'>Odstranit</button>
                        </td>
                        <td>$attendantFullName</td>
                        <td>$parentFullName</td>
                        <td><a href='mailto:$parentEmail'>$parentEmail</td>
                        <td>$reason</td>
                        <td>$registered</td>
                        <td>$paid</td>
                        <td>$unregistered</td>
                        <td>$refundedFormated</td>
                    </tr>";
                }
                echo "</table>";
            }
        }

        if (!$found) {
            echo "<h1>Žádní zájemci nejsou k dispozici.</h1>";
        }
        ?>
    </main>
    <footer>

    </footer>
</body>
<script type="module" src="../formWebScripts/js/formScript.js"></script>
<script type='module' src='./sharedScripts.js'></script>
<script type='module' src='./attendants.js'></script>

</html>
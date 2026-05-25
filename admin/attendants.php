<?php
session_start();
require "../assets/config.php";
require "./adminFunctions.php";
?>

<!DOCTYPE html>
<html lang="cs">

<head>
    <meta charset="UTF-8">
    <meta name="form-icons-main-db" content="../formWebScripts/formIcons.json">
    <meta name="form-icons-db" content="../assets/formIcons.json">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zájemci</title>

    <link rel="stylesheet" href="../formWebScripts/css/formStyle.css">
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body class="pageHolder">
    <header>
        <?php $result = setupTitlebarAdmin($conn, "attendants.php"); ?>
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
        if ($result->roleType->role == userRole::ADMIN) {
            $resultEventId = $result->eventId;
            $resultSubeventId = $result->subeventId;
            $stmt = $conn->prepare("SELECT ra.registered, ra.paid, ra.id_attendants, a.name, a.surname, a.id_parent, u.name,u.surname,u.email,a.id_schools, s.name,s.address, ap.id_classrooms,c.name FROM registered_attendants_teamPropaganda AS ra JOIN attendants_teamPropaganda AS a ON ra.id_attendants = a.id_attendants JOIN users_teamPropaganda AS u ON a.id_parent = u.id_users JOIN schools_teamPropaganda AS s ON a.id_schools = s.id_schools LEFT JOIN attendants_presence_teamPropaganda ap ON ap.variable_symbol = ra.variable_symbol AND ap.id_subevents = ? LEFT JOIN classrooms_teamPropaganda AS c ON ap.id_classrooms = c.id_classrooms WHERE ra.paid IS NOT NULL AND ra.id_events = ?;");
            if (!$stmt->bind_param("ii", $resultSubeventId, $resultEventId) || !$stmt->execute() || !$stmt->store_result()) {
                echo "<h1>Nelze získat informace o registrovaných a zaplacených zájemcích.</h1>";
                $stmt->close();
            } else {
                if ($stmt->num_rows > 0) {
                    $found = true;
                    //Echo header
                    echo "<h1>Registrovaní a zaplacení zájemci: " . $stmt->num_rows . "</h1>
                  <table>
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
                        if (!$stmt->bind_result($registered, $paid, $attendantId, $attendantName, $attendantSurname, $parentId, $parentName, $parentSurname, $parentEmail, $schoolId, $schoolName, $schoolAddress, $classroomId, $classroomName) || !$stmt->fetch()) {
                            $attendantName = "CHYBA";
                            $attendantSurname = "CHYBA";
                            $parentName = "CHYBA";
                            $parentSurname = "CHYBA";
                            $parentEmail = "CHYBA";
                            $parentId = "NULL";
                            $schoolName = "CHYBA";
                            $schoolAddress = "CHYBA";
                            $registered = "CHYBA";
                            $paid = "CHYBA";
                            $attendantId = "NULL";
                            $schoolId = "NULL";
                            $classroomId = "NULL";
                            $classroomName = "CHYBA";
                        } else {
                            $registered = new DateTime($registered)->format(STANDARD_CZECH_DATETIME_FORMAT_FULL);
                            $paid = new DateTime($paid)->format(STANDARD_CZECH_DATETIME_FORMAT_FULL);
                        }

                        //Classroom name field
                        $classroomNameText = $classroomName;
                        if ($result->subeventId == null) {
                            $classroomNameText = "<a href='./events.php?noSubeventId=1'>Vyberte podudálost</a>";
                        } else if ($classroomId == null) {
                            $classroomNameText = "<a href='./subevent.php?subevent=$result->subeventId'>Zařaďte žáka automaticky do učebny</a>";
                        }

                        //Highlight
                        $highlightSchoolClass = "";
                        if (isset($_GET["school"]) && $_GET["school"] == $schoolId) {
                            $highlightSchoolClass = "trHighlight";
                        }

                        //Put in table
                        echo "<tr class='clickHighlightRow $highlightSchoolClass'>
                        <td class='formButtonBoxTable'>
                            <a href='./attendant.php?attendant=$attendantId'><button class='formButton formButtonInline purkynkaButton' form-icon='!edit'></button></a><button class='formButton btnUnregisterTable formButtonInline purkynkaButton' variableSymbol='$variableSymbol' form-icon='!removePerson'></button>
                        </td>
                        <td>$attendantName $attendantSurname</td>
                        <td>$parentName $parentSurname</td>
                        <td> <a href='./sendMail.php?uid=$parentId&isNILE=0'>$parentEmail</td>
                        <td>$classroomNameText</td>
                        <td>$schoolName → $schoolAddress</td>
                        <td>$registered</td>
                        <td>$paid</td>
                    </tr>";
                    }
                    echo "</table>";
                    $stmt->close();
                }
            }
        }
        ////Get highlighted schools
        //$highlightSchools = [];
        //if(isset($_GET['schools'])) {
        //    $highlightSchools = explode(',',$_GET["schools"]);
        //}
        
        //Request not paid attendants
        $stmt = $conn->prepare("SELECT ra.registered, ra.paid, ra.id_attendants, a.name, a.surname, a.id_parent, u.name,u.surname,u.email,a.id_schools, s.name,s.address, ap.id_classrooms,c.name FROM registered_attendants_teamPropaganda AS ra JOIN attendants_teamPropaganda AS a ON ra.id_attendants = a.id_attendants JOIN users_teamPropaganda AS u ON a.id_parent = u.id_users JOIN schools_teamPropaganda AS s ON a.id_schools = s.id_schools LEFT JOIN attendants_presence_teamPropaganda ap ON ap.variable_symbol = ra.variable_symbol AND ap.id_subevents = ? LEFT JOIN classrooms_teamPropaganda AS c ON ap.id_classrooms = c.id_classrooms WHERE ra.paid IS NULL AND ra.id_events = ?;");
        if (!$stmt->bind_param("ii", $resultSubeventId, $resultEventId) || !$stmt->execute() || !$stmt->store_result()) {
            echo "<h1>Nelze získat informace o registrovaných a nezaplacených zájemcích.</h1>";
            $stmt->free_result();
        } else {
            if ($stmt->num_rows > 0) {
                $found = true;
                //Echo header
                echo "<h1>Registrovaní a nezaplacení zájemci: " . $stmt->num_rows . "</h1>
                  <table>
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
                    if (!$stmt->bind_result($registered, $variableSymbol, $attendantId, $attendantName, $attendantSurname, $parentId, $parentName, $parentSurname, $parentEmail, $schoolId, $schoolName, $schoolAddress, $classroomId, $classroomName) || !$stmt->fetch()) {
                        $registered = "CHYBA";
                        $variableSymbol = null;
                        $attendantId = null;
                        $attendantName = "CHYBA";
                        $attendantSurname = "CHYBA";
                        $parentId = null;
                        $parentName = "CHYBA";
                        $parentSurname = "CHYBA";
                        $parentEmail = "CHYBA";
                        $schoolId = null;
                        $schoolName = "CHYBA";
                        $schoolAddress = "CHYBA";
                        $classroomId = null;
                        $classroomName = "CHYBA";
                        $variableSymbolFormated = "CHYBA";
                    } else {
                        $variableSymbolFormated = str_pad($variableSymbol, 10, "0", STR_PAD_LEFT);
                        $registered = new DateTime($registered)->format(STANDARD_CZECH_DATETIME_FORMAT_FULL);
                    }

                    //Classroom name field
                    $classroomNameText = $classroomName;
                    if ($result->subeventId == null) {
                        $classroomNameText = "<a href='./events.php?noSubeventId=1'>Vyberte podudálost</a>";
                    } else if ($classroomId == null) {
                        $classroomNameText = "<a href='./payments.php?variableSymbol=$variableSymbol'>Čeká na platbu</a>";
                    }

                    //Highlight
                    $highlightSchoolClass = "";
                    if (isset($_GET["school"]) && $_GET["school"] == $schoolId) {
                        $highlightSchoolClass = "trHighlight";
                    }

                    //Put in table
                    echo "<tr class='clickHighlightRow $highlightSchoolClass'>
                        <td class='formButtonBoxTable'>
                            <a href='./attendant.php?attendant=$attendantId'><button class='formButton formButtonInline purkynkaButton' form-icon='!edit'></button></a><button class='formButton formButtonInline purkynkaButton btnUnregisterTable' variableSymbol='$variableSymbol' form-icon='!removePerson'></button>
                        </td>
                        <td>$attendantName $attendantSurname</td>
                        <td>$parentName $parentSurname</td>
                        <td><a href='./sendMail.php?uid=$parentId&isNILE=0'>$parentEmail</td>
                        <td>$classroomNameText</td>
                        <td>$schoolName → $schoolAddress</td>
                        <td>$registered</td>
                    </tr>";
                }
                echo "</table>";
                $stmt->close();
            }
        }

        //Request rejected attendants
        if ($result->roleType->role == userRole::ADMIN) {
            $stmt = $conn->prepare("SELECT ua.variable_symbol, ua.bank_account, ua.registered,ua.paid, ua.unregistered, ua.reason, ua.id_attendants, ua.refunded, a.name, a.surname, a.id_parent, u.name, u.surname,u.email, e.price FROM unregistered_attendants_teamPropaganda ua LEFT JOIN attendants_teamPropaganda a ON ua.id_attendants = a.id_attendants LEFT JOIN users_teamPropaganda u ON a.id_parent = u.id_users LEFT JOIN events_teamPropaganda e ON ua.id_events = e.id_events WHERE ua.id_events = ?");
            if (!$stmt->bind_param("i", $_COOKIE["adminEventId"]) || !$stmt->execute() || !$stmt->store_result()) {
                echo "<h1>Nelze získat informace o odhlášených zájemcích.</h1>";
                $stmt->close();
            } else {
                if ($stmt->num_rows > 0) {
                    $found = true;
                    echo "<h1>Odhlášení zájemci</h1>
                  <table>
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
                        if (!$stmt->bind_result($variableSymbol, $bankAccount, $registered, $paid, $unregistered, $reason, $attendantId, $refunded, $attendantName, $attendantSurname, $parentId, $parentName, $parentSurname, $parentEmail, $eventPrice) || !$stmt->fetch()) {
                            $variableSymbol = null;
                            $bankAccount = "CHYBA";
                            $registered = "CHYBA";
                            $paid = null;
                            $unregistered = "CHYBA";
                            $reason = "CHYBA";
                            $attendantId = null;
                            $refunded = null;
                            $attendantName = "CHYBA";
                            $attendantSurname = "CHYBA";
                            $parentId = null;
                            $parentName = "CHYBA";
                            $parentSurname = "CHYBA";
                            $parentEmail = "CHYBA";
                            $eventPrice = "CHYBA";
                        } else {
                            $variableSymbolFormated = str_pad($variableSymbol, 10, "0", STR_PAD_LEFT);
                            $attendantFullName = $attendantName . " " . $attendantSurname;
                            $registered = new DateTime($registered)->format(STANDARD_CZECH_DATETIME_FORMAT_FULL);
                            $unregistered = new DateTime($unregistered)->format(STANDARD_CZECH_DATETIME_FORMAT_FULL);
                        }
                        if ($attendantId == null) {
                            $attendantFullName = "Není k dispozici";
                        }
                        $parentFullName = $parentName . " " . $parentSurname;
                        if ($parentId == null) {
                            $parentFullName = "Není k dispozici";
                            $parentEmail = "Není k dispozici";
                        }
                        if ($paid != null) {
                            $paid = new DateTime($paid)->format(STANDARD_CZECH_DATETIME_FORMAT_FULL);
                        } else {
                            $paid = "Nezaplaceno";
                        }
                        $refundedFormated = "Zatím nevráceno";
                        $disableDelete = "";
                        if ($refunded != null) {
                            $refundedFormated = new DateTime($refunded)->format(STANDARD_CZECH_DATETIME_FORMAT_FULL);
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
                        <td class='formButtonBoxTable'>
                            <button class='formButton formButtonInline purkynkaButton btnDeleteTotalTable' $disableDelete variableSymbol='$variableSymbol' form-icon='!delete'></button>
                        </td>
                        <td>$attendantFullName</td>
                        <td>$parentFullName</td>";
                        if ($parentId != null) {
                            echo "<td><a href='./sendMail.php?uid=$parentId&isNILE=0'>$parentEmail</td>";
                        } else {
                            echo "<td>$parentEmail</td>";
                        }
                        echo "<td>$reason</td>
                        <td>$registered</td>
                        <td>$paid</td>
                        <td>$unregistered</td>
                        <td>$refundedFormated</td>
                    </tr>";
                    }
                    echo "</table>";
                    $stmt->close();
                }
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
<script type='module' src='../assets/sharedScripts.js'></script>
<script type='module' src='./attendants.js'></script>

</html>
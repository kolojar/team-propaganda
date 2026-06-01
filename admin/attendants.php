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
    <meta name="form-locales-main" content="../formWebScripts/locales/">
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
        function attendantEmail($result, $setup)
        {
            $email = $result["email"];
            $uid = $result["id_parent"];
            return "<a href='./sendMail.php?uid=$uid&isNILE=0'>$email</a>";
        }
        function attendantClassroom($result, $setup)
        {
            if ($setup->subeventId == null) {
                return "<a href='./events.php?noSubeventId=1'>Vyberte podudálost</a>";
            } else if ($result["id_classrooms"] == null) {
                return "<a href='./subevent.php?subevent=$setup->subeventId'>Zařaďte žáka automaticky do učebny</a>";
            }
            return $result["cname"];
        }
        function attendantActionButtons($result, $setup)
        {
            $attendantId = $result["id_attendants"];
            $variableSymbol = $result["variable_symbol"];
            return "<a href='./attendant.php?attendant=$attendantId'><button class='formButton formButtonInline purkynkaButton' form-icon='!edit'></button></a><button class='formButton btnUnregisterTable formButtonInline purkynkaButton' variableSymbol='$variableSymbol' form-icon='!removePerson'></button>";
        }
        $resultEventId = $result->eventId;
        $resultSubeventId = $result->subeventId;

        //Get classrooms for subevent
        $stmt = $conn->prepare("SELECT cs.id_classrooms, c.name FROM classrooms_subevents_teamPropaganda cs JOIN classrooms_teamPropaganda c ON cs.id_classrooms = c.id_classrooms WHERE cs.id_subevents = ?;");
        if (!$stmt->bind_param("i", $resultSubeventId) || !$stmt->execute() || !$stmt->store_result()) {
            $stmt->close();
            echo "<h1>Nelze získat informace o učebnách.</h1>";
            die();
        }
        echo "<datalist id='classrooms'>";
        echo "<option label='Žádná' value='NULL'></option>";
        for ($i = 0; $i < $stmt->num_rows; $i++) {
            $stmt->bind_result($idClassroom, $classroomName);
            $stmt->fetch();
            echo "<option label='$classroomName' value='$idClassroom'></option>";
        }
        echo "</datalist>";

        //Echo registered attendants
        echo "<h1>Zájemci přihlášení na akci</h1>";
        setupFilteredTable(
            $conn,
            $result,
            "purkynkaTableStripped purkynkaTableFullLines",
            "ra.variable_symbol, ra.registered, ra.paid, (ra.paid IS NOT NULL) as hasPaid, ra.id_attendants, a.name, a.surname, a.id_parent, u.name,u.surname,u.email,a.id_schools, s.name,s.address, CONCAT(s.name, ' → ', s.address) as school, ap.id_classrooms,c.name AS cname, CONCAT(a.name, ' ', a.surname) AS aFullName, CONCAT(u.name, ' ', u.surname) AS uFullName",
            "registered_attendants_teamPropaganda AS ra JOIN attendants_teamPropaganda AS a ON ra.id_attendants = a.id_attendants JOIN users_teamPropaganda AS u ON a.id_parent = u.id_users JOIN schools_teamPropaganda AS s ON a.id_schools = s.id_schools LEFT JOIN attendants_presence_teamPropaganda ap ON ap.variable_symbol = ra.variable_symbol AND ap.id_subevents = ? LEFT JOIN classrooms_teamPropaganda AS c ON ap.id_classrooms = c.id_classrooms",
            "ra.id_events = ?",
            "",
            "",
            "",
            "ii",
            [$result->subeventId, $result->eventId],
            [
                new filterSelector("aFullName", "Jméno a přijmení", "aFullName", filterSelectorType::TEXT, filterCompareOperator::LIKE, true),
                new filterSelector("uFullName", "Zákonný zástupce", "uFullName", filterSelectorType::TEXT, filterCompareOperator::LIKE, true),
                new filterSelector("email", "Email zákonného zástupce", "email", filterSelectorType::TEXT, filterCompareOperator::LIKE),
                new filterSelector("ap.id_classrooms", "Učebna", "classroom", filterSelectorType::SELECTNUMERIC, filterCompareOperator::EQUALSNULLABLE, false, ["listId" => "classrooms"]),
                new filterSelector("hasPaid", "Zaplaceno", "hasPaid", filterSelectorType::BOOLEAN, filterCompareOperator::EQUALS, true),
                new filterSelector("ra.variable_symbol", "Variabilní symbol", "variableSymbol", filterSelectorType::NUMBER, filterCompareOperator::EQUALS),
                new filterSelector("school", "Základní škola", "school", filterSelectorType::TEXT, filterCompareOperator::LIKE, true, ["filterFieldId" => "school"]),
            ],
            [
                new filterDisplayer("!attendantActionButtons", "Akce", true),
                new filterDisplayer("aFullName", "Jméno a příjmení", true),
                new filterDisplayer("uFullName", "Zákonný zástupce", true),
                new filterDisplayer("!attendantEmail", "Email zákonného zástupce", true),
                new filterDisplayer("!attendantClassroom", "Učebna", true),
                new filterDisplayer("hasPaid", "Zaplaceno", true, filterSelectorType::BOOLEAN),
                new filterDisplayer("school", "Základní škola", true),
                new filterDisplayer("registered", "Datum registrace", false, filterSelectorType::DATETIME),
                new filterDisplayer("paid", "Datum platby", false, filterSelectorType::DATETIME),
                new filterDisplayer("variable_symbol", "Variabilní symbol", false),
            ]
        );

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
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
            if($email == "") {
                return "Není k dispozici";
            }
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

        function formatVariableSymbol($value) {
            return str_pad($value,10,"0",STR_PAD_LEFT);
        }
        function attendantActionButtons($result, $setup)
        {
            $attendantId = $result["id_attendants"];
            $variableSymbol = $result["id_registered_attendants"];
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
            "ra.id_registered_attendants, ra.registered, ra.paid, (ra.paid IS NOT NULL) as hasPaid, ra.id_attendants, a.name, a.surname, a.id_parent, u.name,u.surname,u.email,a.id_schools, s.name,s.address, CONCAT(s.name, ' → ', s.address) as school, ap.id_classrooms,c.name AS cname, CONCAT(a.name, ' ', a.surname) AS aFullName, CONCAT(u.name, ' ', u.surname) AS uFullName",
            "registered_attendants_teamPropaganda AS ra JOIN attendants_teamPropaganda AS a ON ra.id_attendants = a.id_attendants JOIN users_teamPropaganda AS u ON a.id_parent = u.id_users JOIN schools_teamPropaganda AS s ON a.id_schools = s.id_schools LEFT JOIN attendants_presence_teamPropaganda ap ON ap.id_registered_attendants = ra.id_registered_attendants AND ap.id_subevents = ? LEFT JOIN classrooms_teamPropaganda AS c ON ap.id_classrooms = c.id_classrooms",
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
                new filterSelector("ra.id_registered_attendants", "Variabilní symbol", "variableSymbol", filterSelectorType::NUMBER, filterCompareOperator::EQUALS),
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
                new filterDisplayer("id_registered_attendants", "Variabilní symbol", false,filterSelectorType::TEXT,"fontMono","formatVariableSymbol"),
            ]
        );
        ?>
    </main>
    <footer>
        <div class="formButtonBoxHolder">
        <a href="./unregisteredAttendants.php"><button class="purkynkaButton">Odhlášení zájemci</button></a>
        </div>
    </footer>
</body>
<script type="module" src="../formWebScripts/js/formScript.js"></script>
<script type='module' src='../assets/sharedScripts.js'></script>
<script type='module' src='./attendants.js'></script>

</html>
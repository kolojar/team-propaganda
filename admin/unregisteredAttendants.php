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
        function attendantActionButtons($result, $setup)
        {
            $disableDelete = $result["refunded"] == null ? "disabled" : "";
            $variableSymbol = $result["variable_symbol"];
            return "<button class='formButton formButtonInline purkynkaButton btnDeleteTotalTable' $disableDelete variableSymbol='$variableSymbol' form-icon='!delete'></button>";
        }
        $resultEventId = $result->eventId;
        $resultSubeventId = $result->subeventId;

        //Echo unregistered attendants
        echo "<h1>Odhlášení zájemci z akce</h1>";
        setupFilteredTable(
            $conn,
            $result,
            "purkynkaTableStripped purkynkaTableFullLines",
            "ua.variable_symbol, ua.bank_account, ua.registered,ua.paid, (ua.paid IS NOT NULL) as hasPaid, ua.unregistered, ua.reason, ua.id_attendants, ua.refunded, (ua.refunded IS NOT NULL) as hasReturned, a.name, a.surname, CONCAT(a.name, ' ', a.surname) AS aFullName, a.id_parent, u.name, u.surname, CONCAT(u.name, ' ', u.surname) AS uFullName, u.email, e.price",
            "unregistered_attendants_teamPropaganda ua LEFT JOIN attendants_teamPropaganda a ON ua.id_attendants = a.id_attendants LEFT JOIN users_teamPropaganda u ON a.id_parent = u.id_users LEFT JOIN events_teamPropaganda e ON ua.id_events = e.id_events",
            "ua.id_events = ?",
            "",
            "",
            "",
            "i",
            [$result->eventId],
            [
                new filterSelector("aFullName", "Jméno a přijmení", "aFullName", filterSelectorType::TEXT, filterCompareOperator::LIKE, true),
                new filterSelector("uFullName", "Zákonný zástupce", "uFullName", filterSelectorType::TEXT, filterCompareOperator::LIKE, true),
                new filterSelector("u.email", "Email zákonného zástupce", "email", filterSelectorType::TEXT, filterCompareOperator::LIKE),
                new filterSelector("hasPaid", "Zaplaceno", "hasPaid", filterSelectorType::BOOLEAN, filterCompareOperator::EQUALS, true),
                new filterSelector("hasReturned", "Vráceno", "hasReturned", filterSelectorType::BOOLEAN, filterCompareOperator::EQUALS, true),
                new filterSelector("ua.variable_symbol", "Variabilní symbol", "variableSymbol", filterSelectorType::NUMBER, filterCompareOperator::EQUALS),
            ],
            [
                new filterDisplayer("!attendantActionButtons", "Akce", true),
                new filterDisplayer("aFullName", "Jméno a příjmení", true),
                new filterDisplayer("uFullName", "Zákonný zástupce", true),
                new filterDisplayer("!attendantEmail", "Email zákonného zástupce", true),
                new filterDisplayer("variable_symbol", "Variabilní symbol", false,filterSelectorType::TEXT,"fontMono"),
                new filterDisplayer("hasPaid", "Zaplaceno", true, filterSelectorType::BOOLEAN),
                new filterDisplayer("hasReturned", "Vráceno", true, filterSelectorType::BOOLEAN),
                new filterDisplayer("registered", "Datum registrace", false, filterSelectorType::DATETIME),
                new filterDisplayer("paid", "Datum platby", false, filterSelectorType::DATETIME),
                new filterDisplayer("unregistered", "Datum odhlášení", false, filterSelectorType::DATETIME),
                new filterDisplayer("refunded", "Datum vrácení platby", false, filterSelectorType::DATETIME),
                new filterDisplayer("reason", "Důvod odhlášení", true),
            ]
        );
        ?>
    </main>
    <footer>
        <div class="formButtonBoxHolder">
        <a href="./attendants.php"><button class="purkynkaButton">Přihlášení zájemci</button></a>
        </div>
    </footer>
</body>
<script type="module" src="../formWebScripts/js/formScript.js"></script>
<script type='module' src='../assets/sharedScripts.js'></script>
<script type='module' src='./unregisteredAttendants.js'></script>

</html>
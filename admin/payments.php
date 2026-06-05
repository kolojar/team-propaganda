<?php
session_start();
require "../assets/config.php";
require "./adminFunctions.php";

if (isset($_POST["action"])) {
    if ($_POST["action"] == "addPayment") {
        //Check if values set
        if (!isset($_POST["paid"]) || !isset($_POST["bank_account"]) || !isset($_POST["id"]) || !isset($_POST["unregistered"]) || !isset($_POST["email"]) || !isset($_POST["id_events"])) {
            http_response_code(400);
            echo "Neplatné použití funkce - chybí parametr";
            die();
        }

        //Get SQL info
        $stmt = $conn->prepare("SELECT user_paid FROM registered_attendants_teamPropaganda WHERE id_registered_attendants = ?");
        if (!$stmt->bind_param("i", $_POST["id"]) || !$stmt->execute() || !$stmt->store_result() || !$stmt->bind_result($userPaid) || !$stmt->fetch() || !$stmt->close()) {
            http_response_code(400);
            echo "Nelze získat informace o zájemci.";
            die();
        }
        if ($userPaid == null) {
            $userPaid = $_POST["paid"];
        }

        //Make SQL Update
        $table = "registered_attendants_teamPropaganda";
        if ($_POST["unregistered"] == "1") {
            $table = "unregistered_attendants_teamPropaganda";
        }
        $stmt = $conn->prepare("UPDATE " . $table . " SET paid=?,user_paid=?,bank_account=? WHERE id_registered_attendants=?;");
        if ($stmt->bind_param("sssi", $_POST["paid"], $userPaid, $_POST["bank_account"], $_POST["id"]) && $stmt->execute() && $stmt->close()) {
            logToConsole("SELECT price FROM events_teamPropaganda WHERE id_events = " . $_POST["id_events"]);
            $res = $conn->query("SELECT price FROM events_teamPropaganda WHERE id_events = " . $_POST["id_events"])->fetch_assoc();
            $message = file_get_contents("../assets/PaymentOk.html");
            $message = str_replace("\${id_registered_attendants}", str_pad($_POST["id"], 10, "0", STR_PAD_LEFT), $message);
            $date = new DateTime($_POST["paid"]);
            $d = $date->format('d. m. Y H:i:s');
            $message = str_replace("\${payment_date}", $d, $message);
            $message = str_replace("\${amount}", $res["price"], $message);

            echo "\n\n$message\n\n";
            sendMail($_POST["email"], "Platba potvrzena.", $message);
            http_response_code(201);
            echo "Platba přidána.";
            die();
        } else {
            http_response_code(400);
            echo "Platba nemohla být přidána.";
            die();
        }
    } else if ($_POST["action"] == "removePayment") {
        //Check if values set
        if (!isset($_POST["id"])) {
            http_response_code(400);
            echo "Neplatné použití funkce - chybí parametr";
            die();
        }

        //Make SQL Update
        $stmt = $conn->prepare("UPDATE unregistered_attendants_teamPropaganda SET refunded = CURRENT_TIMESTAMP() WHERE id_registered_attendants = ?;");
        if ($stmt->bind_param("i", $_POST["id"]) && $stmt->execute() && $stmt->close()) {
            http_response_code(201);
            echo "Platba odebrána.";
            die();
        } else {
            http_response_code(400);
            echo "Platba nemohla být odebrána.";
            die();
        }
    } else if ($_POST["action"] == "unregister") {
        //Check if values set
        if (!isset($_POST["id"])) {
            http_response_code(400);
            echo "Neplatné použití funkce - chybí parametr";
            die();
        }

        //Get SQL info
        $stmt = $conn->prepare("SELECT id_attendants, id_events, bank_account, registered, paid, user_paid FROM registered_attendants_teamPropaganda WHERE id_registered_attendants = ?");
        if (!$stmt->bind_param("i", $_POST["id"]) || !$stmt->execute() || !$stmt->store_result() || !$stmt->bind_result($attendantId, $eventId, $bankAccount, $registered, $paid, $userPaid) || !$stmt->fetch() || !$stmt->close()) {
            http_response_code(400);
            echo "Nelze získat informace o zájemci.";
            die();
        }

        //Insert SQL entry
        $stmt = $conn->prepare("INSERT INTO unregistered_attendants_teamPropaganda(id_registered_attendants, id_attendants, id_events, bank_account, registered, paid, reason,user_paid) VALUES (?,?,?,?,?,?,?,?)");
        if (!$stmt->bind_param("iiissss", $_POST["id"], $attendantId, $eventId, $bankAccount, $registered, $paid, $_POST["reason"], $userPaid) || !$stmt->execute() || !$stmt->close()) {
            http_response_code(400);
            echo "Nelze vložit informace o odhlášení zájemce.";
            die();
        }

        //Delete SQL entry
        $stmt = $conn->prepare("DELETE FROM registered_attendants_teamPropaganda WHERE id_registered_attendants = ?");
        if (!$stmt->bind_param("i", $_POST["id"]) || !$stmt->execute() || !$stmt->close()) {
            http_response_code(400);
            echo "Nelze odebrat přihlášeného zájemce.";
            die();
        } else {
            http_response_code(201);
            echo "Zájemce odhlášen";
            die();
        }
    } else {
        http_response_code(400);
        echo "Neplatné použití funkce - neplatná akce";
        die();
    }
}
?>

<!DOCTYPE html>
<html lang="cs">

<head>
    <meta charset="UTF-8">
    <meta name="form-icons-main-db" content="../formWebScripts/formIcons.json">
    <meta name="form-icons-db" content="../assets/formIcons.json">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="form-locales-main" content="../formWebScripts/locales/">
    <title>Platby</title>
    <link rel="stylesheet" href="../formWebScripts/css/formStyle.css">
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body class="pageHolder">
    <header>
        <?php $result = setupTitlebarAdmin($conn, "payments.php") ?>
    </header>
    <main>
        <h1>Čekající platby na schválení</h1>
        <i>Doporučení: Při kontrole plateb se doporučuje počkat několik dní. Některým bankám trvají převody delší
            dobu.</i> <br>
        <i>Tip: Pro filtrování plateb na určitou událost otevřte pohled pomocí správy událostí.</i>
        <?php
        ////Get highlighted schools
        //$highlightSchools = [];
        //if(isset($_GET['schools'])) {
        //    $highlightSchools = explode(',',$_GET["schools"]);
        //}
        
        //Get events
        $stmt = $conn->prepare("SELECT id_events, name FROM events_teamPropaganda");
        if (!$stmt->execute() || !$stmt->store_result()) {
            $stmt->close();
            echo "<h1>Nelze získat informace o učebnách.</h1>";
            die();
        }
        echo "<datalist id='events'>";
        echo "<option label='Žádná' value='NULL'></option>";
        for ($i = 0; $i < $stmt->num_rows; $i++) {
            $stmt->bind_result($idEvents, $eventName);
            $stmt->fetch();
            echo "<option label='$eventName' value='$idEvents'></option>";
        }
        echo "</datalist>";

        $found = false;
        $resultEventId = $result->eventId;

        //Request event info
        if ($resultEventId != null) {
            $stmt = $conn->prepare("SELECT price FROM events_teamPropaganda WHERE id_events=?");
            if (!$stmt->bind_param("i", $resultEventId) || !$stmt->execute() || !$stmt->bind_result($price) || !$stmt->fetch() || !$stmt->close()) {
                $stmt->close();
                echo "<h1>Nelze získat cenu události.</h1>";
                echo "<a href='./admin.php'><button class='purkynkaButton'>Zpět na hlavní stránku</button></a>";
                die();
            }
            if ($price <= 0) {
                echo "<h1>Tato událost je zdarma, nebudou tedy žádné platby.</h1>";
                echo "<a href='./admin.php'><button class='purkynkaButton'>Zpět na hlavní stránku</button></a>";
                die();
            }
        }

        function attendantEmail($result, $setup)
        {
            $email = $result["email"];
            $uid = $result["id_parent"];
            if ($email == "") {
                return "Není k dispozici";
            }
            return "<a href='./sendMail.php?uid=$uid&isNILE=0'>$email</a>";
        }

        function action($result, $setup)
        {
            $variableSymbol = $result['vs'];
            $attendantId = $result['id_attendants'];
            if ($result['paid'] === null) {
                $btn = "<button class='purkynkaButton btnTableAddPayment' variableSymbol='$variableSymbol' form-icon='!addPayment'></button>";
                if($setup->roleType->role == userRole::ADMIN) {
                    $btn .= "<a href='./attendant.php?attendant=$attendantId'><button form-icon='!edit' class='purkynkaButton'></button></a><button class='purkynkaButton btnUnregisterTable' variableSymbol='$variableSymbol' form-icon='!removePerson'></button>";
                }
                return $btn;
            } else {
                return "Již zaplaceno";
            }
        }

        function formatVariableSymbol($value) {
            return str_pad($value,10,"0",STR_PAD_LEFT);
        }

        //Request waiting for refund attendats and paid attendants
        setupFilteredTable(
            $conn,
            $result,
            "purkynkaTableStripped purkynkaTableFullLines",
            "ra.id_events, ra.registered, ra.id_registered_attendants as vs, ra.id_attendants, a.name, a.surname, a.id_parent, u.name, u.surname, u.email, CONCAT(a.name, ' ', a.surname) as aName, CONCAT(u.name, ' ', u.surname) as uName, (ra.paid IS NOT NULL) as hasPaid, (ra.user_paid IS NOT NULL) as hasPaidUser",
            "registered_attendants_teamPropaganda AS ra JOIN attendants_teamPropaganda AS a ON ra.id_attendants = a.id_attendants JOIN users_teamPropaganda AS u ON a.id_parent = u.id_users",
            "(? IS NULL OR ra.id_events = ?)",
            "",
            "",
            "",
            "ii",
            [$result->eventId, $result->eventId],
            [
                new filterSelector("ra.id_registered_attendants", "Variabilní symbol", "vs", filterSelectorType::NUMBER, filterCompareOperator::EQUALS),
                new filterSelector("aName", "Jméno a přijmení", "aName", filterSelectorType::TEXT, filterCompareOperator::LIKE, true),
                new filterSelector("uName", "Zákonný zástupce", "uName", filterSelectorType::TEXT, filterCompareOperator::LIKE, true),
                new filterSelector("email", "Email zákonného zástupce", "email", filterSelectorType::TEXT, filterCompareOperator::LIKE, true),
                new filterSelector("ra.user_paid", "Zaplaceno", "isUserPaid", filterSelectorType::BOOLEAN_NULL, filterCompareOperator::ISNOT),
                new filterSelector("ra.paid", "Zaplacení ověřeno účetní", "isPaid", filterSelectorType::BOOLEAN_NULL, filterCompareOperator::ISNOT),
                new filterSelector("ra.registered", "Minimální datum registrace", "registeredMin", filterSelectorType::DATETIME, filterCompareOperator::MOREEQUALS),
                new filterSelector("ra.registered", "Maximální datum registrace", "registeredMax", filterSelectorType::DATETIME, filterCompareOperator::LESSEQUALS),
                new filterSelector("ra.user_paid", "Minimální datum platby", "userPaidMin", filterSelectorType::DATETIME, filterCompareOperator::MOREEQUALS),
                new filterSelector("ra.user_paid", "Maximální datum platby", "userPaidMax", filterSelectorType::DATETIME, filterCompareOperator::LESSEQUALS),
                new filterSelector("ra.paid", "Minimální datum oveření platby", "paidMin", filterSelectorType::DATETIME, filterCompareOperator::MOREEQUALS),
                new filterSelector("ra.paid", "Maximální datum oveření platby", "paidMax", filterSelectorType::DATETIME, filterCompareOperator::LESSEQUALS),
                $result->eventId === null ? new filterSelector("ua.id_events", "Událost", "event", filterSelectorType::TEXT, filterCompareOperator::EQUALSNULLABLE, false, ["listId" => "events"]) : null,
            ],
            [
                new filterDisplayer("!action", "Akce", true, filterSelectorType::TEXT, 'formButtonBoxTable'),
                new filterDisplayer("vs", "Variabilní symbol", true, filterSelectorType::TEXT, "fontMono","formatVariableSymbol"),
                new filterDisplayer("aName", "Jméno a přijmení", true),
                new filterDisplayer("uName", "Zákonný zástupce", true),
                new filterDisplayer("!attendantEmail", "Email zákonného zástupce", true),
                new filterDisplayer("hasPaidUser", "Zaplaceno", true, filterSelectorType::BOOLEAN),
                new filterDisplayer("hasPaid", "Zaplacení ověřeno účetní", true, filterSelectorType::BOOLEAN),
                new filterDisplayer("eName", "Událost", false),
                new filterDisplayer("registered", "Datum registrace", false, filterSelectorType::DATETIME),
                new filterDisplayer("user_paid", "Datum platby", false, filterSelectorType::DATETIME),
                new filterDisplayer("paid", "Datum ověření platby", false, filterSelectorType::DATETIME),
            ]
        );
        ?>
    </main>
    <footer>
        <div class="formButtonBoxHolder">
            <a href="./refundPayments.php"><button class="purkynkaButton">Platby na vrácení</button></a>
        </div>
    </footer>
</body>
<script type="module" src="../formWebScripts/js/formScript.js"></script>
<script type='module' src='../assets/sharedScripts.js'></script>
<script type='module' src='./payments.js'></script>

</html>
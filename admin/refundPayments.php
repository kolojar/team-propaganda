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
        $stmt = $conn->prepare("SELECT id_attendants, id_events, bank_account,registered,paid FROM registered_attendants_teamPropaganda WHERE id_registered_attendants = ?");
        if (!$stmt->bind_param("i", $_POST["id"]) || !$stmt->execute() || !$stmt->store_result() || !$stmt->bind_result($attendantId, $eventId, $bankAccount, $registered, $paid) || !$stmt->fetch() || !$stmt->close()) {
            http_response_code(400);
            echo "Nelze získat informace o zájemci.";
            die();
        }

        //Insert SQL entry
        $stmt = $conn->prepare("INSERT INTO unregistered_attendants_teamPropaganda(id_registered_attendants, id_attendants, id_events, bank_account, registered, paid, reason) VALUES (?,?,?,?,?,?,?)");
        if (!$stmt->bind_param("iiissss", $_POST["id"], $attendantId, $eventId, $bankAccount, $registered, $paid, $_POST["reason"]) || !$stmt->execute() || !$stmt->close()) {
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
    <title>Vrácení plateb</title>
    <link rel="stylesheet" href="../formWebScripts/css/formStyle.css">
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body class="pageHolder">
    <header>
        <?php $result = setupTitlebarAdmin($conn, "payments.php") ?>
    </header>
    <main>
        <h1>Čekající platby na vrácení</h1>
        <i>Poznámka: Někdy se i v této sekci objevují ikonky na schválení plateb = není to chyba.
            Tato situace může nastat, pokud se zájemce odhlásil a nezaplatil vůbec, nebo odeslal peníze a účetní je ještě nepotvrdila.
            Jedná se o bezpečnostní funkci. Nejprve tedy vyberte stav platby (zaplaceno / nezaplaceno) a poté proveďte vrácení peněz.</i><br>
        <i>Doporučení: Při kontrole plateb se doporučuje počkat několik dní. Některým bankám trvají převody delší dobu.</i> <br>
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

function formatVariableSymbol($value) {
            return str_pad($value,10,"0",STR_PAD_LEFT);
        }

        function action($result, $setup)
        {
            $variableSymbol = $result['vs'];
            $bankAccount = $result['bank_account'];
            $eventPrice = $result['price'];
            if ($result['paid'] !== null) {
                if($result['refunded'] === null) {
                return "<button class='purkynkaButton btnRefundTable' variableSymbol='$variableSymbol' bankAccount='$bankAccount' price='$eventPrice' form-icon='!refund'></button>";
                } else {
                    return "Již vráceno";
                }
            } else {
                return "<button class='purkynkaButton btnTableAddPayment' variableSymbol='$variableSymbol' unregistered='1' form-icon='!addPayment'></button><button class='purkynkaButton btnRemoveNotPaidTable' variableSymbol='$variableSymbol' form-icon='!addNoPayment'></button>";
            }
        }

        //Request waiting for refund attendats and paid attendants
        setupFilteredTable(
            $conn,
            $result,
            "purkynkaTableStripped purkynkaTableFullLines",
            "ua.id_registered_attendants as vs, ua.bank_account, ua.registered, ua.paid, ua.unregistered, ua.refunded, ua.reason, ua.id_attendants, a.name, a.surname, a.id_parent, u.name, u.surname,u.email,e.price,e.name as eName, CONCAT(a.name, ' ', a.surname) as aName, CONCAT(u.name, ' ', u.surname) as uName, (ua.paid IS NOT NULL) as hasPaid, (ua.refunded IS NOT NULL) as hasRefunded, (ua.user_paid IS NOT NULL) as hasPaidUser",
            "unregistered_attendants_teamPropaganda ua LEFT JOIN attendants_teamPropaganda a ON ua.id_attendants = a.id_attendants LEFT JOIN users_teamPropaganda u ON a.id_parent = u.id_users LEFT JOIN events_teamPropaganda e ON ua.id_events = e.id_events",
            "(? IS NULL OR ua.id_events = ?) AND e.price != 0;",
            "",
            "",
            "",
            "ii",
            [$resultEventId,$resultEventId],
            [
                new filterSelector("ua.id_registered_attendants", "Variabilní symbol", "vs", filterSelectorType::NUMBER, filterCompareOperator::EQUALS),
                new filterSelector("aName", "Jméno a přijmení", "aName", filterSelectorType::TEXT, filterCompareOperator::LIKE, true),
                new filterSelector("uName", "Zákonný zástupce", "uName", filterSelectorType::TEXT, filterCompareOperator::LIKE, true),
                new filterSelector("email", "Email zákonného zástupce", "email", filterSelectorType::TEXT, filterCompareOperator::LIKE, true),
                new filterSelector("ua.user_paid", "Zaplaceno", "isUserPaid", filterSelectorType::BOOLEAN_NULL, filterCompareOperator::ISNOT),
                new filterSelector("ua.paid", "Zaplacení ověřeno účetní", "isPaid", filterSelectorType::BOOLEAN_NULL, filterCompareOperator::ISNOT),
                new filterSelector("ua.refunded", "Vráceno", "isRefunded", filterSelectorType::BOOLEAN_NULL, filterCompareOperator::ISNOT),
                new filterSelector("ua.registered", "Minimální datum registrace", "registeredMin", filterSelectorType::DATETIME, filterCompareOperator::MOREEQUALS),
                new filterSelector("ua.registered", "Maximální datum registrace", "registeredMax", filterSelectorType::DATETIME, filterCompareOperator::LESSEQUALS),
                new filterSelector("ua.user_paid", "Minimální datum platby", "userPaidMin", filterSelectorType::DATETIME, filterCompareOperator::MOREEQUALS),
                new filterSelector("ua.user_paid", "Maximální datum platby", "userPaidMax", filterSelectorType::DATETIME, filterCompareOperator::LESSEQUALS),
                new filterSelector("ua.paid", "Minimální datum ověření platby", "paidMin", filterSelectorType::DATETIME, filterCompareOperator::MOREEQUALS),
                new filterSelector("ua.paid", "Maximální datum ověření platby", "paidMax", filterSelectorType::DATETIME, filterCompareOperator::LESSEQUALS),
                new filterSelector("ua.unregistered", "Minimální datum odhlášení", "unregisteredMin", filterSelectorType::DATETIME, filterCompareOperator::MOREEQUALS),
                new filterSelector("ua.unregistered", "Maximální datum odhlášení", "unregisteredMax", filterSelectorType::DATETIME, filterCompareOperator::LESSEQUALS),
                new filterSelector("ua.refunded", "Minimální datum vrácení platby", "refundedMin", filterSelectorType::DATETIME, filterCompareOperator::MOREEQUALS),
                new filterSelector("ua.refunded", "Maximální datum vrácení platby", "refundedMax", filterSelectorType::DATETIME, filterCompareOperator::LESSEQUALS),
                new filterSelector("ua.reason", "Důvod odhlášení", "reason", filterSelectorType::TEXT, filterCompareOperator::LIKE),
                $result->eventId === null ? new filterSelector("ua.id_events","Událost","event",filterSelectorType::TEXT,filterCompareOperator::EQUALSNULLABLE,false,["listId" => "events"]) : null,
            ],
            [
                new filterDisplayer("!action", "Akce", true, filterSelectorType::TEXT, 'formButtonBoxTable'),
                new filterDisplayer("vs", "Variabilní symbol", true, filterSelectorType::TEXT, "fontMono","formatVariableSymbol"),
                new filterDisplayer("aName", "Jméno a přijmení", true),
                new filterDisplayer("uName", "Zákonný zástupce", true),
                new filterDisplayer("!attendantEmail", "Email zákonného zástupce", true),
                new filterDisplayer("hasPaidUser", "Zaplaceno", true, filterSelectorType::BOOLEAN),
                new filterDisplayer("hasPaid", "Zaplacení ověřeno účetní", true, filterSelectorType::BOOLEAN),
                new filterDisplayer("hasRefunded", "Vráceno", true, filterSelectorType::BOOLEAN),
                new filterDisplayer("eName","Událost",false),
                new filterDisplayer("registered", "Datum registrace", false, filterSelectorType::DATETIME),
                new filterDisplayer("user_paid", "Datum platby", false, filterSelectorType::DATETIME),
                new filterDisplayer("paid", "Datum ověření platby", false, filterSelectorType::DATETIME),
                new filterDisplayer("unregistered", "Datum odhlášení", false, filterSelectorType::DATETIME),
                new filterDisplayer("refunded", "Datum vrácení platby", false, filterSelectorType::DATETIME),
                new filterDisplayer("reason", "Důvod odhlášení", true),
            ]
        );
        ?>
    </main>
    <footer>
        <div class="formButtonBoxHolder">
            <a href="./payments.php"><button class="purkynkaButton">Platby na zkontrolování</button></a>
        </div>
    </footer>
</body>
<script type="module" src="../formWebScripts/js/formScript.js"></script>
<script type='module' src='../assets/sharedScripts.js'></script>
<script type='module' src='./payments.js'></script>
</html>
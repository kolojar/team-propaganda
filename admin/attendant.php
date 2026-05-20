<?php
session_start();
require "../assets/config.php";
require "./adminFunctions.php";

if (isset($_POST["action"])) {
    if ($_POST["action"] == "update") {
        //Check if values set
        if (!isset($_POST["name"]) || !isset($_POST["surname"]) || !isset($_POST["school"]) || !isset($_POST["id"])) {
            http_response_code(400);
            echo "Neplatné použití funkce - chybí parametr";
            die();
        }

        //Make SQL Update
        $stmt = $conn->prepare("UPDATE attendants_teamPropaganda SET name=?, surname=?, id_schools=? WHERE id_attendants=?");
        $stmt->bind_param("ssii", $_POST["name"], $_POST["surname"], $_POST["school"], $_POST["id"]);
        if ($stmt->execute()) {
            http_response_code(201);
            echo "Entry updated.";
            die();
        } else {
            http_response_code(400);
            echo "Entry could not be updated.";
            die();
        }
    } else if ($_POST["action"] == "delete") {
        //Check if values set
        if (!isset($_POST["id"])) {
            http_response_code(400);
            echo "Neplatné použití funkce - chybí parametr";
            die();
        }

        //Make SQL Update
        $stmt = $conn->prepare("DELETE FROM unregistered_attendants_teamPropaganda WHERE variable_symbol=?");
        $stmt->bind_param("i", $_POST["id"]);
        if ($stmt->execute()) {
            http_response_code(201);
            echo "Entry deleted.";
            die();
        } else {
            http_response_code(400);
            echo "Entry could not be deleted.";
            die();
        }
    } else if ($_POST["action"] == "addPayment") {
        //Check if values set
        if (
            !isset($_POST["paid"]) ||
            !isset($_POST["bank_account"]) ||
            !isset($_POST["id"]) ||
            !isset($_POST["unregistered"]) ||
            !isset($_POST["email"]) ||
            !isset($_POST["id_events"])
        ) {
            http_response_code(400);
            echo "Neplatné použití funkce - chybí parametr";
            die();
        }

        //Make SQL Update
        $table = "registered_attendants_teamPropaganda";
        if ($_POST["unregistered"] == "1") {
            $table = "unregistered_attendants_teamPropaganda";
        }
        $stmt = $conn->prepare("UPDATE " . $table . " SET paid=?,bank_account=? WHERE variable_symbol=?;");
        $stmt->bind_param("ssi", $_POST["paid"], $_POST["bank_account"], $_POST["id"]);
        if ($stmt->execute()) {
            $res = $conn->query("SELECT price FROM `events_teamPropaganda` WHERE id_events = " . $_POST["id_events"])->fetch_assoc();
            $message = file_get_contents("../assets/PaymentOk.html");
            $message = str_replace("\${variable_symbol}", str_pad($_POST["id"], 10, "0", STR_PAD_LEFT), $message);
            $date = new DateTime($_POST["paid"]);
            $d = $date->format('d. m. Y H:i:s');
            $message = str_replace("\${payment_date}", $d, $message);
            $message = str_replace("\${amount}", $res["price"], $message);

            echo "\n\n$message\n\n";
            sendMail($_POST["email"], "Platba potvrzena.", $message);
            http_response_code(201);
            echo "Entry updated.";
            die();
        } else {
            http_response_code(400);
            echo "Entry could not be updated.";
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
        $stmt = $conn->prepare("UPDATE unregistered_attendants_teamPropaganda SET refunded = CURRENT_TIMESTAMP() WHERE variable_symbol = ?;");
        $stmt->bind_param("i", $_POST["id"]);
        if ($stmt->execute()) {
            http_response_code(201);
            echo "Entry updated.";
            die();
        } else {
            http_response_code(400);
            echo "Entry could not be updated.";
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
    } else {
        http_response_code(400);
        echo "Invalid usage of function - missing action parameter";
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
    <title>Zájemce</title>
    <link rel="stylesheet" href="../formWebScripts/css/sharedStyle.css">
    <link rel="stylesheet" href="../formWebScripts/css/formStyle.css">
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body class="pageHolder">
    <header>
        <?php
        setupTitlebarAdmin($conn, "attendant.php")
        ?>
    </header>
    <main>
        <?php
        //Get attendant info
        $stmt = $conn->prepare("SELECT a.name, a.surname, a.id_parent, a.id_schools, u.name, u.surname, u.email, s.name, s.address FROM attendants_teamPropaganda a JOIN users_teamPropaganda u ON a.id_parent = u.id_users JOIN schools_teamPropaganda s ON a.id_schools = s.id_schools WHERE a.id_attendants = ?;");
        $stmt->bind_param("i", $_GET["attendant"]);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($name, $surname, $parentId, $schoolId, $parentName, $parentSurname, $parentEmail, $schoolName, $schoolAddress);
        $stmt->fetch();

        //Print HTML
        echo "<h1>Informace o zájemci: $name $surname</h1>";
        echo "<form-input icon='!userName' label='Křestní jméno:' class='validate' do-change-check='true' type='text' value-id='name' original-value='$name' value='$name' placeholder='$name'></form-input>";
        echo "<form-input icon='!userSurname' label='Přijmení:' class='validate' do-change-check='true' type='text' value-id='surname' original-value='$surname' value='$surname' placeholder='$surname'></form-input>";
        //echo "<form-input label='Email:' class='attendantValidate' do-change-check='true' type='email' id='email' original-value='$email' value='$email' placeholder='$email'></form-input>";
        echo "<p>Zákonný zástupce: $parentName $parentSurname</p>";
        echo "<p>Email zákonného zástupce: <a href='./sendMail.php?uid=$parentId&isNILE=0'>$parentEmail</a></p>";
        //echo "<p>Základní škola: <a id='schoolIdHolder' schoolId='$schoolId' href='?view=school&school=$schoolId'>$schoolName → $schoolAddress</a> <button class='formButton formWarnColor' id='attendantBtnChangeSchool'>Změnit školu</button></p>";
        echo "<form-input icon='!school' id='school' label='Základní škola:' class='validate' type='select' do-change-check='true' value-id='school' original-value='$schoolName → $schoolAddress' value='$schoolName → $schoolAddress' is-case-sensitive-list='false' style='width: 100%'></form-input>";
        echo "<div class='formButtonBoxHolder'>";
        echo "<div class='formButtonBox'>";
        echo "<button class='formButton purkynkaButton btnSave' form-icon='!save'></button>";
        echo "<button class='formButton purkynkaButton btnCancel' form-icon='!dontSave'></button>";
        echo "<a href='./attendants.php'><button class='formButton purkynkaButton' form-icon='!listTable'><span>Zpět na seznam zájemců</span></button></a>";
        echo "<a href='./school.php?school=$schoolId'><button class='formButton purkynkaButton' form-icon='!school'><span>Zobrazit informace o škole</span></button></a>";
        echo "<a href='./user.php?user=$parentId'><button class='formButton purkynkaButton' form-icon='!parentInfo'><span>Zobrazit informace o zákonném zástupci</span></button></a>";
        echo "</div>";
        echo "</div>";
        ?>
    </main>
    <footer>

    </footer>
</body>
<script type="module" src="../formWebScripts/js/formScript.js"></script>
<script type='module' src='./attendant.js'></script>

</html>

<?php
session_start();
require "../assets/config.php";
require "./adminFunctions.php";

if (isset($_POST["action"])) {
    if ($_POST["action"] == "update") {
        //Check if values set
        if (!isset($_POST["email"]) || !isset($_POST["name"]) || !isset($_POST["surname"]) || !isset($_POST["school"]) || !isset($_POST["id"])) {
            http_response_code(400);
            echo "Invalid usage of function - missing table column parameters";
            die();
        }

        //Make SQL Update
        $stmt = $conn->prepare("UPDATE users_teamPropaganda SET email=?, name=?, surname=?, id_schools=? WHERE id_users=?");
        $stmt->bind_param("sssii", $_POST["email"], $_POST["name"], $_POST["surname"], $_POST["school"], $_POST["id"]);
        if ($stmt->execute()) {
            http_response_code(201);
            echo "Entry updated.";
            die();
        } else {
            http_response_code(400);
            echo "Entry could not be updated.";
            die();
        }
    }else if ($_POST["action"] == "delete") {
        //Check if values set
        if (!isset($_POST["id"])) {
            http_response_code(400);
            echo "Invalid usage of function - missing table column parameters";
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
    }  else if ($_POST["action"] == "addPayment") {
        //Check if values set
        if (!isset($_POST["paid"]) || !isset($_POST["bank_account"]) || !isset($_POST["id"]) || !isset($_POST["unregistered"])) {
            http_response_code(400);
            echo "Invalid usage of function - missing table column parameters";
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
            echo "Invalid usage of function - missing table column parameters";
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
            echo "Invalid usage of function - missing table column parameters";
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zájemce</title>
    <link rel="stylesheet" href="../formWebScripts/css/sharedStyle.css">
    <link rel="stylesheet" href="../formWebScripts/css/formStyle.css">
    <link rel="stylesheet" href="../formWebScripts/css/tableStyle.css">
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body class="pageHolder">
    <header>
        <?php
        setupTitlebar($conn, "attendant.php")
            ?>
    </header>
    <main>
        <?php
        //Get attendant info
        $stmt = $conn->prepare("SELECT name,surname,id_schools, id_parent FROM attendants_teamPropaganda WHERE id_attendants=? LIMIT 1");
        $stmt->bind_param("i", $_GET["attendant"]);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($name, $surname, $idSchool, $idParent);
        $stmt->fetch();

        //Get attendant's school info
        $stmt = $conn->prepare("SELECT name, address FROM schools_teamPropaganda WHERE id_schools = ? LIMIT 1");
        $stmt->bind_param("i", $idSchool);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($schoolName, $schoolAddress);
        $stmt->fetch();

        //Get attendant's parent info
        $stmt = $conn->prepare("SELECT name,surname,email FROM users_teamPropaganda WHERE id_users = ? LIMIT 1");
        $stmt->bind_param("i", $idParent);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($parentName, $parentSurname, $parentEmail);
        $stmt->fetch();

        //Print HTML
        echo "<h1>Informace o zájemci: $name $surname</h1>";
        echo "<form-input label='Křestní jméno:' class='attendantValidate' do-change-check='true' type='text' id='name' original-value='$name' value='$name' placeholder='$name'></form-input>";
        echo "<br>";
        echo "<form-input label='Přijmení:' class='attendantValidate' do-change-check='true' type='text' id='surname' original-value='$surname' value='$surname' placeholder='$surname'></form-input>";
        echo "<br>";
        //echo "<form-input label='Email:' class='attendantValidate' do-change-check='true' type='email' id='email' original-value='$email' value='$email' placeholder='$email'></form-input>";
        echo "<p>Zákonný zástupce: $parentName $parentSurname</p>";
        echo "<p>Email zákonného zástupce: <a href='mailto:$parentEmail'>$parentEmail</a></p>";
        //echo "<p>Základní škola: <a id='schoolIdHolder' schoolId='$schoolId' href='?view=school&school=$schoolId'>$schoolName → $schoolAddress</a> <button class='formButton formWarnColor' id='attendantBtnChangeSchool'>Změnit školu</button></p>";
        echo "<br>";
        echo "<form-input label='Základní škola:' class='attendantValidate' type='select' do-change-check='true' id='school' original-value='$schoolName → $schoolAddress' value='$schoolName → $schoolAddress' is-case-sensitive-list='false' style='width: 100%'></form-input>";
        echo "<div class='formButtonBoxHolder'>";
        echo "<div class='formButtonBox'>";
        echo "<button id='btnSave' class='formButton formOkColor'>Uložit změny</button>";
        echo "<button id='btnCancel' class='formButton formErrorColor'>Zrušit změny</button>";
        echo "<a href='./attendants.php'><button class='formButton formInfoColor'>Zpět na seznam zájemců</button></a>";
        echo "<a href='./school.php?school=$schoolId'><button class='formButton formInfoColor'>Zobrazit informace o škole</button></a>";
        echo "<a href='./user.php?user=$idParent'><button class='formButton formInfoColor'>Zobrazit informace o zákonném zástupci</button></a>";
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
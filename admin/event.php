<?php
session_start();
require "../assets/config.php";
if (isset($_POST["action"])) {
    if ($_POST["action"] == "update") {
        //Check if values set
        if (!isset($_POST["name"]) || !isset($_POST["placesToSit"]) || !isset($_POST["isFunctional"]) || !isset($_POST["note"]) || !isset($_POST["id"])) {
            http_response_code(400);
            echo "Invalid usage of function - missing table column parameters";
            die();
        }

        //Make SQL Update
        $stmt = $conn->prepare("UPDATE classrooms SET name=?, placesToSit=?,isFunctional=?, note=? WHERE id_classrooms=?");
        $stmt->bind_param("siisi", $_POST["name"], $_POST["placesToSit"], $_POST["isFunctional"], $_POST["note"], $_POST["id"]);
        if ($stmt->execute()) {
            http_response_code(201);
            echo "Entry updated.";
            die();
        } else {
            http_response_code(400);
            echo "Entry could not be updated.";
            die();
        }
    } else if ($_POST["action"] == "insert") {
        //Check if values set
        if (!isset($_POST["name"]) || !isset($_POST["placesToSit"]) || !isset($_POST["isFunctional"]) || !isset($_POST["note"])) {
            http_response_code(400);
            echo "Invalid usage of function - missing table column parameters";
            die();
        }

        //Make SQL Insert
        $stmt = $conn->prepare("INSERT INTO classrooms(name,placesToSit,isFunctional,note) VALUES (?, ?,?, ?)");
        $stmt->bind_param("siis", $_POST["name"], $_POST["placesToSit"], $_POST["isFunctional"], $_POST["note"]);
        if ($stmt->execute()) {
            http_response_code(201);
            echo "Entry created.";
            die();
        } else {
            http_response_code(400);
            echo "Entry could not be created.";
            die();
        }
    } else if ($_POST["action"] == "delete")  {
        //Check if values set
        if(!isset($_POST["id"])) {
            http_response_code(400);
            echo "Invalid usage of function - missing table column parameters";
            die();
        }

        //Make SQL Delete
        $stmt = $conn->prepare("DELETE FROM classrooms WHERE id_classrooms=?");
        $stmt->bind_param("i",$_POST["id"]);
        if ($stmt->execute()) {
            http_response_code(201);
            echo "Entry deleted.";
            die();
        } else {
            http_response_code(400);
            echo "Entry could not be deleted.";
            die();
        }
    }
    else {
        http_response_code(400);
        echo "Invalid usage of function - invalid action";
        die();
    }
}
?>

<!DOCTYPE html>
<html lang="cs">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Událost</title>
    <link rel="stylesheet" href="../formWebScripts/css/sharedStyle.css">
    <link rel="stylesheet" href="../formWebScripts/css/formStyle.css">
    <link rel="stylesheet" href="../formWebScripts/css/tableStyle.css">
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body class="pageHolder">
    <header style="padding-left: 4px; padding-right: 4px; margin-top: 0px; padding-top: 1px; padding-bottom: 0px;" class="formInfoColor">
        <h1>Akce: <?php echo $_SESSION["adminSubEventId"] ?></h1>
        <div class="formButtonBoxHolder">
            <div class="formButtonBox formJustifyLeft">
                <a href="./admin.php"><button class="formButton formOkColor">Hlavní menu</button></a>
                <a href="./attendants.php"><button class="formButton formOkColor">Zájemci</button></a>
                <a href="./classrooms.php"><button class="formButton formOkColor">Učebny</button></a>
                <a href="./schools.php"><button class="formButton formOkColor">Školy</button></a>
                <a href="./messages.php"><button class="formButton formOkColor">Zprávy</button></a>
                <a href="./payments.php"><button class="formButton formOkColor">Platby</button></a>
            </div>
            <div class="formButtonBox formJustifyRight">
                <a href="./changeEvent.php"><button class="formButton formWarnColor">Změnit událost</button></a>
                <a href="./logout.php"><button class="formButton formErrorColor">Odhlásit se</button></a>
            </div>
        </div>
    </header>
    <main>
        <datalist id="typeTypes">
            <option value="kurzy" label="Kurzy"></option>
            <option value="prijimacky" label="Přijmačky nanečisto"></option>
            <option value="dod" label="Dny otevřených dveří"></option>
            <option value="denFirem" label="Den firem"></option>
        </datalist>
        <?php
        $name = "";
        $type = "";
        $description = "";
        $activeSince = new DateTime()->format("Y-m-d");
        $activeUntil = null;
        $registrationOpen = null;
        $registrationClose = null;
        $repeatInterval = 0;
        $repeatCount = 0;
        $repeatStart = null;
        $exists = "true";
        if (isset($_GET["newEvent"])) {
            echo "<h1>Vytvořit novou událost</h1>";
            $exists = "false";
        } else {
            $stmt = $conn->prepare("SELECT name, type, description, active_since, active_until, registration_open, registration_close, repeat_interval, repeat_count, repeat_start FROM events WHERE id_events = ?;");
            $stmt->bind_param("i", $_GET["event"]);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($name,$type,$description,$activeSince,$activeUntil,$registrationOpen,$registrationClose,$repeatInterval, $repeatCount, $repeatStart);
            $stmt->fetch();
            echo "<h1>Informace o události: $name</h1>";
        }
        //$isFunctionalString = $isFunctional == 1 ? "true" : "false";

        //Create HTML
        echo "<form-input label='Název události:' class='eventValidate' do-change-check='$exists' type='text' id='name' original-value='$name' value='$name' placeholder='$name'></form-input>";
        echo "<br>";
        echo "<form-input label='Typ události:' class='eventValidate' do-change-check='$exists' type='select' id='type' original-value='$type' value='$type' placeholder='$type' list='typeTypes'></form-input>";
        echo "<br>";
        echo "<form-input label='Popis události:' class='eventValidate' do-change-check='$exists' type='textarea' id='description' original-value='$description' value='$description' placeholder='$description'></form-input>";
        echo "<br>";
        echo "<form-input label='Událost aktivní od:' class='eventValidate' do-change-check='$exists' type='date' id='active_since' original-value='$activeSince' value='$activeSince' placeholder='$activeSince'></form-input>";
        echo "<br>";
        echo "<form-input label='Událost aktivní do:' class='eventValidate' do-change-check='$exists' type='date' id='active_until' original-value='$activeUntil' value='$activeUntil' placeholder='$activeUntil'></form-input>";
        echo "<br>";
        echo "<form-input label='Registrace aktivní od:' class='eventValidate' do-change-check='$exists' type='date' id='registration_open' original-value='$registrationOpen' value='$registrationOpen' placeholder='$registrationOpen'></form-input>";
        echo "<br>";
        echo "<form-input label='Registrace aktivní do:' class='eventValidate' do-change-check='$exists' type='date' id='registration_close' original-value='$registrationClose' value='$registrationClose' placeholder='$registrationClose'></form-input>";
        echo "<br>";
        echo "<form-input label='Název události:' class='eventValidate' do-change-check='$exists' type='text' id='name' original-value='$name' value='$name' placeholder='$name'></form-input>";
        echo "<br>";
        echo "<form-input label='Počet míst k sezení:' class='eventValidate' do-change-check='$exists' type='number' id='placesToSit' original-value='$placesToSit' value='$placesToSit' placeholder='$placesToSit'></form-input>";
        echo "<br><form-toggle labelBefore='Je učebna aktivní: ' class='eventValidate' offColorClass='formErrorColor' onColorClass='formOkColor' original-value='$isFunctionalString' value='$isFunctionalString' id='isFunctional'></form-toggle><br>";
        echo "<form-input label='Poznámka:' class='eventValidate' do-change-check='$exists' type='textarea' id='note' original-value='$note' value='$note' placeholder='$note'></form-input>";
        echo "<div class='formButtonBoxHolder'>";
        echo "<div class='formButtonBox'>";
        echo "<button id='btnSave' exists='$exists' class='formButton formOkColor'>Uložit změny</button>";
        echo "<button id='btnCancel' exists='$exists' class='formButton formErrorColor'>Zrušit změny</button>";
        echo "<a href='./classrooms.php'><button class='formButton formInfoColor'>Zpět na seznam učeben</button></a>";
        echo "</div>";
        echo "</div>";
        ?>
    </main>
    <footer>

    </footer>
</body>
<script type="module" src="../formWebScripts/js/formScript.js"></script>
<script type='module' src='./classroom.js'></script>

</html>
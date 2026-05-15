<?php
session_start();
require "../assets/config.php";
require "./adminFunctions.php";

if (isset($_POST["action"])) {
    if ($_POST["action"] == "update") {
        //Check if values set
        if (!isset($_POST["name"]) || !isset($_POST["type"]) || !isset($_POST["description"]) || !isset($_POST["active_since"]) || !isset($_POST["active_until"]) || !isset($_POST["registration_open"]) || !isset($_POST["registration_close"]) || !isset($_POST["repeat_interval"]) || !isset($_POST["repeat_count"]) || !isset($_POST["repeat_start"]) || !isset($_POST["price"]) || !isset($_POST["id"])) {
            http_response_code(400);
            echo "Invalid usage of function - missing table column parameters";
            die();
        }

        //Make SQL Update
        $stmt = $conn->prepare("UPDATE events_teamPropaganda SET name=?,type=?,description=?,active_since=?,active_until=?,registration_open=?,registration_close=?,repeat_interval=?,repeat_count=?,repeat_start=?,price=? WHERE id_events=?");
        $stmt->bind_param("sssssssiisii", $_POST["name"], $_POST["type"], $_POST["description"], $_POST["active_since"], $_POST["active_until"], $_POST["registration_open"], $_POST["registration_close"], $_POST["repeat_interval"], $_POST["repeat_count"], $_POST["repeat_start"],$_POST["price"], $_POST["id"]);
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
        if (!isset($_POST["name"]) || !isset($_POST["type"]) || !isset($_POST["description"]) || !isset($_POST["active_since"]) || !isset($_POST["active_until"]) || !isset($_POST["registration_open"]) || !isset($_POST["registration_close"]) || !isset($_POST["repeat_interval"]) || !isset($_POST["repeat_count"]) || !isset($_POST["repeat_start"]) || !isset($_POST["price"])) {
            http_response_code(400);
            echo "Invalid usage of function - missing table column parameters";
            die();
        }

        //Make SQL Insert
        $stmt = $conn->prepare("INSERT INTO events_teamPropaganda(name,type,description,active_since,active_until,registration_open,registration_close,repeat_interval,repeat_count,repeat_start,price) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("sssssssiisi", $_POST["name"], $_POST["type"], $_POST["description"], $_POST["active_since"], $_POST["active_until"], $_POST["registration_open"], $_POST["registration_close"], $_POST["repeat_interval"], $_POST["repeat_count"], $_POST["repeat_start"], $_POST["price"]);
        if ($stmt->execute()) {
            http_response_code(201);
            echo "Entry created.";
            die();
        } else {
            http_response_code(400);
            echo "Entry could not be created.";
            die();
        }
    } else if ($_POST["action"] == "delete") {
        //Check if values set
        if (!isset($_POST["id"])) {
            http_response_code(400);
            echo "Invalid usage of function - missing table column parameters";
            die();
        }

        //Make SQL Delete
        $stmt = $conn->prepare("DELETE FROM events_teamPropaganda WHERE id_events=?");
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
    } else {
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
    <header>
        <?php setupTitlebar($conn,"event.php") ?>
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
        $description = "-";
        $activeSinceDB = new DateTime("now", new DateTimeZone("Europe/Prague"))->format('Y-m-d H:i:s');
        $activeUntilDB = new DateTime("now", new DateTimeZone("Europe/Prague"))->format('Y-m-d H:i:s');
        $registrationOpenDB = new DateTime("now", new DateTimeZone("Europe/Prague"))->format('Y-m-d H:i:s');
        $registrationCloseDB = new DateTime("now", new DateTimeZone("Europe/Prague"))->format('Y-m-d H:i:s');
        $repeatInterval = 0;
        $repeatCount = 0;
        $repeatStartDB = new DateTime("now", new DateTimeZone("Europe/Prague"))->format('Y-m-d H:i:s');
        $price = 0;
        $exists = "true";
        if (isset($_GET["newEvent"])) {
            echo "<h1>Vytvořit novou událost</h1>";
            $exists = "false";
        } else {
            $stmt = $conn->prepare("SELECT name, type, description, active_since, active_until, registration_open, registration_close, repeat_interval, repeat_count, repeat_start, price FROM events_teamPropaganda WHERE id_events = ?;");
            $stmt->bind_param("i", $_GET["event"]);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($name, $type, $description, $activeSinceDB, $activeUntilDB, $registrationOpenDB, $registrationCloseDB, $repeatInterval, $repeatCount, $repeatStartDB, $price);
            $stmt->fetch();
            echo "<h1>Informace o události: $name</h1>";
        }
        $activeSince = DateTime::createFromFormat('Y-m-d H:i:s', $activeSinceDB)->format(JS_TIME_FORMAT);
        $activeUntil = DateTime::createFromFormat('Y-m-d H:i:s', $activeUntilDB)->format(JS_TIME_FORMAT);
        $registrationOpen = DateTime::createFromFormat('Y-m-d H:i:s', $registrationOpenDB)->format(JS_TIME_FORMAT);
        $registrationClose = DateTime::createFromFormat('Y-m-d H:i:s', $registrationCloseDB)->format(JS_TIME_FORMAT);
        $repeatStart = DateTime::createFromFormat('Y-m-d H:i:s', $repeatStartDB)->format(JS_TIME_FORMAT);
        //$isFunctionalString = $isFunctional == 1 ? "true" : "false";
        
        //Create HTML
        echo "<form-input label='Název události:' class='eventValidate' do-change-check='$exists' type='text' id='name' original-value='$name' value='$name' placeholder='$name'></form-input>";
        echo "<br>";
        echo "<form-input label='Typ události:' is-case-sensitive-list='false' class='eventValidate' do-change-check='$exists' type='select' id='type' original-value='$type' raw-value='$type' placeholder='$type' list='typeTypes'></form-input>";
        echo "<br>";
        echo "<form-input label='Popis události:' class='eventValidate' do-change-check='$exists' type='textarea' id='description' original-value='$description' value='$description' placeholder='$description'></form-input>";
        echo "<br>";
        echo "<form-input label='Cena události:' class='eventValidate' do-change-check='$exists' type='number' min=0 id='price' original-value='$price' value='$price' placeholder='$price'></form-input>";
        echo "<br>";
        echo "<form-input label='Událost aktivní od:' class='eventValidate' do-change-check='$exists' type='datetime-local' id='active_since' original-value='$activeSince' value='$activeSince'></form-input>";
        echo "<br>";
        echo "<form-input label='Událost aktivní do:' class='eventValidate' do-change-check='$exists' type='datetime-local' id='active_until' original-value='$activeUntil' value='$activeUntil'></form-input>";
        echo "<br>";
        echo "<form-input label='Registrace aktivní od:' class='eventValidate' do-change-check='$exists' type='datetime-local' id='registration_open' original-value='$registrationOpen' value='$registrationOpen'></form-input>";
        echo "<br>";
        echo "<form-input label='Registrace aktivní do:' class='eventValidate' do-change-check='$exists' type='datetime-local' id='registration_close' original-value='$registrationClose' value='$registrationClose'></form-input>";
        echo "<br>";
        echo "<form-input min=0 label='Počet konání akce:' class='eventValidate' do-change-check='$exists' type='number' id='repeat_count' original-value='$repeatCount' value='$repeatCount' placeholder='$repeatCount'></form-input>";
        echo "<br>";
        echo "<form-input min=0 label='Rozestup automatického opakování akce ve dnech (automatické vytváření podakcí):' class='eventValidate' do-change-check='$exists' type='number' id='repeat_interval' original-value='$repeatInterval' value='$repeatInterval' placeholder='$repeatInterval'></form-input>";
        echo "<br>";
        echo "<form-input label='První den podakce (automatické vytváření podakcí):' class='eventValidate' do-change-check='$exists' type='datetime-local' id='repeat_start' original-value='$repeatStart' value='$repeatStart'></form-input>";
        echo "<br>";
        echo "<div class='formButtonBoxHolder'>";
        echo "<div class='formButtonBox'>";
        echo "<button id='btnSave' exists='$exists' class='formButton formOkColor'>Uložit změny</button>";
        echo "<button id='btnCancel' exists='$exists' class='formButton formErrorColor'>Zrušit změny</button>";
        echo "<a href='./events.php'><button class='formButton formInfoColor'>Zpět na seznam události</button></a>";
        echo "</div>";
        echo "</div>";
        ?>
    </main>
    <footer>

    </footer>
</body>
<script type="module" src="../formWebScripts/js/formScript.js"></script>
<script type='module' src='./event.js'></script>

</html>
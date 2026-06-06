<?php
session_start();
require "../assets/config.php";
require "./adminFunctions.php";

if (isset($_POST["action"])) {
    try {
        if ($_POST["action"] == "update") {
            //Check if values set
            if (!isset($_POST["name"]) || !isset($_POST["description"]) || !isset($_POST["date"]) || !isset($_POST["active_since"]) || !isset($_POST["active_until"]) || !isset($_POST["registration_open"]) || !isset($_POST["registration_close"]) || !isset($_POST["id"])) {
                http_response_code(400);
                echo "Neplatné použití funkce - chybí parametr";
                die();
            }

            //Make SQL Update
            $stmt = $conn->prepare("UPDATE company_days_teamPropaganda SET name=?,description=?,date=?,active_since=?,active_until=?,registration_open=?,registration_close=? WHERE id_company_days=?");
            if ($stmt->bind_param("sssssssi", $_POST["name"], $_POST["description"], $_POST["date"], $_POST["active_since"], $_POST["active_until"], $_POST["registration_open"], $_POST["registration_close"], $_POST["id"]) && $stmt->execute() && $stmt->close()) {
                http_response_code(201);
                echo "Den firem upraven.";
                die();
            } else {
                http_response_code(400);
                echo "Den firem nemohl být upraven.";
                die();
            }
        } else if ($_POST["action"] == "insert") {
            //Check if values set
            if (!isset($_POST["name"]) || !isset($_POST["description"]) || !isset($_POST["date"]) || !isset($_POST["active_since"]) || !isset($_POST["active_until"]) || !isset($_POST["registration_open"]) || !isset($_POST["registration_close"])) {
                http_response_code(400);
                echo "Neplatné použití funkce - chybí parametr";
                die();
            }

            //Make SQL Insert
            $stmt = $conn->prepare("INSERT INTO company_days_teamPropaganda(name,description,date,active_since,active_until,registration_open,registration_close) VALUES (?,?,?,?,?,?,?)");
            if ($stmt->bind_param("sssssss", $_POST["name"], $_POST["description"], $_POST["date"], $_POST["active_since"], $_POST["active_until"], $_POST["registration_open"], $_POST["registration_close"]) && $stmt->execute() && $stmt->close()) {
                http_response_code(201);
                echo "Den firem vytvořen.";
                die();
            } else {
                http_response_code(400);
                echo "Den firem nemohl být vytvořen.";
                die();
            }
        } else if ($_POST["action"] == "delete") {
            //Check if values set
            if (!isset($_POST["id"])) {
                http_response_code(400);
                echo "Neplatné použití funkce - chybí parametr";
                die();
            }

            //Make SQL Delete
            $stmt = $conn->prepare("DELETE FROM company_days_teamPropaganda WHERE id_company_days=?");
            if ($stmt->bind_param("i", $_POST["id"]) && $stmt->execute() && $stmt->close()) {
                http_response_code(201);
                echo "Den firem odstraněn.";
                die();
            } else {
                http_response_code(400);
                echo "Den firem nemohl být odstraněna.";
                die();
            }
        } else {
            http_response_code(400);
            echo "Neplatné použití funkce - neplatná akce";
            die();
        }
    } catch (Exception $e) {
        exceptionHandler($e);
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
    <title>Den firem</title>
    <link rel="stylesheet" href="../formWebScripts/css/formStyle.css">
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body class="pageHolder">
    <header>
        <?php setupTitlebarAdmin($conn, "companyDay.php") ?>
    </header>
    <main>
        <?php
        $description = "-";
        $activeSinceDB = new DateTime("now", new DateTimeZone("Europe/Prague"))->format('Y-m-d H:i:s');
        $activeUntilDB = new DateTime("now", new DateTimeZone("Europe/Prague"))->format('Y-m-d H:i:s');
        $registrationOpenDB = new DateTime("now", new DateTimeZone("Europe/Prague"))->format('Y-m-d H:i:s');
        $registrationCloseDB = new DateTime("now", new DateTimeZone("Europe/Prague"))->format('Y-m-d H:i:s');
        $date = new DateTime("now", new DateTimeZone("Europe/Prague"))->format('Y-m-d');
        $exists = "true";
        if (isset($_GET["newCompanyDay"])) {
            echo "<h1>Vytvořit nový den firem</h1>";
            $exists = "false";
        } else {
            $stmt = $conn->prepare("SELECT name,date, description, active_since, active_until, registration_open, registration_close FROM company_days_teamPropaganda WHERE id_company_days = ?;");
            if (!$stmt->bind_param("i", $_GET["companyDay"]) || !$stmt->execute() || !$stmt->store_result() || $stmt->num_rows != 1 || !$stmt->bind_result($name, $date, $description, $activeSinceDB, $activeUntilDB, $registrationOpenDB, $registrationCloseDB) || !$stmt->fetch() || !$stmt->close()) {
                echo "<h1>Nelze získat informace o dni firem.</h1>";
                echo "<a href='./admin.php'><button class='purkynkaButton'>Zpět na hlavní stránku</button></a>";
                die();
            }
            echo "<h1>Informace o dni firem: $name</h1>";
        }
        $activeSince = DateTime::createFromFormat('Y-m-d H:i:s', $activeSinceDB)->format(JS_TIME_FORMAT);
        $activeUntil = DateTime::createFromFormat('Y-m-d H:i:s', $activeUntilDB)->format(JS_TIME_FORMAT);
        $registrationOpen = DateTime::createFromFormat('Y-m-d H:i:s', $registrationOpenDB)->format(JS_TIME_FORMAT);
        $registrationClose = DateTime::createFromFormat('Y-m-d H:i:s', $registrationCloseDB)->format(JS_TIME_FORMAT);
        //$isFunctionalString = $isFunctional == 1 ? "true" : "false";
        
        //Create HTML
        echo "<form-input label='Název události:' class='eventValidate' do-change-check='$exists' type='text' value-id='name' original-value='$name' value='$name' placeholder='$name'></form-input>";
        echo "<form-input label='Popis události:' class='eventValidate' do-change-check='$exists' type='textarea' value-id='description' original-value='$description' value='$description' placeholder='$description'></form-input>";
        echo "<form-input label='Datum konání události:' class='eventValidate' do-change-check='$exists' type='date' value-id='date'  id='date' original-value='$date' value='$date'></form-input>";
        echo "<form-input label='Událost aktivní od:' class='eventValidate' do-change-check='$exists' type='datetime-local' value-id='active_since' id='active_since' original-value='$activeSince' value='$activeSince'></form-input>";
        echo "<form-input label='Událost aktivní do:' class='eventValidate' do-change-check='$exists' type='datetime-local' value-id='active_until' id='active_until' original-value='$activeUntil' value='$activeUntil'></form-input>";
        echo "<form-input label='Registrace aktivní od:' class='eventValidate' do-change-check='$exists' type='datetime-local' value-id='registration_open'  id='registration_open' original-value='$registrationOpen' value='$registrationOpen'></form-input>";
        echo "<form-input label='Registrace aktivní do:' class='eventValidate' do-change-check='$exists' type='datetime-local' value-id='registration_close' id='registration_close' original-value='$registrationClose' value='$registrationClose'></form-input>";
        echo "<div class='formButtonBoxHolder'>";
        echo "<div class='formButtonBox'>";
        echo "<button exists='$exists' class='formButton purkynkaButton btnSave' form-icon='!save'></button>";
        echo "<button exists='$exists' class='formButton purkynkaButton btnCancel' form-icon='!dontSave'></button>";
        echo "<a href='./events.php'><button class='formButton purkynkaButton' form-icon='!listTable'><span>Zpět na seznam událostí</span></button></a>";
        echo "</div>";
        echo "</div>";
        ?>
    </main>
    <footer>

    </footer>
</body>
<script type="module" src="../formWebScripts/js/formScript.js"></script>
<script type='module' src='./companyDay.js'></script>

</html>
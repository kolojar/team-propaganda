<?php
session_start();
require "../assets/config.php";
if (isset($_POST["action"])) {
    if ($_POST["action"] == "update") {
        //Check if values set
        if (!isset($_POST["name"]) || !isset($_POST["placesToSit"]) || !isset($_POST["isFunctional"])|| !isset($_POST["note"]) || !isset($_POST["id"])) {
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
        if (!isset($_POST["name"]) || !isset($_POST["placesToSit"]) || !isset($_POST["isFunctional"])|| !isset($_POST["note"])) {
            http_response_code(400);
            echo "Invalid usage of function - missing table column parameters";
            die();
        }

        //Make SQL Insert
        $stmt = $conn->prepare("INSERT INTO classrooms(name,placesToSit,isFunctional,note) VALUES (name=?, placesToSit=?,isFunctional=?, note=?)");
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
    }
}
?>

<!DOCTYPE html>
<html lang="cs">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Učebna</title>
    <link rel="stylesheet" href="../formWebScripts/css/sharedStyle.css">
    <link rel="stylesheet" href="../formWebScripts/css/formStyle.css">
    <link rel="stylesheet" href="../formWebScripts/css/tableStyle.css">
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body>
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
        <?php
        $name = "";
        $placesToSit = 0;
        $isFunctional = 1;
        $isFunctionalString = $isFunctional == 1 ? "true" : "false";
        $note = "-";
        $exists = "true";
        if (isset($_GET["newClassroom"])) {
            echo "<h1>Vytvořit novou učebnu</h1>";
            $exists = "false";
        } else {
            $stmt = $conn->prepare("SELECT name, placesToSit, isFunctional, note FROM `classrooms` WHERE id_classrooms = ?;");
            $stmt->bind_param("i", $_GET["classroom"]);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($name, $placesToSit, $isFunctional, $note);
            echo "<h1>Informace o učebně: </h1>";
        }

        //Create HTML
        echo "<form-input label='Název učebny:' class='classroomValidate' do-change-check='$exists' type='text' id='name' original-value='$name' value='$name' placeholder='$name'></form-input>";
        echo "<br>";
        echo "<form-input label='Počet míst k sezení:'class='classroomValidate'  do-change-check='$exists' type='number' id='placesToSit' original-value='$placesToSit' value='$placesToSit' placeholder='$placesToSit'></form-input>";
        echo "<br><form-toggle labelBefore='Je učebna aktivní: ' class='classroomValidate' offColorClass='formErrorColor' onColorClass='formOkColor' original-value='$isFunctionalString' value='$isFunctionalString' id='isFunctional'></form-toggle><br>";
        echo "<form-input label='Poznámka:' class='classroomValidate' do-change-check='$exists' type='textarea' id='note' original-value='$note' value='$note' placeholder='$note'></form-input>";
        echo "<div class='formButtonBoxHolder'>";
        echo "<div class='formButtonBox'>";
        echo "<button id='btnSave' exists='$exists' class='formButton formOkColor'>Uložit změny</button>";
        echo "<button id='btnCancel' exists='$exists' class='formButton formErrorColor'>Zrušit změny</button>";
        echo "<a href='?view=classrooms'><button class='formButton formInfoColor'>Zpět na seznam učeben</button></a>";
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
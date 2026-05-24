<?php
session_start();
require "../assets/config.php";
require "./adminFunctions.php";

if (isset($_POST["action"])) {
    if ($_POST["action"] == "update") {
        //Check if values set
        if (!isset($_POST["name"]) || !isset($_POST["placesToSit"]) || !isset($_POST["note"]) || !isset($_POST["id"])) {
            http_response_code(400);
            echo "Neplatné použití funkce - chybí parametr";
            die();
        }

        //Make SQL Update
        $stmt = $conn->prepare("UPDATE classrooms_teamPropaganda SET name=?, places_to_sit=?, note=? WHERE id_classrooms=?");
        if ($stmt->bind_param("sisi", $_POST["name"], $_POST["placesToSit"], $_POST["note"], $_POST["id"]) && $stmt->execute() && $stmt->close()) {
            http_response_code(201);
            echo "Učebna upravena.";
            die();
        } else {
            http_response_code(400);
            echo "Učebna nemohla být upravena.";
            die();
        }
    } else if ($_POST["action"] == "insert") {
        //Check if values set
        if (!isset($_POST["name"]) || !isset($_POST["placesToSit"]) || !isset($_POST["note"])) {
            http_response_code(400);
            echo "Neplatné použití funkce - chybí parametr";
            die();
        }

        //Make SQL Insert
        $stmt = $conn->prepare("INSERT INTO classrooms_teamPropaganda(name,places_to_sit,note) VALUES (?,?,?)");
        if ($stmt->bind_param("sis", $_POST["name"], $_POST["placesToSit"], $_POST["note"]) && $stmt->execute() && $stmt->close()) {
            http_response_code(201);
            echo "Učebna vytvořena";
            die();
        } else {
            http_response_code(400);
            echo "Učebna nemohla být vytvořena.";
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
        $stmt = $conn->prepare("DELETE FROM classrooms_teamPropaganda WHERE id_classrooms=?");
        if ($stmt->bind_param("i", $_POST["id"]) && $stmt->execute() && $stmt->close()) {
            http_response_code(201);
            echo "Učebna odstraněna.";
            die();
        } else {
            http_response_code(400);
            echo "Učebna nemohla být odstraněna.";
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
    <title>Učebna</title>
    <link rel="stylesheet" href="../formWebScripts/css/formStyle.css">
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body class="pageHolder">
    <header>
        <?php setupTitlebarAdmin($conn, "classroom.php") ?>
    </header>
    <main>
        <?php
        $name = "";
        $placesToSit = 0;
        $isFunctional = 1;
        $note = "-";
        $exists = "true";
        if (isset($_GET["newClassroom"])) {
            echo "<h1>Vytvořit novou učebnu</h1>";
            $exists = "false";
        } else {
            $stmt = $conn->prepare("SELECT name, places_to_sit, note FROM `classrooms_teamPropaganda` WHERE id_classrooms = ?;");
            if (!$stmt->bind_param("i", $_GET["classroom"]) || !$stmt->execute() || !$stmt->store_result() || !$stmt->bind_result($name, $placesToSit,  $note) || !$stmt->fetch() || !$stmt->close()) {
                echo "<h1>Nelze získat informace o učebně.</h1>";
                echo "<a href='./admin.php'><button class='purkynkaButton'>Zpět na hlavní stránku</button></a>";
                die();
            }
            echo "<h1>Informace o učebně: $name</h1>";
        }
        $isFunctionalString = $isFunctional == 1 ? "true" : "false";

        //Create HTML
        echo "<form-input label='Název učebny:' class='classroomValidate' do-change-check='$exists' type='text' value-id='name' original-value='$name' value='$name' placeholder='$name'></form-input>";
        echo "<form-input label='Počet míst k sezení:' class='classroomValidate' do-change-check='$exists' type='number' value-id='placesToSit' original-value='$placesToSit' value='$placesToSit' placeholder='$placesToSit'></form-input>";
        echo "<form-input label='Poznámka:' class='classroomValidate' do-change-check='$exists' type='textarea' value-id='note' original-value='$note' value='$note' placeholder='$note'></form-input>";
        echo "<div class='formButtonBoxHolder'>";
        echo "<div class='formButtonBox'>";
        echo "<button exists='$exists' class='purkynkaButton btnSave' form-icon='!save'></button>";
        echo "<button exists='$exists' class='purkynkaButton btnCancel' form-icon='!dontSave'></button>";
        echo "<a href='./classrooms.php'><button class='formButton purkynkaButton' form-icon='!listTable'><span>Zpět na seznam učeben</span></button></a>";
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
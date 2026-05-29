<?php
session_start();
require "../assets/config.php";
require "./adminFunctions.php";

if (isset($_POST["action"])) {
    if ($_POST["action"] == "update") {
        //Check if values set
        if (!isset($_POST["name"]) || !isset($_POST["address"]) || !isset($_POST["id"])) {
            http_response_code(400);
            echo "Neplatné použití funkce - chybí parametr";
            die();
        }

        //Make SQL Update
        $stmt = $conn->prepare("UPDATE schools_teamPropaganda SET name=?, address=? WHERE id_schools=?");
        if ($stmt->bind_param("ssi", $_POST["name"], $_POST["address"], $_POST["id"]) && $stmt->execute() && $stmt->close()) {
            http_response_code(201);
            echo "Škola upravena.";
            die();
        } else {
            http_response_code(400);
            echo "Škola nemohla být upravena.";
            die();
        }
    } else if ($_POST["action"] == "insert") {
        //Check if values set
        if (!isset($_POST["name"]) || !isset($_POST["address"])) {
            http_response_code(400);
            echo "Neplatné použití funkce - chybí parametr";
            die();
        }

        //Make SQL Update
        $stmt = $conn->prepare("INSERT INTO schools_teamPropaganda(name,address) VALUES (?, ?)");
        if ($stmt->bind_param("ss", $_POST["name"], $_POST["address"]) && $stmt->execute() && $stmt->close()) {
            http_response_code(201);
            echo "Škola přidána.";
            die();
        } else {
            http_response_code(400);
            echo "Škola nemohla být přidána.";
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
        $stmt = $conn->prepare("DELETE FROM schools_teamPropaganda WHERE id_schools=?");
        if ($stmt->bind_param("i", $_POST["id"]) && $stmt->execute() && $stmt->close()) {
            http_response_code(201);
            echo "Škola odstraněna.";
            die();
        } else {
            http_response_code(400);
            echo "Škola nemohla být odstraněna.";
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
    <title>Informace o škole</title>
    <link rel="stylesheet" href="../formWebScripts/css/formStyle.css">
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body class="pageHolder">
    <header>
        <?php setupTitlebarAdmin($conn, "school.php") ?>
    </header>
    <main>
        <?php
        $name = "";
        $address = "";
        $exists = "true";
        if (isset($_GET["newSchool"])) {
            echo "<h1>Vytvořit novou školu</h1>";
            $exists = "false";
        } else {
            //Get school info
            $stmt = $conn->prepare("SELECT name,address FROM schools_teamPropaganda WHERE id_schools = ?");
            if (!$stmt->bind_param("i", $_GET["school"]) || !$stmt->execute() || !$stmt->store_result() || $stmt->num_rows != 1 || !$stmt->bind_result($name, $address) || !$stmt->fetch() || !$stmt->close()) {
                echo "<h1>Nelze získat informace o škole.</h1>";
                echo "<a href='./admin.php'><button class='purkynkaButton'>Zpět na hlavní stránku</button></a>";
                die();
            }
            echo "<h1>Informace o škole: $name → $address</h1>";
        }

        //Print HTML
        echo "<form-input label='Název:' class='validate' do-change-check='$exists' type='text' value-id='name' value='$name' original-value='$name' placeholder='$name'></form-input>";
        echo "<form-input label='Adresa:' class='validate' do-change-check='$exists' type='text' value-id='address' value='$address' original-value='$address' placeholder='$address'></form-input>";
        echo "<div class='formButtonBoxHolder'>";
        echo "<div class='formButtonBox'>";
        echo "<button exists='$exists' class='formButton purkynkaButton btnSave' form-icon='!save'></button>";
        echo "<button exists='$exists' class='formButton purkynkaButton btnCancel' form-icon='!dontSave'></button>";
        echo "<a href='./schools.php'><button class='formButton purkynkaButton' form-icon='!listTable'><span>Zpět na seznam škol</span></button></a>";
        echo "</div>";
        echo "</div>";
        ?>
    </main>
    <footer>

    </footer>
</body>
<script type="module" src="../formWebScripts/js/formScript.js"></script>
<script type='module' src='./school.js'></script>

</html>
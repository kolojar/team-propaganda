<?php
session_start();
require "../assets/config.php";
if (isset($_POST["action"])) {
    if ($_POST["action"] == "update") {
        //Check if values set
        if (!isset($_POST["name"]) || !isset($_POST["address"]) || !isset($_POST["id"])) {
            http_response_code(400);
            echo "Invalid usage of function - missing table column parameters";
            die();
        }

        //Make SQL Update
        $stmt = $conn->prepare("UPDATE schools SET name=?, address=? WHERE id_schools=?");
        $stmt->bind_param("ssi", $_POST["name"], $_POST["address"], $_POST["id"]);
        if ($stmt->execute()) {
            http_response_code(201);
            echo "Entry updated.";
            die();
        } else {
            http_response_code(400);
            echo "Entry could not be updated.";
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
    <title>Informace o škole</title>
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
        //Get school info
        $stmt = $conn->prepare("SELECT schools.name, schools.address FROM schools WHERE schools.id_schools = ? LIMIT 1");
        $stmt->bind_param("i", $_GET["school"]);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($name, $address);
        $stmt->fetch();

        //Print HTML
        echo "<h1>Informace o škole: $name → $address</h1>";
        echo "<form-input label='Název:' style='width: 100%' class='schoolValidate'  do-change-check='true' type='text' id='schoolName' value='$name' original-value='$name' placeholder='$name'></form-input>";
        echo "<br>";
        echo "<form-input label='Adresa:' style='width: 100%' class='schoolValidate'  do-change-check='true' type='text' id='schoolAddress' value='$address' original-value='$address' placeholder='$address'></form-input>";
        echo "<div class='formButtonBoxHolder'>";
        echo "<div class='formButtonBox'>";
        echo "<button id='schoolBtnSave' class='formButton formOkColor'>Uložit změny</button>";
        echo "<button id='schoolBtnCancel' class='formButton formErrorColor'>Zrušit změny</button>";
        echo "<a href='./schools.php'><button class='formButton formInfoColor'>Zpět na seznam škol</button></a>";
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
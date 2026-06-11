<?php
session_start();
require "../assets/config.php";
/** @var mysqli $conn */
require "./adminFunctions.php";

if (isset($_POST["action"])) {
    if ($_POST["action"] == "update") {
        //Check if values set
        if (!isset($_POST["name"]) || !isset($_FILES["icon"]) || !isset($_POST["short_info"]) || !isset($_POST["long_info"]) || !isset($_POST["id"])) {
            http_response_code(400);
            echo "Neplatné použití funkce - chybí parametr";
            die();
        }
        if ($_FILES["icon"]["error"] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo "Neplatné použití funkce - chybí soubor";
            die();
        }

        //Make SQL Update
        logToConsole($_FILES["icon"]["tmp_name"]);
        $fileData = file_get_contents($_FILES["icon"]["tmp_name"]);
        if ($fileData === false) {
            http_response_code(400);
            echo "Neplatné použití funkce - chybí soubor";
            die();
        }
        if (empty($fileData)) {
            http_response_code(400);
            echo "Soubor je prázdný.";
            die();
        }
        $stmt = $conn->prepare("UPDATE companies_teamPropaganda SET name=?, icon=?,short_info = ?, long_info=? WHERE id_companies=?");
        if ($stmt->bind_param("sbssi", $_POST["name"], $null, $_POST["short_info"], $_POST["long_info"], $_POST["id"]) && $stmt->send_long_data(1, $fileData) && $stmt->execute() && $stmt->close()) {
            http_response_code(201);
            echo "Firma upravena.";
            die();
        } else {
            http_response_code(400);
            echo "Firma nemohla být upravena.";
            die();
        }
    } elseif ($_POST["action"] == "delete") {
        //Check if values set
        if (!isset($_POST["id"])) {
            http_response_code(400);
            echo "Neplatné použití funkce - chybí parametr";
            die();
        }

        //Make SQL Update
        $stmt = $conn->prepare("DELETE FROM companies_teamPropaganda WHERE id_companies=?");
        if ($stmt->bind_param("i", $_POST["id"]) && $stmt->execute() && $stmt->close()) {
            http_response_code(201);
            echo "Firma odstraněna.";
            die();
        } else {
            http_response_code(400);
            echo "Firma nemohla být odstraněna.";
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
    <title>Firma</title>

    <link rel="stylesheet" href="../formWebScripts/css/formStyle.css">
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body class="pageHolder">
    <header>
        <?php setupTitlebarAdmin($conn, "company.php"); ?>
    </header>
    <main>
        <?php
        //Get attendant info
        $stmt = $conn->prepare("SELECT c.name, c.icon, c.short_info, c.long_info, c.id_users, u.name, u.surname, u.email FROM companies_teamPropaganda c JOIN users_teamPropaganda u ON c.id_users = u.id_users WHERE c.id_companies = ?");
        if (!$stmt->bind_param("i", $_GET["company"]) || !$stmt->execute() || !$stmt->store_result() || $stmt->num_rows != 1 || !$stmt->bind_result($name, $icon, $shortInfo, $longInfo, $contactId, $contactName, $contactSurname, $contactEmail) || !$stmt->fetch() || !$stmt->close()) {
            echo "<h1>Nelze získat informace o firmě.</h1>";
            echo "<a href='./admin.php'><button class='purkynkaButton'>Zpět na hlavní stránku</button></a>";
            die();
        }

        //Print HTML
        echo "<h1>Informace o firmě: $name</h1>";
        echo "<form-input tabindex='1' icon='!companyName' label='Název firmy:' class='validate' do-change-check type='text' value-id='name' original-value='$name' value='$name' placeholder='$name'></form-input>";
        echo "<form-input tabindex='2' type='file' icon='!image' label='Logo:' class='validate' do-change-check value-id='icon' original-value='' allow-empty-file></form-input>";
        echo '<img style="height: 100px" class="icon" src="data:image/jpeg;base64,' . base64_encode($icon) . '" >';
        //echo "<form-input label='Email:' class='attendantValidate' do-change-check type='email' id='email' original-value='$email' value='$email' placeholder='$email'></form-input>";
        echo "<p>Zástupce firmy zástupce: $contactName $contactSurname</p>";
        echo "<p>Email zástupce firmy: <a href='./sendMail.php?uid=$contactId&isNILE=+'>$contactEmail</a></p>";
        //echo "<p>Základní škola: <a id='schoolIdHolder' schoolId='$schoolId' href='?view=school&school=$schoolId'>$schoolName → $schoolAddress</a> <button class='formButton formWarnColor' id='attendantBtnChangeSchool'>Změnit školu</button></p>";
        echo "<form-input tabindex='3' icon='!companyDescriptionShort' label='Krátký popis:' class='validate' type='text' do-change-check value-id='short_info' original-value='$shortInfo' value='$shortInfo'></form-input>";
        echo "<form-input tabindex='4' icon='!companyDescriptionLong' label='Dlouhý popis:' class='validate' type='textarea' do-change-check value-id='long_info' original-value='$longInfo' value='$longInfo'></form-input>";
        echo "<div class='formButtonBoxHolder'>";
        echo "<div class='formButtonBox'>";
        echo "<button tabindex='5' class='formButton purkynkaButton btnSave' form-icon='!save'></button>";
        echo "<button tabindex='6' class='formButton purkynkaButton btnCancel' form-icon='!dontSave'></button>";
        echo "<a tabindex='-1' href='./companies.php'><button tabindex='7' class='formButton purkynkaButton' form-icon='!listTable'><span>Zpět na seznam firem</span></button></a>";
        echo "<a tabindex='-1' href='./user.php?user=$contactId'><button tabindex='8' class='formButton purkynkaButton' form-icon='!parentInfo'><span>Zobrazit informace o zástupci firmy</span></button></a>";
        echo "</div>";
        echo "</div>";
        ?>
    </main>
    <footer>

    </footer>
</body>
<script type="module" src="../formWebScripts/js/formScript.js"></script>
<script type='module' src='./company.js'></script>

</html>

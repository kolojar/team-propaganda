<?php
session_start();
$_SESSION["userId"] = 7;
if (!isset($_SESSION["userId"])) {
    header("Location: ./");
    exit();
}
require '../assets/config.php';
require './userFunctions.php';
require '../assets/sharedFunctions.php';
?>
<!DOCTYPE html>
<html lang='cz'>

<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Uživatelský panel</title>
    <link rel='stylesheet' href='../formWebScripts/css/sharedStyle.css'>
    <link rel='stylesheet' href='../formWebScripts/css/formStyle.css'>
    <link rel='stylesheet' href='../assets/style.css'>
    <link rel='stylesheet' href='./user.css'>
</head>

<body class="pageHolder">
    <header>
        <?php
        setupTitlebarUser($conn)
        ?>
    </header>
    <main>
        <fieldset id="userInfo">
            <legend>Informace o Vás</legend>
            <?php
            //Get name of current user
            $stmt = $conn->prepare("SELECT name, surname, email, isNILE FROM users_teamPropaganda WHERE id_users=?");
            $stmt->bind_param("i", $_SESSION["userId"]);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($name, $surname, $email, $isNILE);
            $stmt->fetch();

            echo "<form-input class='validate' value-id='name' label='Jméno:' type='text' do-change-check='true' value='$name' original-value='$name'></form-input>";
            echo "<form-input class='validate' value-id='surname' label='Přijmení:' type='text' do-change-check='true' value='$surname' original-value='$surname'></form-input>";
            echo "<span>Email: $email</span>"
            ?>
            <div class='formButtonBoxHolder'>
                <div class='formButtonBox formJustifyLeft'>
                    <button class='formButton purkynkaButton' id='btnChangeEmail'>Převést účet na jiný Email</button>
                </div>
                <div class='formButtonBox formJustifyRight'>
                    <button class='formButton purkynkaButton btnCancel'>Zrušit provedené změny</button>
                    <button class='formButton purkynkaButton btnSave'>Uložit změny</button>
                </div>
            </div>
        </fieldset>

        <?php
        if ($isNILE == 0) {
            //Get attendants of current user
            $stmt = $conn->prepare("SELECT a.id_attendants, a.name, a.surname, a.id_schools, s.name, s.address FROM attendants_teamPropaganda a JOIN schools_teamPropaganda s ON  a.id_schools = s.id_schools WHERE a.id_parent = ?;");
            $stmt->bind_param("i", $_SESSION["userId"]);
            $stmt->execute();
            $stmt->store_result();
            for ($i = 0; $i < $stmt->num_rows; $i++) {
                $stmt->bind_result($id, $name, $surname, $schoolId, $schoolName, $schoolAddress);
                $stmt->fetch();
                echo "<br>
                <fieldset class='attendantInfo' attendant='$id'>
                <legend>Informace o zájemci: $name $surname</legend>
                <form-input value-id='name' label='Jméno:' class='validate' type='text' do-change-check='true' value='$name' original-value='$name'></form-input>
                <form-input value-id='surname' label='Přijmení:' class='validate' type='text' do-change-check='true' value='$surname' original-value='$surname'></form-input>
                <form-input value-id='school' label='Základní škola:' class='validate schoolValue' type='select' do-change-check='true' original-value='$schoolName → $schoolAddress' value='$schoolName → $schoolAddress' is-case-sensitive-list='false'></form-input>";

                //Get events of attendant
                $stmt2 = $conn->prepare("SELECT ra.variable_symbol, ra.id_events, ra.paid, e.name, e.price FROM registered_attendants_teamPropaganda ra JOIN events_teamPropaganda e ON ra.id_events = e.id_events WHERE ra.id_attendants = ?;");
                $stmt2->bind_param("i", $id);
                $stmt2->execute();
                $stmt2->store_result();
                if ($stmt2->num_rows > 0) {
                    echo "<span>Přihlášené akce - kliknutím na modrý název zobrazíte podrobnosti:</span><ul>";
                    for ($j = 0; $j < $stmt2->num_rows; $j++) {
                        $stmt2->bind_result($variableSymbol, $eventId, $paid, $eventName, $price);
                        $stmt2->fetch();
                        echo "<li><a href='./event.php?variableSymbol=$variableSymbol'>$eventName</a>";
                        if ($paid == null) {
                            echo "<span> → Potřeba uhradit poplatek!</span>";
                        }
                        echo "</li>";
                    }
                    echo "</ul>";
                } else {
                    echo "<span>Zájemce není přihlášen na žádnou akci.</span>";
                }

                //Buttons
                echo "<div class='formButtonBoxHolder'>
                <div class='formButtonBox formJustifyLeft'>
                    <button class='formButton purkynkaButton'>Přihlásit na další akce</button>
                    <button class='formButton purkynkaButton btnDeleteAttendant' attendant='$id'>Odebrat zájemce</button>
                </div>
                <div class='formButtonBox formJustifyRight'>
                    <button class='formButton purkynkaButton btnCancel'>Zrušit provedené změny</button>
                    <button class='formButton purkynkaButton btnSave'>Uložit změny</button>
                </div>
                </div>
                </fieldset>";
            }
        } elseif ($isNILE == 1) {
            //Get companies of current user
            $stmt = $conn->prepare("SELECT * FROM companies_teamPropaganda WHERE id_users = ?");
            $stmt->bind_param("i", $_SESSION["userId"]);
            $stmt->execute();
            $res = $stmt->get_result();
            $stmt->close();
            if ($res->num_rows > 0) {
                while ($row = $res->fetch_assoc()) {
                    echo "<br>
                <fieldset class='companyInfo' company='" . $row["id_companies"] . "'>
                <legend>Informace o firmě: " . $row["name"] . ((isset($row["icon"])) ? " </legend><img style='width: 4vw; height: 4vw;' src='data:image/jpeg;base64," . base64_encode($row["icon"]) . "'>" : "</legend>") . "
                <button class='formButton purkynkaButton' id='icon' company=" . $row["id_companies"] . ">Přidat/změnit logo firmy.</button>
                <span>Musí být v poměru 1:1. Maximální velikost souboru: 16MB</span>
                <form-input value-id='name' label='Jméno:' class='validate' type='text' do-change-check='true' value='" . $row["name"] . "' original-value='" . $row["name"] . "'></form-input>
                <form-input value-id='short_info' label='Krátký popis:' class='validate' type='text' do-change-check='true' min-len='300' value='" . $row["short_info"] . "' original-value='" . $row["short_info"] . "'></form-input>
                <form-input value-id='long_info' label='Dlouhý popis:' class='validate' type='text' do-change-check='true' value='" . $row["long_info"] . "' original-value='" . $row["long_info"] . "'></form-input>";

                    //Get events of attendant

                    $comp = $conn->query("SELECT * FROM `company_days_companies_teamPropaganda` NATURAL JOIN company_days_teamPropaganda WHERE id_companies = " . $row["id_companies"]);
                    if ($comp->num_rows > 0) {
                        echo "<span>Data dní firem - kliknutím na modrý název zobrazíte podrobnosti:</span><ul>";
                        while ($cd = $comp->fetch_assoc()) {
                            echo "<li><a href='./event.php?cd=" . $cd["id_company_days"] . "'>" . $date = new DateTime($cd["date"])->format(STANDARD_CZECH_DATE_FORMAT_FULL);
                            echo " → " . $cd["description"] . "</a>";
                            echo "</li>";
                        }
                        echo "</ul>";
                    } else {
                        echo "<span>Nejste přihlášen na žádnou akci.</span>";
                    }

                    //Buttons
                    echo "<div class='formButtonBoxHolder'>
                <div class='formButtonBox formJustifyLeft'>
                    <button class='formButton purkynkaButton' id='addNew' comp=" . $row["id_companies"] . ">Přihlásit na další akce</button>
                </div>
                <div class='formButtonBox formJustifyRight'>
                    <button class='formButton purkynkaButton btnCancel'>Zrušit provedené změny</button>
                    <button class='formButton purkynkaButton btnSave'>Uložit změny</button>
                </div>
                </div>
                </fieldset>";
                }
            }
        }
        ?>
    </main>
    <script type='module' src='../formWebScripts/js/formScript.js'></script>
    <script src='./main.js' type='module'></script>
</body>

</html>

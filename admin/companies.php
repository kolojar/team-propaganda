<?php
session_start();
require "../assets/config.php";
require "./adminFunctions.php";
?>

<!DOCTYPE html>
<html lang="cs">

<head>
    <meta charset="UTF-8">
    <meta name="form-icons-main-db" content="../formWebScripts/formIcons.json">
    <meta name="form-icons-db" content="../assets/formIcons.json">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="form-locales-main" content="../formWebScripts/locales/">
    <title>Firmy</title>
    <link rel="stylesheet" href="../formWebScripts/css/formStyle.css">
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body class="pageHolder">
    <header>
        <?php $result = setupTitlebarAdmin($conn, "companies.php"); ?>
    </header>
    <main>
        <?php
        function userEmail($result, $setup)
        {
            $email = $result["email"];
            $uid = $result["id_parent"];
            if($email == "") {
                return "Není k dispozici";
            }
            return "<a href='./sendMail.php?uid=$uid&isNILE=1'>$email</a>";
        }
        function actions($result, $setup)
        {
            $attendantId = $result["id_attendants"];
            $variableSymbol = $result["id_registered_attendants"];
            return "<a href='./attendant.php?attendant=$attendantId'><button class='formButton formButtonInline purkynkaButton' form-icon='!edit'></button></a><button class='formButton btnUnregisterTable formButtonInline purkynkaButton' variableSymbol='$variableSymbol' form-icon='!removePerson'></button>";
        }
        $resultEventId = $result->eventId;
        $resultSubeventId = $result->subeventId;

        //Get classrooms for subevent
        $stmt = $conn->prepare("SELECT cs.id_classrooms, c.name FROM classrooms_subevents_teamPropaganda cs JOIN classrooms_teamPropaganda c ON cs.id_classrooms = c.id_classrooms WHERE cs.id_subevents = ?;");
        if (!$stmt->bind_param("i", $resultSubeventId) || !$stmt->execute() || !$stmt->store_result()) {
            $stmt->close();
            echo "<h1>Nelze získat informace o učebnách.</h1>";
            die();
        }
        echo "<datalist id='classrooms'>";
        echo "<option label='Žádná' value='NULL'></option>";
        for ($i = 0; $i < $stmt->num_rows; $i++) {
            $stmt->bind_result($idClassroom, $classroomName);
            $stmt->fetch();
            echo "<option label='$classroomName' value='$idClassroom'></option>";
        }
        echo "</datalist>";

        //Echo registered attendants
        echo "<h1>Firmy</h1>";
        setupFilteredTable(
            $conn,
            $result,
            "purkynkaTableStripped purkynkaTableFullLines",
            "c.id_companies, c.name, c.icon, c.short_info, c.long_info, CONCAT(u.name, ' ', u.surname) AS uFullName, (SELECT GROUP_CONCAT(CONCAT(cf.id_fields, ',',f.name, ' (', f.short,')')) FROM companies_fields_teamPropaganda cf JOIN fields_teamPropaganda f ON cf.id_fields = f.id_fields WHERE cf.id_companies = c.id_companies) AS wanted,GROUP_CONCAT(cdc.id_company_days) visitedDaysIds, GROUP_CONCAT(cd.name) visitedDaysNames",
            "companies_teamPropaganda c JOIN users_teamPropaganda u ON c.id_users = u.id_users LEFT JOIN company_days_companies_teamPropaganda cdc ON cdc.id_companies = c.id_companies LEFT JOIN company_days_teamPropaganda cd ON cd.id_company_days = cdc.id_company_days",
            "(? IS NULL OR cdc.id_company_days = ?)",
            "c.id_companies;",
            "",
            "",
            "ii",
            [$result->companyDayId, $result->companyDayId],
            [
                new filterSelector("cName", "Název společnosti", "cName", filterSelectorType::TEXT, filterCompareOperator::LIKE, true),
                new filterSelector("uFullName", "Zástupce firmy", "uFullName", filterSelectorType::TEXT, filterCompareOperator::LIKE, true),
                new filterSelector("email", "Email zástupce firmy", "email", filterSelectorType::TEXT, filterCompareOperator::LIKE),
                new filterSelector("c.short_info", "Krátký popis", "shortInfo", filterSelectorType::TEXT, filterCompareOperator::LIKE, false),
                new filterSelector("c.long_info", "Dlouhý popis", "longInfo", filterSelectorType::TEXTAREA, filterCompareOperator::LIKE,false),
                new filterSelector("wanted", "Zájem o obor", "wanted", filterSelectorType::SELECT, filterCompareOperator::IN,true),
                new filterSelector("event", "Den firem", "cdc.id_company_days", filterSelectorType::SELECT, filterCompareOperator::IN,true,["listId" => "daysList"]),
            ],
            [
                new filterDisplayer("!actions", "Akce", true),
                new filterDisplayer("cName", "Název společnosti", true),
                new filterDisplayer("uFullName", "Zástupce firmy", true),
                new filterDisplayer("!userEmail", "Email zástupce firmy", true),
                new filterDisplayer("short_info", "Krátký popis", true),
                new filterDisplayer("long_info", "Dlouhý popis", false, filterSelectorType::TEXTAREA),
                new filterDisplayer("wantedNames", "Zájem o obory", true,filterSelectorType::SELECT),
            ]
        );
        ?>
    </main>
    <footer>
        <div class="formButtonBoxHolder">
        <a href="./unregisteredAttendants.php"><button class="purkynkaButton">Odhlášení zájemci</button></a>
        </div>
    </footer>
</body>
<script type="module" src="../formWebScripts/js/formScript.js"></script>
<script type='module' src='../assets/sharedScripts.js'></script>
<script type='module' src='./attendants.js'></script>

</html>
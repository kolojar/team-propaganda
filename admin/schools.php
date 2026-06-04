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
    <title>Seznam škol</title>

    <link rel="stylesheet" href="../formWebScripts/css/formStyle.css">
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body class="pageHolder">
    <header>
        <?php $result = setupTitlebarAdmin($conn, "schools.php") ?>
    </header>
    <main>
        <h1>Seznam škol</h1>
        <i>Tip: Pro filtrování škol na určitou událost otevřte pohled pomocí správy událostí.</i>
        <?php
        function putFirstCell($result, $setup)
        {
            $id = $result["id_schools"];
            $res = "<a href='./school.php?school=$id'><button class='formButton formButtonInline purkynkaButton' form-icon='!edit'></button></a>";
            $res .= "<a href='./attendants.php?school=$id'><button class='formButton formButtonInline purkynkaButton' form-icon='!highlightUsers'></button></a>";
            return $res;
        }

        setupFilteredTable(
            $conn,
            null,
            "purkynkaTableStripped purkynkaTableFullLines",
            "s.id_schools, s.name, s.address as address, COUNT(a.id_attendants) as cnt",
            "attendants_teamPropaganda a RIGHT JOIN registered_attendants_teamPropaganda ra ON a.id_attendants = ra.id_attendants RIGHT JOIN schools_teamPropaganda s ON s.id_schools = a.id_schools",
            "(? IS NULL OR ra.id_events = ?)",
            "s.id_schools",
            "",
            "",
            "ii",
            [$result->eventId, $result->eventId],
            [
                new filterSelector("s.name", "Název", "name", filterSelectorType::TEXT, filterCompareOperator::LIKE),
                new filterSelector("s.address", "Adresa", "address", filterSelectorType::TEXT, filterCompareOperator::LIKE),
                new filterSelector("cnt", "Počet zájemců", "count", filterSelectorType::NUMBER, filterCompareOperator::EQUALS, true, ["min" => 0]),
                new filterSelector("cnt", "Minimální počet zájemců", "countmin", filterSelectorType::NUMBER, filterCompareOperator::MOREEQUALS, true, ["min" => 0]),
                new filterSelector("cnt", "Maximální počet zájemců", "countmax", filterSelectorType::NUMBER, filterCompareOperator::LESSEQUALS, true, ["min" => 0]),
            ],
            [
                new filterDisplayer("!putFirstCell", "Akce", true),
                new filterDisplayer("name", "Název", true),
                new filterDisplayer("address", "Adresa", true),
                new filterDisplayer("cnt", "Počet zájemcu", true, filterSelectorType::NUMBER)
            ]
        );
        ?>
    </main>
    <footer>
        <div class="formButtonBoxHolder">
            <a href="./school.php?newSchool=1"><button class="formButton purkynkaButton" form-icon='!add'><span>Přidat novou školu</span></button></a>
        </div>
    </footer>
</body>
<script type="module" src="../formWebScripts/js/formScript.js"></script>

</html>
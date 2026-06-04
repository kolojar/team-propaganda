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
        <?php setupTitlebarAdmin($conn, "schoolsAll.php") ?>
    </header>
    <main>
        <?php
        function putFirstCell($result) {
            $id = $result["id_schools"];
            $count = $result["cnt"];
            $res =  "<a href='./school.php?school=$id'><button class='formButton formButtonInline purkynkaButton' form-icon='!edit'></button></a>";
            if ($count != "0") {
                $res .= "<a href='./attendants.php?school=$id'><button class='formButton formButtonInline purkynkaButton' form-icon='!highlightUsers'></button></a>";
            } else {
                $res .= "<button class='formButton formButtonInline purkynkaButton btnTableDelete' school='$id' form-icon='!delete'></button>";
            }
            return $res;
        }
        function test($result) {
            $id = $result["id_schools"];
            return $id;
        }

        echo "<h1>Seznam všech škol</h1>";
        setupFilteredTable($conn,
        null,
        "purkynkaTableStripped purkynkaTableFullLines",
        "s.id_schools as id_schools, s.name as sname, s.address as address, COUNT(a.id_attendants) AS cnt, GROUP_CONCAT(a.id_attendants)",
        "attendants_teamPropaganda a RIGHT JOIN schools_teamPropaganda s ON s.id_schools = a.id_attendants",
        "",
        "id_schools",
        "",
        "",
        "",
        [],
        [
        new filterSelector("s.name","Název","name",filterSelectorType::TEXT,filterCompareOperator::LIKE),
        new filterSelector("s.address","Adresa","address",filterSelectorType::TEXT,filterCompareOperator::LIKE),
        new filterSelector("cnt","Počet zájemců","count",filterSelectorType::NUMBER,filterCompareOperator::EQUALS,true,["min"=>0]),
        new filterSelector("cnt","Minimální počet zájemců","countmin",filterSelectorType::NUMBER,filterCompareOperator::MOREEQUALS, true,["min"=>0]),
        new filterSelector("cnt","Maximální počet zájemců","countmax",filterSelectorType::NUMBER,filterCompareOperator::LESSEQUALS, true,["min"=>0]),
        ],[
            new filterDisplayer("!putFirstCell","Akce",true),
            new filterDisplayer("sname","Název",true),
            new filterDisplayer("address","Adresa",true),
            new filterDisplayer("cnt","Počet zájemcu",true,filterSelectorType::NUMBER)
        ])
            ?>
    </main>
    <footer>

    </footer>
</body>
<script type="module" src="../formWebScripts/js/formScript.js"></script>
<script type="module" src="./schoolsAll.js"></script>
</html>
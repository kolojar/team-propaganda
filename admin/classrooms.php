<?php
session_start();
require "../assets/config.php";
require "./adminFunctions.php";

if (isset($_POST["action"])) {
    if ($_POST["action"] == "getFunctionalClassrooms") {
        //Make SQL Select
        $stmt = $conn->prepare("SELECT id_classrooms, name, places_to_sit FROM classrooms_teamPropaganda;");
        if (!$stmt->execute() || !$stmt->store_result()) {
            http_response_code(400);
            echo "Nelze získat informace o učebnách.";
            die();
        }

        //Fetch all classrooms
        $jsonRecords = [];
        for ($i = 0; $i < $stmt->num_rows; $i++) {
            if ($stmt->bind_result($id, $name, $placesToSit) && $stmt->fetch()) {
                $jsonRecords[] = [
                    "id" => $id,
                    "name" => $name,
                    "placesToSit" => $placesToSit,
                ];
            }
        }

        //Generate JSON
        $stmt->close();
        http_response_code(201);
        echo json_encode($jsonRecords);
        die();
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
    <title>Admin panel</title>

    <link rel="stylesheet" href="../formWebScripts/css/formStyle.css">
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body class="pageHolder">
    <header>
        <?php setupTitlebarAdmin($conn, "classrooms.php") ?>
    </header>
    <main>
        <?php
        function actionButtons($result, $setup) {
            $id = $result["id_classrooms"];
            return "<a tabindex='-1' href='./classroom.php?classroom=$id'><button form-icon='!edit' class='purkynkaButton'></button></a><button form-icon='!delete' class='purkynkaButton btnTableDelete' classroom='$id'></button>";
        }

        setupFilteredTable(
            $conn,
            null,
            "purkynkaTableStripped purkynkaTableFullLines",
            "id_classrooms, name, places_to_sit, note",
            "classrooms_teamPropaganda",
            "",
            "",
            "",
            "",
            "",
            [],
            [
                new filterSelector("name", "Název učebny", "name", filterSelectorType::TEXT, filterCompareOperator::LIKE, false),
                new filterSelector("places_to_sit", "Počet míst k sezení", "placesToSit", filterSelectorType::NUMBER, filterCompareOperator::EQUALS, false),
                new filterSelector("places_to_sit", "Minimální počet míst k sezení", "placesToSitMin", filterSelectorType::NUMBER, filterCompareOperator::MOREEQUALS, false),
                new filterSelector("places_to_sit", "Maximální počet míst k sezení", "placesToSitMax", filterSelectorType::NUMBER, filterCompareOperator::LESSEQUALS, false),
            ],
            [
                new filterDisplayer("!actionButtons", "Akce", true,filterSelectorType::TEXT,'formButtonBoxTable'),
                new filterDisplayer("name", "Název učebny", true),
                new filterDisplayer("places_to_sit", "Počet míst k sezení", true),
                new filterDisplayer("note", "Poznámka", true),
            ]
        );
        ?>
        <a tabindex='-1' href='./classroom.php?newClassroom=1'><button tabindex='1' class='formButton purkynkaButton' form-icon="!add"><span>Vytvořit učebnu</span></button></a>
    </main>
    <footer>

    </footer>
</body>
<script type="module" src="../formWebScripts/js/formScript.js"></script>
<script type="module" src="./classrooms.js"></script>

</html>
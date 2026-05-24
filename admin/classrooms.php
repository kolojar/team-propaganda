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
            $stmt->bind_result($id, $name, $placesToSit);
            $stmt->fetch();
            $jsonRecords[] = [
                "id" => $id,
                "name" => $name,
                "placesToSit" => $placesToSit,
            ];
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
        //Request classrooms
        $stmt = $conn->prepare("SELECT id_classrooms, name,places_to_sit,  note FROM classrooms_teamPropaganda");
        if (!$stmt->execute() || !$stmt->store_result()) {
            echo "<h1>Nelze získat seznam učeben.</h1>";
            echo "<a href='./admin.php'><button class='purkynkaButton'>Zpět na hlavní stránku</button></a>";
            $stmt->free_result();
            die();
        }

        //Generate HTML
        echo "<h1>Všechny dostupné učebny v databázi</h1>";
        echo "<table>";
        echo "<tr>";
        echo "<th>Akce</th>";
        echo "<th>Název učebny</th>";
        echo "<th>Počet míst k sezení</th>";
        echo "<th>Poznámka</th>";
        echo "</tr>";

        //List all classrooms in table
        for ($i = 0; $i < $stmt->num_rows; $i++) {
            $stmt->bind_result($id, $name, $placesToSit, $note);
            $stmt->fetch();
            echo "<tr class='clickHighlightRow'>
                        <td class='formButtonBoxTable'>
                            <a href='./classroom.php?classroom=$id'><button form-icon='!edit' class='purkynkaButton'></button></a><button form-icon='!delete' class='purkynkaButton btnTableDelete' classroom='$id'></button>
                        </td>
                        <td>$name</td>
                        <td>$placesToSit</td>
                        <td>$note</td>
                    </tr>";
        }
        $stmt->free_result();
        ?>
        </table>
        <a href='./classroom.php?newClassroom=1'><button class='formButton purkynkaButton' form-icon="!add"><span>Vytvořit učebnu</span></button></a>
    </main>
    <footer>

    </footer>
</body>
<script type="module" src="../formWebScripts/js/formScript.js"></script>
<script type="module" src="./classrooms.js"></script>

</html>
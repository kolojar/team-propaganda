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
    <title>Seznam škol</title>
    <link rel="stylesheet" href="../formWebScripts/css/sharedStyle.css">
    <link rel="stylesheet" href="../formWebScripts/css/formStyle.css">
    <link rel="stylesheet" href="../formWebScripts/css/tableStyle.css">
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body class="pageHolder">
    <header>
        <?php setupTitlebarAdmin($conn, "schoolsAll.php") ?>
    </header>
    <main>
        <h1>Seznam všech škol</h1>
        <a href="./school.php?newSchool=1"><button class="purkynkaButton" form-icon="!add"><span>Přidat novou školu</span></button></a><br>
        <?php
        //$itemsPerPage = isset($_GET["itemsPerPage"]) ? $_GET["itemsPerPage"] : 10;
        //$page = isset($_GET["page"]) ? $_GET["page"] * $itemsPerPage : 0;
        //echo "<form-input label='Číslo stránky:' type='number' id='pageNumber' value='$page'></form-input>";
        //echo "<form-input label='Počet položek na stránku:' type='number' id='itemsPerPage' value='$itemsPerPage'></form-input>";
        //echo "<br>";
        //echo "<span>Posun stránek: </span>";
        //echo "<button id='btnPagePrev' class='formButton formInfoColor' " . (isset($_GET["page"]) ? ($_GET["page"] == 0 ? "disabled" : "") : "disabled") . ">⬅</button>";
        //echo "<button id='btnPageNext' class='formButton formInfoColor'>➡</button>";
        //echo "<button id='btnPageShow' class='formButton formInfoColor'>Zobrazit</button>";
        ?>
        <i>Poznámka: Nekteré školy není možné smazat, jelikož mají nahlášené zájemce.</i>
        <table>
            <tr>
                <th>Akce</th>
                <th>Počet zájemců</th>
                <th>Název</th>
                <th>Adresa</th>
            </tr>
            <?php
            //Request schools with student
            $stmt = $conn->prepare("SELECT s.id_schools, s.name, s.address, COUNT(a.id_attendants), GROUP_CONCAT(a.id_attendants) FROM attendants_teamPropaganda a RIGHT JOIN schools_teamPropaganda s ON s.id_schools = a.id_attendants GROUP BY s.id_schools;");
            //$stmt->bind_param("ii", $page, $itemsPerPage);
            $stmt->execute();
            $stmt->store_result();

            //List all schools with students in table
            for ($i = 0; $i < $stmt->num_rows; $i++) {
                $stmt->bind_result($id, $name, $address, $count, $users);
                $stmt->fetch();
                echo "<tr class='clickHighlightRow'>
                        <td class='formButtonBoxTable'>
                            <a href='./school.php?school=$id'><button class='formButton formButtonInline purkynkaButton' form-icon='!edit'></button></a> ";
                if ($count > 0) {
                    echo "<a href='./attendants.php?school=$id'><button class='formButton formButtonInline purkynkaButton' form-icon='!highlightUsers'></button></a>";
                } else {
                    echo "<button class='formButton formButtonInline purkynkaButton btnTableDelete' school=$id form-icon='!delete'></button>";
                }
                echo "</td>
                        <td>$count</td>
                        <td>$name</td>
                        <td>$address</td>
                    </tr>";
            }
            ?>
        </table>
    </main>
    <footer>

    </footer>
</body>
<script type="module" src="../formWebScripts/js/formScript.js"></script>
<script type="module" src="./schoolsAll.js"></script>

</html>
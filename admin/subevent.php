<?php
session_start();
require "../assets/config.php";
require "./adminFunctions.php";

class variableSymbolInfo
{
    public int $variableSymbol;
    public int $schoolId;
    public bool $isInTable;

    public function __construct(int $variableSymbol, int $schoolId, bool $isInTable)
    {
        $this->variableSymbol = $variableSymbol;
        $this->isInTable = $isInTable;
        $this->schoolId = $schoolId;
    }
}

class classroomInfo
{
    public int $classroomId;
    public int $placesToSit;
    public int $usedPlaces;
    public function __construct(int $classroomId, int $placesToSit, int $usedPlaces)
    {
        $this->classroomId = $classroomId;
        $this->placesToSit = $placesToSit;
        $this->usedPlaces = $usedPlaces;
    }
}

function classroomOrdererInsert(mysqli $conn, $valuesToInsert)
{
    //Do Insert
    $stmt = $conn->prepare("INSERT INTO attendants_presence_teamPropaganda(id_subevents, variable_symbol, id_classrooms) VALUES (?, ?, ?)");
    if (!$stmt->bind_param("iii", $_POST["id"], $variableSymbol, $classroom)) {
        http_response_code(400);
        echo "Nebylo možno přiřadit zájemce do učebny.";
        $stmt->close();
        die();
    }
    if (!$conn->begin_transaction()) {
        http_response_code(400);
        echo "Nebylo možno přiřadit zájemce do učebny.";
        $stmt->close();
        die();
    }
    for ($i = 0; $i < count($valuesToInsert); $i += 2) {
        $variableSymbol = $valuesToInsert[$i];
        $classroom = $valuesToInsert[$i + 1];
        if (!$stmt->execute()) {
            http_response_code(400);
            echo "Nebylo možno přiřadit zájemce do učebny.";
            $stmt->close();
            die();
        }
    }
    if (!$conn->commit()) {
        http_response_code(400);
        echo "Nebylo možno přiřadit zájemce do učebny.";
        $stmt->close();
        die();
    }
    $stmt->close();
}

function classroomOrdererUpdate(mysqli $conn, $valuesToUpdate)
{
    //Do Update
    $stmt = $conn->prepare("UPDATE attendants_presence_teamPropaganda SET id_classrooms = ? WHERE id_subevents = ? AND variable_symbol = ?");
    if (!$stmt->bind_param("iii", $classroom, $_POST["id"], $variableSymbol)) {
        http_response_code(400);
        echo "Nebylo možno přiřadit zájemce do učebny.";
        $stmt->close();
        die();
    }
    if (!$conn->begin_transaction()) {
        http_response_code(400);
        echo "Nebylo možno přiřadit zájemce do učebny.";
        $stmt->close();
        die();
    }
    for ($i = 0; $i < count($valuesToUpdate); $i += 2) {
        $variableSymbol = $valuesToUpdate[$i];
        $classroom = $valuesToUpdate[$i + 1];
        if (!$stmt->execute()) {
            http_response_code(400);
            echo "Nebylo možno přiřadit zájemce do učebny.";
            $stmt->close();
            die();
        }
    }
    if (!$conn->commit()) {
        http_response_code(400);
        echo "Nebylo možno přiřadit zájemce do učebny.";
        $stmt->close();
        die();
    }
    $stmt->close();
}

function classroomOrderer(mysqli $conn, $variableSymbolToSchools)
{
    //Get classrooms
    $stmt = $conn->prepare("SELECT cs.id_classrooms, c.places_to_sit, COUNT(ap.variable_symbol) FROM classrooms_subevents_teamPropaganda cs JOIN classrooms_teamPropaganda c ON cs.id_classrooms = c.id_classrooms LEFT JOIN attendants_presence_teamPropaganda ap ON cs.id_classrooms = ap.id_classrooms AND cs.id_subevents = ap.variable_symbol WHERE cs.id_subevents = ? GROUP BY cs.id_classrooms;");
    if (!$stmt->bind_param("i", $_POST["id"]) || !$stmt->execute() || !$stmt->store_result()) {
        http_response_code(400);
        echo "Nepodařilo se načíst seznam učeben.";
        $stmt->close();
        die();
    }

    //Add to array
    $classrooms = [];
    for ($i = 0; $i < $stmt->num_rows; $i++) {
        if (!$stmt->bind_result($classroom, $placesToSit, $usedPlaces) || !$stmt->fetch()) {
            http_response_code(400);
            echo "Nepodařilo se načíst seznam studentů.";
            $stmt->close();
            die();
        }
        $classrooms[] = new classroomInfo($classroom, $placesToSit, $usedPlaces);
    }
    $stmt->close();

    //Check if there are classrooms
    if (count($classrooms) == 0) {
        http_response_code(400);
        echo "Nejsou k dispozici žádné učebny.";
        die();
    }

    //Sort to classrooms
    $valuesToInsert = [];
    $valuesToUpdate = [];
    for ($i = 0; $i < count($variableSymbolToSchools); $i++) {
        $foundClassroom = false;
        for ($j = 0; $j < count($classrooms); $j++) {
            $classroom = ($i + $j) % count($classrooms);
            if ($classrooms[$classroom]->placesToSit - $classrooms[$classroom]->usedPlaces > 0) {
                if ($variableSymbolToSchools[$i]->isInTable) {
                    $valuesToUpdate[] = $variableSymbolToSchools[$i]->variableSymbol;
                    $valuesToUpdate[] = $classrooms[$classroom]->classroomId;
                } else {
                    $valuesToInsert[] = $variableSymbolToSchools[$i]->variableSymbol;
                    $valuesToInsert[] = $classrooms[$classroom]->classroomId;
                }
                $classrooms[$classroom]->usedPlaces++;
                $foundClassroom = true;
                break;
            }
        }
        if (!$foundClassroom) {
            http_response_code(400);
            echo "Nebylo možno přiřadit zájemce do učebny.";
            die();
        }
    }
    classroomOrdererInsert($conn, $valuesToInsert);
    classroomOrdererUpdate($conn, $valuesToUpdate);
    http_response_code(201);
    echo "Změny uloženy.";
    die();
}

if (isset($_POST["action"])) {
    if ($_POST["action"] == "update") {
        //Check if values set
        if (!isset($_POST["date"]) || !isset($_POST["start_time"]) || !isset($_POST["end_time"]) || !isset($_POST["id"])) {
            http_response_code(400);
            echo "Neplatné použití funkce - chybí parametr";
            die();
        }

        //Make SQL Update
        $stmt = $conn->prepare("UPDATE subevents_teamPropaganda SET date=?,start_time=?,end_time=? WHERE id_subevents=?");
        if ($stmt->bind_param("sssi", $_POST["date"], $_POST["start_time"], $_POST["end_time"], $_POST["id"]) && $stmt->execute() && $stmt->close()) {
            http_response_code(201);
            echo "Podudálost upravena.";
            die();
        } else {
            http_response_code(400);
            echo "Podudálost nemohla být upravena.";
            die();
        }
    } else if ($_POST["action"] == "insert") {
        //Check if values set
        if (!isset($_POST["date"]) || !isset($_POST["start_time"]) || !isset($_POST["end_time"]) || !isset($_POST["id_events"])) {
            http_response_code(400);
            echo "Neplatné použití funkce - chybí parametr";
            die();
        }

        //Make SQL Insert
        $stmt = $conn->prepare("INSERT INTO subevents_teamPropaganda(id_events,date,start_time, end_time) VALUES (?,?,?,?)");
        if ($stmt->bind_param("isss", $_POST["id_events"], $_POST["date"], $_POST["start_time"], $_POST["end_time"]) && $stmt->execute() && $stmt->close()) {
            http_response_code(201);
            echo "Podudálost vytvořena.";
            die();
        } else {
            http_response_code(400);
            echo "Podudálost nemohla být vytvořena.";
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
        $stmt = $conn->prepare("DELETE FROM subevents_teamPropaganda WHERE id_subevents=?");
        if ($stmt->bind_param("i", $_POST["id"]) && $stmt->execute() && $stmt->close()) {
            http_response_code(201);
            echo "Podudálost odstraněna.";
            die();
        } else {
            http_response_code(400);
            echo "Podudálost nemohla být odstraněna.";
            die();
        }
    } else if ($_POST["action"] == "addClassroom") {
        //Check if values set
        if (!isset($_POST["id"]) || !isset($_POST["classroom"])) {
            http_response_code(400);
            echo "Neplatné použití funkce - chybí parametr";
            die();
        }

        //Check if in table
        $stmt = $conn->prepare("SELECT COUNT(id_classrooms) FROM classrooms_subevents_teamPropaganda WHERE id_classrooms = ? AND id_subevents = ?");
        if (!$stmt->bind_param("ii", $_POST["classroom"], $_POST["id"]) || !$stmt->execute() || !$stmt->store_result() || !$stmt->bind_result($count) || !$stmt->fetch() || !$stmt->close()) {
            http_response_code(400);
            echo "Nelze získat informace o učebně.";
            die();
        }
        if ($count > 0) {
            http_response_code(400);
            echo "Učebna již přidána.";
            die();
        }

        //Make SQL Insert
        $stmt = $conn->prepare("INSERT INTO classrooms_subevents_teamPropaganda(id_classrooms, id_subevents) VALUES (?,?)");
        if ($stmt->bind_param("ii", $_POST["classroom"], $_POST["id"]) && $stmt->execute()) {
            if ($stmt->affected_rows == 0) {
                http_response_code(400);
                echo "Učebna již přidána.";
                $stmt->close();
                die();
            }
            $stmt->close();
            http_response_code(201);
            echo "Učebna přidána.";
            die();
        } else {
            $stmt->close();
            http_response_code(400);
            echo "Učebna nemohla být přidána.";
            die();
        }
    } else if ($_POST["action"] == "removeClassroom") {
        //Check if values set
        if (!isset($_POST["id"]) || !isset($_POST["classroom"])) {
            http_response_code(400);
            echo "Neplatné použití funkce - chybí parametr";
            die();
        }

        //Move attendants
        $stmt = $conn->prepare("UPDATE attendants_presence_teamPropaganda SET id_classrooms=NULL WHERE id_classrooms=? AND id_subevents=?;");
        if (!$stmt->bind_param("ii", $_POST["classroom"], $_POST["id"]) || !$stmt->execute() || !$stmt->close()) {
            http_response_code(400);
            echo "Nepodařilo se změnit učebnu.";
            die();
        }

        //Make SQL Delete
        $stmt = $conn->prepare("DELETE FROM classrooms_subevents_teamPropaganda WHERE id_subevents=? AND id_classrooms=?");
        if ($stmt->bind_param("ii", $_POST["id"], $_POST["classroom"]) && $stmt->execute() && $stmt->close()) {
            http_response_code(201);
            echo "Učebna odebrána.";
            die();
        } else {
            http_response_code(400);
            echo "Učebna nemohla být odebrána.";
            die();
        }
    } else if ($_POST["action"] == "moveClassroom") {
        //Check if values set
        if (!isset($_POST["id"]) || !isset($_POST["source_classroom"]) || !isset($_POST["target_classroom"])) {
            http_response_code(400);
            echo "Neplatné použití funkce - chybí parametr";
            die();
        }

        //Get info about new classroom
        $stmt = $conn->prepare("SELECT c.places_to_sit, cs.id_classrooms IS NOT NULL FROM classrooms_teamPropaganda c LEFT JOIN classrooms_subevents_teamPropaganda cs ON c.id_classrooms = cs.id_classrooms AND cs.id_subevents = ? WHERE c.id_classrooms = ?;");
        if (!$stmt->bind_param("ii", $_POST["id"], $_POST["target_classroom"]) || !$stmt->execute() || !$stmt->store_result() || $stmt->num_rows == 0 || !$stmt->bind_result($placesToSitTarget, $exists) || !$stmt->fetch() || !$stmt->close()) {
            http_response_code(400);
            echo "Nepodařilo se získat informace o cílové učebně.";
            die();
        }
        if ($exists == 1) {
            http_response_code(400);
            echo "Učebna již přidána k této podudálosti.";
            die();
        }

        //Get info about current classroom
        $stmt = $conn->prepare("SELECT places_to_sit FROM classrooms_teamPropaganda WHERE id_classrooms = ?;");
        if (!$stmt->bind_param("i", $_POST["source_classroom"]) || !$stmt->execute() || !$stmt->store_result() || $stmt->num_rows == 0 || !$stmt->bind_result($placesToSitSource) || !$stmt->fetch() || !$stmt->close()) {
            http_response_code(400);
            echo "Nepodařilo se získat informace o zdrojové učebně.";
            die();
        }
        if ($placesToSitTarget < $placesToSitSource) {
            http_response_code(400);
            echo "Cílová učebna je příliš malá.";
            die();
        }

        //Check if in table
        $stmt = $conn->prepare("SELECT COUNT(id_classrooms) FROM classrooms_subevents_teamPropaganda WHERE id_classrooms = ? AND id_subevents = ?");
        if (!$stmt->bind_param("ii", $_POST["classroom"], $_POST["id"]) || !$stmt->execute() || !$stmt->store_result() || !$stmt->bind_result($count) || !$stmt->fetch() || !$stmt->close()) {
            http_response_code(400);
            echo "Nelze získat informace o učebně.";
            $stmt->close();
            die();
        }
        if ($count > 0) {
            http_response_code(400);
            $stmt->close();
            echo "Učebna již přidána.";
            die();
        }

        //Add target classroom
        $stmt = $conn->prepare("INSERT INTO classrooms_subevents_teamPropaganda(id_classrooms, id_subevents) VALUES (?,?)");
        if ($stmt->bind_param("ii", $_POST["target_classroom"], $_POST["id"]) && $stmt->execute()) {
            if ($stmt->affected_rows == 0) {
                $stmt->close();
                http_response_code(400);
                echo "Učebna již přidána.";
                die();
            } else {
                $stmt->close();
            }
        } else {
            $stmt->close();
            http_response_code(400);
            echo "Nepodařilo se přidat učebnu.";
            die();
        }

        //Move attendants
        $stmt = $conn->prepare("UPDATE attendants_presence_teamPropaganda SET id_classrooms=? WHERE id_classrooms=? AND id_subevents=?;");
        if (!$stmt->bind_param("iii", $_POST["target_classroom"], $_POST["source_classroom"], $_POST["id"]) || !$stmt->execute() || !$stmt->close()) {
            http_response_code(400);
            echo "Nepodařilo se změnit učebnu.";
            die();
        }

        //Delete old classroom
        $stmt = $conn->prepare("DELETE FROM classrooms_subevents_teamPropaganda WHERE id_subevents=? AND id_classrooms=?");
        if (!$stmt->bind_param("ii", $_POST["id"], $_POST["source_classroom"]) || !$stmt->execute() || !$stmt->close()) {
            http_response_code(201);
            echo "Žáci přesunuti do nové učebny.";
            die();
        } else {
            http_response_code(400);
            echo "Původní učebna nemohla být odstraněna.";
            die();
        }
    } else if ($_POST["action"] == "sortAttendants") {
        //Check if values set
        if (!isset($_POST["id"]) || !isset($_POST["force"]) || !isset($_POST["not_in_table"]) || !isset($_POST["in_table"])) {
            http_response_code(400);
            echo "Neplatné použití funkce - chybí parametr";
            die();
        }

        //Select if force
        $variableSymbolToSchools = [];
        if ($_POST["force"] == "1") {
            //Get force = all attendants will recalculate class
            $stmt = $conn->prepare("SELECT ra.variable_symbol, a.id_schools, ap.variable_symbol IS NOT NULL FROM registered_attendants_teamPropaganda ra JOIN attendants_teamPropaganda a ON ra.id_attendants = a.id_attendants JOIN schools_teamPropaganda sch ON a.id_schools = sch.id_schools JOIN subevents_teamPropaganda s ON ra.id_events = s.id_events LEFT JOIN attendants_presence_teamPropaganda ap ON ra.variable_symbol = ap.variable_symbol AND s.id_subevents = ap.id_subevents WHERE s.id_subevents IN (?) ORDER BY CONCAT(sch.name, ' ', sch.address), a.surname, a.name;");
            if (!$stmt->bind_param("i", $_POST["id"]) || !$stmt->execute() || !$stmt->store_result()) {
                http_response_code(400);
                echo "Nepodařilo se načíst seznam studentů.";
                $stmt->close();
                die();
            }

            //Add to array
            for ($i = 0; $i < $stmt->num_rows; $i++) {
                if (!$stmt->bind_result($variableSymbol, $school, $isInTable) || !$stmt->fetch()) {
                    http_response_code(400);
                    echo "Nepodařilo se načíst seznam studentů.";
                    $stmt->close();
                    die();
                }
                $variableSymbolToSchools[] = new variableSymbolInfo($variableSymbol, $school, $isInTable == 1);
            }
            $stmt->close();
        } else {
            //Get all variable symbols
            $variableSymbols = [];
            foreach (explode(", ", $_POST["not_in_table"]) as $key => $value) {
                $variableSymbols[] = $value;
            }
            foreach (explode(", ", $_POST["in_table"]) as $key => $value) {
                $variableSymbols[] = $value;
            }

            //Check if there is something to do
            if (count($variableSymbols) > 0) {
                //Get needed attendants
                $stmt = $conn->prepare("SELECT ra.variable_symbol, a.id_schools, ap.variable_symbol IS NOT NULL FROM registered_attendants_teamPropaganda ra JOIN attendants_teamPropaganda a ON ra.id_attendants = a.id_attendants JOIN schools_teamPropaganda sch ON a.id_schools = sch.id_schools LEFT JOIN attendants_presence_teamPropaganda ap ON ra.variable_symbol = ap.variable_symbol AND ap.id_subevents = ? WHERE ra.variable_symbol IN (" . str_repeat("?,", count($variableSymbols) - 1) . "?" . ") ORDER BY CONCAT(sch.name, ' ', sch.address), a.surname, a.name;");
                if (!$stmt->bind_param("i" . str_repeat("i", count($variableSymbols)), $_POST["id"], ...$variableSymbols) || !$stmt->execute() || !$stmt->store_result()) {
                    http_response_code(400);
                    echo "Nepodařilo se načíst seznam studentů.";
                    $stmt->close();
                    die();
                }

                //Add to array
                for ($i = 0; $i < $stmt->num_rows; $i++) {
                    if (!$stmt->bind_result($variableSymbol, $school, $isInTable) || !$stmt->fetch()) {
                        http_response_code(400);
                        echo "Nepodařilo se načíst seznam studentů.";
                        $stmt->close();
                        die();
                    }
                    $variableSymbolToSchools[] = new variableSymbolInfo($variableSymbol, $school, $isInTable == 1);
                }
                $stmt->close();
            }
        }

        //Check if has something to do
        if (count($variableSymbolToSchools) == 0) {
            http_response_code(201);
            echo "Není co dělat.";
            die();
        }

        //Clear classrooms when force
        if ($_POST["force"] == "1") {
            $stmt = $conn->prepare("UPDATE attendants_presence_teamPropaganda SET id_classrooms = NULL WHERE id_subevents = ?;");
            if (!$stmt->bind_param("i", $_POST["id"]) || !$stmt->execute() || !$stmt->close()) {
                http_response_code(400);
                echo "Nepodařilo se odebrat zájemce z učeben.";
                die();
            }
        }
        classroomOrderer($conn, $variableSymbolToSchools);
    } else if ($_POST["action"] == "copySettings") {
        //Check if values set
        if (!isset($_POST["id"]) || !isset($_POST["source_id"])) {
            http_response_code(400);
            echo "Neplatné použití funkce - chybí parametr";
            die();
        }
        if ($_POST["id"] == $_POST["source_id"]) {
            http_response_code(400);
            echo "Zdroj a cíl je stejný.";
            die();
        }

        //Get all attendants
        $variableSymbolToIsInTable = [];
        $order = [];
        $stmt = $conn->prepare("SELECT ra.variable_symbol, ap.variable_symbol IS NOT NULL FROM registered_attendants_teamPropaganda ra JOIN attendants_teamPropaganda a ON ra.id_attendants = a.id_attendants JOIN schools_teamPropaganda sch ON a.id_schools = sch.id_schools JOIN subevents_teamPropaganda s ON ra.id_events = s.id_events LEFT JOIN attendants_presence_teamPropaganda ap ON ra.variable_symbol = ap.variable_symbol AND s.id_subevents = ap.id_subevents WHERE s.id_subevents IN (?) ORDER BY CONCAT(sch.name, ' ', sch.address), a.surname, a.name;");
        if (!$stmt->bind_param("i", $_POST["id"]) || !$stmt->execute() || !$stmt->store_result()) {
            http_response_code(400);
            echo "Nepodařilo se načíst seznam studentů.";
            $stmt->close();
            die();
        }

        //Add to array
        for ($i = 0; $i < $stmt->num_rows; $i++) {
            if (!$stmt->bind_result($variableSymbol, $isInTable) || !$stmt->fetch()) {
                http_response_code(400);
                echo "Nepodařilo se načíst seznam studentů.";
                $stmt->close();
                die();
            }
            $order[] = $variableSymbol;
            $variableSymbolToIsInTable[$variableSymbol] = $isInTable == 1;
        }
        $stmt->close();

        //Check if has something to do
        if (count($variableSymbolToIsInTable) == 0) {
            http_response_code(201);
            echo "Není co dělat.";
            die();
        }

        //Clear classrooms
        $stmt = $conn->prepare("UPDATE attendants_presence_teamPropaganda SET id_classrooms = NULL WHERE id_subevents = ?;");
        if (!$stmt->bind_param("i", $_POST["id"]) || !$stmt->execute() || !$stmt->close()) {
            http_response_code(400);
            echo "Nepodařilo se odebrat zájemce z učeben.";
            die();
        }

        //Delete classrooms
        $stmt = $conn->prepare("DELETE FROM classrooms_subevents_teamPropaganda cs WHERE cs.id_subevents = ?;");
        if (!$stmt->bind_param("i", $_POST["id"]) || !$stmt->execute() || !$stmt->close()) {
            http_response_code(400);
            echo "Nepodařilo se načíst odebrat zájemce z učeben.";
            die();
        }

        //Copy source classrooms
        $stmt = $conn->prepare("INSERT INTO classrooms_subevents_teamPropaganda(id_classrooms, id_subevents) SELECT cs.id_classrooms, ? FROM classrooms_subevents_teamPropaganda cs WHERE cs.id_subevents = ?;");
        if (!$stmt->bind_param("ii", $_POST["id"], $_POST["source_id"]) || !$stmt->execute() || !$stmt->close()) {
            http_response_code(400);
            echo "Nepodařilo se okopírovat seznam učeben ze zdrojové události.";
            die();
        }

        //Copy classrooms to attendants
        $stmt = $conn->prepare("SELECT variable_symbol, id_classrooms FROM attendants_presence_teamPropaganda WHERE id_subevents = ?;");
        if (!$stmt->bind_param("i", $_POST["source_id"]) || !$stmt->execute() || !$stmt->store_result()) {
            http_response_code(400);
            echo "Nepodařilo se získat seznam zájemců k učebnám ze zdrojové události.";
            $stmt->close();
            die();
        }
        $variableSymbolToClassrooms = [];
        for ($i = 0; $i < $stmt->num_rows; $i++) {
            if (!$stmt->bind_result($variableSymbol, $classroomId) || !$stmt->fetch()) {
                http_response_code(400);
                echo "Nepodařilo se získat seznam zájemců k učebnám ze zdrojové události.";
                $stmt->close();
                die();
            }
            if ($variableSymbolToIsInTable[$variableSymbol] == null) {
                http_response_code(400);
                echo "Zájemce není přihlášen na tuto akci.";
                $stmt->close();
                die();
            }
            $variableSymbolToClassrooms[$variableSymbol] = $classroomId;
        }
        $stmt->close();

        //Sort
        $valuesToInsert = [];
        $valuesToUpdate = [];
        foreach ($order as $key => $value) {
            if ($variableSymbolToIsInTable[$value]) {
                $valuesToUpdate[] = $value;
                $valuesToUpdate[] = $variableSymbolToClassrooms[$value];
            } else {
                $valuesToInsert[] = $value;
                $valuesToInsert[] = $variableSymbolToClassrooms[$value];
            }
        }

        //Do copy
        classroomOrdererInsert($conn, $valuesToInsert);
        classroomOrdererUpdate($conn, $valuesToUpdate);
        http_response_code(201);
        echo "Změny uloženy.";
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
    <title>Podudálost</title>
    <link rel="stylesheet" href="../formWebScripts/css/formStyle.css">
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body class="pageHolder">
    <header>
        <?php setupTitlebarAdmin($conn, "subevent.php") ?>
    </header>
    <main>
        <?php
        $eventId = "";
        $dateDB = new DateTime("now", new DateTimeZone("Europe/Prague"))->format('Y-m-d');
        $startTimeDB = new DateTime("now", new DateTimeZone("Europe/Prague"))->format('H:i:s');
        $endTimeDB = new DateTime("now", new DateTimeZone("Europe/Prague"))->format('H:i:s');
        $exists = "true";
        $attendantsCount = 0;
        if (isset($_GET["newSubevent"])) {
            echo "<h1>Vytvořit novou podudálost</h1>";
            $exists = "false";
            $eventId = $_GET["event"];
        } else {
            //Get subevent info
            $stmt = $conn->prepare("SELECT s.id_events, s.date, s.start_time, s.end_time, COUNT(ra.id_attendants), e.name, e.registration_close,e.active_until FROM subevents_teamPropaganda s JOIN events_teamPropaganda e ON s.id_events = e.id_events LEFT JOIN registered_attendants_teamPropaganda ra ON s.id_events = ra.id_events AND ra.paid IS NOT NULL WHERE s.id_subevents = ?;");
            if (!$stmt->bind_param("i", $_GET["subevent"]) || !$stmt->execute() || !$stmt->store_result() || $stmt->num_rows != 1 || !$stmt->bind_result($eventId, $dateDB, $startTimeDB, $endTimeDB, $attendantsCount, $eventName, $registrationCloseDB, $activeUntilDB) || !$stmt->fetch() || !$stmt->close() || $eventId == null) {
                echo "<h1>Nelze získat informace o podudálosti.</h1>";
                echo "<a href='./admin.php'><button class='purkynkaButton'>Zpět na hlavní stránku</button></a>";
                die();
            }
            $dateFormated = new DateTime($dateDB)->format(STANDARD_CZECH_DATE_FORMAT_FULL);
            echo "<h1>Informace o události: $name → $dateFormated </h1>";
            echo "<i>Nedoporučuje se upravovat již proběhlé události, mohl by nastat chaos.</i><br>";
        }

        //Format dates
        $date = DateTime::createFromFormat('Y-m-d', $dateDB)->format("Y-m-d");
        $startTime = DateTime::createFromFormat('H:i:s', $startTimeDB)->format("H:i");
        $endTime = DateTime::createFromFormat('H:i:s', $endTimeDB)->format("H:i");
        $registrationClose = DateTime::createFromFormat('Y-m-d H:i:s', $registrationCloseDB)->format("Y-m-d");
        $registrationCloseTime = DateTime::createFromFormat('Y-m-d H:i:s', $registrationCloseDB)->format("H:i");
        $activeUntil = DateTime::createFromFormat('Y-m-d H:i:s', $activeUntilDB)->format("Y-m-d");
        $activeUntilTime = DateTime::createFromFormat('Y-m-d H:i:s', $activeUntilDB)->format("H:i");
        echo "<form-input label='K události:' style='display: none' type='hidden' class='subeventValidate' original-value='$eventId' id='id_events' value='$eventId'></form-input>";
        //$isFunctionalString = $isFunctional == 1 ? "true" : "false";
        
        //Create HTML
        echo "<fieldset>";
        echo "<legend>Nastavení podudálosi</legend>";
        echo "<p><i>Poznámka: Nastavení v této sekci se ukládají po stisknutí tlačítek na konci této sekce.</i></p>";
        echo "<form-input tabindex='1' label='Datum konání podudálosti:' class='subeventValidate' do-change-check='$exists' type='date' value-id='date'  id='date' original-value='$date' value='$date' min='$registrationClose' max='$activeUntil' minTime='$registrationCloseTime' maxTime='$activeUntilTime'></form-input>";
        echo "<form-input tabindex='2' label='Zahájení události:' class='subeventValidate' do-change-check='$exists' type='time' value-id='start_time' id='start_time' original-value='$startTime' value='$startTime'></form-input>";
        echo "<form-input tabindex='3' label='Konec události:' class='subeventValidate' do-change-check='$exists' type='time' value-id='end_time' id='end_time' original-value='$endTime' value='$endTime'></form-input>";

        //Echo HTML buttons
        echo "<div class='formButtonBoxHolder'>";
        echo "<div class='formButtonBox'>";
        echo "<button tabindex='4' exists='$exists' class='formButton purkynkaButton btnSave' form-icon='!save'></button>";
        echo "<button tabindex='5' exists='$exists' class='formButton purkynkaButton btnCancel' form-icon='!dontSave'></button>";
        echo "<a tabindex='-1'  href='./events.php'><button tabindex='6' class='formButton purkynkaButton' form-icon='!listTable'><span>Zpět na seznam události</span></button></a>";
        echo "</div>";
        echo "</div></fieldset>";

        //Fieldset for classrooms
        if ($exists == "true") {
            echo "<fieldset>";
            echo "<legend>Nastavení učeben</legend>";
            echo "<p><i>Poznámka: Nastavení v této sekci se ukládají automaticky.</i></p>";

            //Select classrooms
            $placesToSitTotal = 0;
            $placesToSitUsedTotal = 0;
            if ($exists == "true") {
                //Get info about attendants without classroom
                $totalCountWithoutClassroom = 0;
                $attendantsWithoutClassroomInTable = "";
                $attendantsWithoutClassroomNotInTable = "";
                $stmt = $conn->prepare("SELECT  ap.id_subevents IS NOT NULL as in_table, GROUP_CONCAT(ra.id_attendants), COUNT(ra.id_attendants) FROM registered_attendants_teamPropaganda ra LEFT JOIN attendants_presence_teamPropaganda ap ON ra.variable_symbol = ap.variable_symbol AND ap.id_subevents = ? WHERE ap.id_classrooms IS NULL AND ra.id_events = ? GROUP BY in_table;");
                if (!$stmt->bind_param("ii", $_GET["subevent"], $eventId) || !$stmt->execute() || !$stmt->store_result()) {
                    $stmt->close();
                    echo "<p id='withoutClassroom' count='0' not-in-table='' in-table=''><b>Nelze získat informace o žácích mimo učebnu.</b></p>";
                } else {
                    for ($i = 0; $i < $stmt->num_rows; $i++) {
                        if (!$stmt->bind_result($inTable, $attendantsWithoutClassroom, $countWithoutClassroom) || !$stmt->fetch()) {
                            $inTable = 0;
                            $attendantsWithoutClassroom = "";
                            $countWithoutClassroom = "";
                        }
                        $totalCountWithoutClassroom += $countWithoutClassroom;
                        if ($inTable == 1) {
                            $attendantsWithoutClassroomInTable = $attendantsWithoutClassroom;
                        } else {
                            $attendantsWithoutClassroomNotInTable = $attendantsWithoutClassroom;
                        }
                    }
                    $stmt->close();

                    //Put to HTML
                    echo "<p id='withoutClassroom' count='$totalCountWithoutClassroom' not-in-table='$attendantsWithoutClassroomNotInTable' in-table='$attendantsWithoutClassroomInTable'>Počet zájemců bez učebny: $totalCountWithoutClassroom</p>";
                }

                //Get info about active classrooms for event
                $stmt = $conn->prepare("SELECT cs.id_classrooms, c.name, c.places_to_sit, GROUP_CONCAT(ap.variable_symbol), COUNT(ap.variable_symbol) FROM classrooms_subevents_teamPropaganda cs JOIN classrooms_teamPropaganda c ON cs.id_classrooms = c.id_classrooms LEFT JOIN attendants_presence_teamPropaganda ap ON (ap.id_subevents = cs.id_subevents AND ap.id_classrooms = cs.id_classrooms) WHERE cs.id_subevents = ? GROUP BY cs.id_classrooms, c.name, c.places_to_sit;");
                if (!$stmt->bind_param("i", $_GET["subevent"]) || !$stmt->execute() || !$stmt->store_result()) {
                    echo "<p>Nelze získat aktivní učebny.</p>";
                    $stmt->close();
                } else if ($stmt->num_rows > 0) {
                    $echoHeader = true;
                    for ($i = 0; $i < $stmt->num_rows; $i++) {
                        $tab1 = $i * 2 + 10;
                        $tab2 = $tab1 + 1;
                        if (!$stmt->bind_result($idClassroom, $classroomName, $placesToSit, $variableSymbols, $placesToSitUsed) || !$stmt->fetch()) {
                            $idClassroom = null;
                            $classroomName = "CHYBA";
                            $placesToSit = 0;
                            $variableSymbols = "";
                            $placesToSitUsed = 0;
                        }
                        if ($echoHeader) {
                            if ($idClassroom == null) {
                                continue;
                            }
                            echo "<p>Aktivní učebny k této podudálosti:</p>";
                            echo "<ul>";
                            $echoHeader = false;
                        }
                        $placesToSitTotal += $placesToSit;
                        $placesToSitUsedTotal += $placesToSitUsed;
                        echo "<li>";
                        echo "<span>$classroomName → $placesToSit míst, obsazeno: $placesToSitUsed</span>";
                        echo "<button tabindex='$tab1' class='purkynkaButton moveClassroom' form-icon='!relocate' classroom='$idClassroom' count='$placesToSitUsed' variableSymbols='$variableSymbols'></button>";
                        echo "<button tabindex='$tab2' class='purkynkaButton deleteClassroom' form-icon='!delete' classroom='$idClassroom' count='$placesToSitUsed'></button>";
                        echo "</li>";
                    }
                    echo "</ul>";
                    $stmt->close();
                }
                if ($echoHeader) {
                    echo "<p>Žádné aktivní učebny.</p>";
                }
            } else {
                echo "<p>Učebny je možné nastavit až po vytvoření.</p>";
            }

            //Calculate needed places
            if ($placesToSitUsedTotal > $attendantsCount) {
                $attendantsCount = $placesToSitUsedTotal;
            }
            $freePlaces = $placesToSitTotal - $attendantsCount;
            $disableSort = "";
            if ($freePlaces >= 0) {
                echo "<p id='freeSpacesCount' ok='1'>Počet volných míst v učebnách: " . $freePlaces . "</p>";
            } else {
                $disableSort = "disabled";
                echo "<p id='freeSpacesCount' ok='0'>Na událost je nedostatečný počet míst v učebnách: " . abs($freePlaces) . "</p>";
            }

            //Echo HTML buttons
            echo "<div class='formButtonBoxHolder'>";
            echo "<div class='formButtonBox'>";
            echo "<button tabindex='7' id='addClassroom' class='formButton purkynkaButton' form-icon='!add'><span>Přidat učebnu</span></button>";
            echo "<button tabindex='8' id='copySettings' class='formButton purkynkaButton' form-icon='!copy'><span>Kopírovat nastavení učeben z jiné podudálosti</span></button>";
            echo "<button tabindex='9' id='sortAttendants' class='formButton purkynkaButton' $disableSort form-icon='!shuffle'><span>Rozřadit zájemce do učeben</span></button>";
            echo "</div>";
            echo "</div></fieldset>";
        }
        ?>
    </main>
    <footer>

    </footer>
</body>
<script type="module" src="../formWebScripts/js/formScript.js"></script>
<script type='module' src='./subevent.js'></script>

</html>
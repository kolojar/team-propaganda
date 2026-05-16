<?php
function setupTitlebarUser(mysqli $conn)
{
    //Get name of current user
    $stmt = $conn->prepare("SELECT name, surname FROM users_teamPropaganda WHERE id_users=?");
    $stmt->bind_param("i", $_SESSION["userId"]);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($name, $surname);
    $stmt->fetch();

    //Make HTML
    echo "<div class='formButtonBoxHolder' style='margin-top: 0px;'>
        <div class='formButtonBox formJustifyLeft'>
            <h1 class='headerName' onclick='window.location.href = \"./index.php\"'>$name $surname</h1>
        </div>
        <div class='formButtonBox formJustifyRight'>
            <a href='../logoff.php'><button class='formButton purkynkaButton'>Odhlásit se</button></a>
        </div>
    </div>";
}

function echoCheckIfParentMatches(mysqli $conn, string $attendantId)
{
    if (!checkIfParentMatches($conn, $attendantId)) {
        http_response_code(400);
        echo "Invalid usage of function - current user is not parent";
        die();
    }
}

function checkIfParentMatches(mysqli $conn, string $attendantId): bool
{
    $stmtAttendant = $conn->prepare("SELECT id_parent FROM attendants_teamPropaganda WHERE id_attendants = ?;");
    if ($stmtAttendant == false) {
        return false;
    }
    if (!$stmtAttendant->bind_param("i", $attendantId)) {
        return false;
    }
    if (!$stmtAttendant->execute()) {
        return false;
    }
    if (!$stmtAttendant->store_result()) {
        return false;
    }
    if (!$stmtAttendant->bind_result($getId)) {
        return false;
    }
    if (!$stmtAttendant->fetch()) {
        return false;
    }
    return $getId == $_SESSION["userId"];
}

function echoCheckIfParentMatches2(mysqli $conn, string $variableSymbol)
{
    if (!checkIfParentMatches($conn, $variableSymbol)) {
        http_response_code(400);
        echo "Invalid usage of function - current user is not parent";
        die();
    }
}

function checkIfParentMatches2(mysqli $conn, string $variableSymbol): bool
{
    $stmtAttendant = $conn->prepare("SELECT a.id_parent FROM registered_attendants_teamPropaganda ra JOIN attendants_teamPropaganda a ON ra.id_attendants = a.id_attendants WHERE ra.variable_symbol = ?;");
    if ($stmtAttendant == false) {
        return false;
    }
    if (!$stmtAttendant->bind_param("i", $variableSymbol)) {
        return false;
    }
    if (!$stmtAttendant->execute()) {
        return false;
    }
    if (!$stmtAttendant->store_result()) {
        return false;
    }
    if (!$stmtAttendant->bind_result($getId)) {
        return false;
    }
    if (!$stmtAttendant->fetch()) {
        return false;
    }
    return $getId == $_SESSION["userId"];
}
?>
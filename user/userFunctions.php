<?php
function setupTitlebarUser(mysqli $conn): userRoleType
{
    //Get name of current user
    $stmt = $conn->prepare("SELECT name, surname, role,type FROM users_teamPropaganda WHERE id_users=?");
    $error = false;
    if (!$stmt->bind_param("i", $_SESSION["userId"]) || !$stmt->execute() || !$stmt->store_result() || !$stmt->bind_result($name, $surname, $role, $type) || !$stmt->fetch() || !$stmt->close()) {
        $name = "Neznámý";
        $surname = "uživatel";
        $error = true;
    }

    //Make HTML
    echo "<div class='formButtonBoxHolder' style='margin-top: 0px;'>
        <div class='formButtonBox formJustifyLeft'>
            <h1 class='headerName' onclick='window.location.href = \"./index.php\"'>$name $surname</h1>
        </div>
        <div class='formButtonBox formJustifyRight'>
            <a href='./logout.php'><button class='formButton purkynkaButton'>Odhlásit se</button></a>
        </div>
    </div>";
    if ($error) {
        die();
    }

    return new userRoleType(userRole::{$role}, userType::{$type});
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
    $stmtAttendant = $conn->prepare("SELECT a.id_parent FROM registered_attendants_teamPropaganda ra JOIN attendants_teamPropaganda a ON ra.id_attendants = a.id_attendants WHERE ra.id_registered_attendants = ?;");
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

function checkIfCDMatches(string $cd): bool
{
    global $conn;
    $stmt = $conn->query("SELECT id_users FROM `company_days_companies_teamPropaganda` NATURAL JOIN companies_teamPropaganda WHERE id_company_days = " . $cd . " and id_companies = " . $_SESSION["companyId"]);
    if (!$stmt->num_rows > 0) {
        return false;
    }
    if ($row = $stmt->fetch_assoc()) {
        return $row["id_users"] == $_SESSION["userId"];
    }
    return false;
}

<?php
const STANDARD_CZECH_DATETIME_FORMAT_FULL = 'd. m. Y H:i:s';
const STANDARD_CZECH_DATE_FORMAT_FULL = 'd. m. Y';
const STANDARD_CZECH_TIME_FORMAT_FULL = 'H:i:s';
const JS_TIME_FORMAT = 'Y-m-d\\TH:i';

function getUserRole(mysqli $conn, int $id): string|null
{
    $stmt = $conn->prepare("SELECT role FROM users_teamPropaganda WHERE id_users=? LIMIT 1;");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($role);
    $stmt->fetch();
    return $role;
}

function logToConsole(string $log)
{
    file_put_contents("php://stdout", $log . "\n");
}
?>
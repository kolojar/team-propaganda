<?php
const STANDARD_CZECH_DATETIME_FORMAT_FULL = 'd. m. Y H:i:s';
const STANDARD_CZECH_DATE_FORMAT_FULL = 'd. m. Y';
const STANDARD_CZECH_TIME_FORMAT_FULL = 'H:i:s';
const JS_TIME_FORMAT = 'Y-m-d\\TH:i';
const USE_LOG_COLOR = true;

enum userType
{
    case GENERIC;
    case KLAL;
    case NILE;
}

class userRoleType
{
    public string|null $role;
    public userType|null $type;
    public function __construct(string|null $role, userType|null $type)
    {
        $this->role = $role;
        $this->type = $type;
    }
}
function getUserRoleType(mysqli $conn, int $id): userRoleType
{
    $stmt = $conn->prepare("SELECT role,type FROM users_teamPropaganda WHERE id_users=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($role, $type);
    $stmt->fetch();
    return new userRoleType($role, ((isset($type) && ($type != null)) ? userType::{$type} : null));
}

function logToConsole(string $log)
{
    file_put_contents("php://stdout", $log . "\n");
}
function errorToConsole(string $log)
{
    error_log((USE_LOG_COLOR ? "\033[31m" : "") . $log . (USE_LOG_COLOR ? "\033[0m" : ""));
}
function exceptionToConsole(Exception $e)
{
    errorToConsole($e->getTraceAsString() . "\n" . $e->getMessage());
}
function exceptionHandler(Exception $e)
{
    http_response_code(400);
    echo "Chyba SQL serveru.";
    exceptionToConsole($e);
    die();
}
?>
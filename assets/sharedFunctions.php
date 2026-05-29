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
    function getIsNILE(): int
    {
        return match ($this) {
            self::GENERIC => 2,
            self::KLAL => 0,
            self::NILE => 1,
        };
    }
    function toString(): string
    {
        return match ($this) {
            self::GENERIC => "GENERIC",
            self::KLAL => "KLAL",
            self::NILE => "NILE",
        };
    }
}

enum userRole
{
    case ADMIN;
    case ACCOUNTANT;
    case USER;
    case ANY;
}

class userRoleType
{
    public userRole|null $role;
    public userType|null $type;
    public function __construct(userRole|null $role, userType|null $type)
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
    return new userRoleType(((isset($role) && ($role != null)) ? userRole::{strtoupper($role)} : null), ((isset($type) && ($type != null)) ? userType::{strtoupper($type)} : null));
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

enum filterSelectorType
{
    case BOOLEAN;
    case BOOLEAN_NULL;
    case TEXT;
    case NUMBER;
    case DATE;
    case TIME;
    case DATETIME;
    case SELECT;
    case TEXTAREA;
}


class filterSelector
{
    public string $displayName;
    public string $sqlName;
    public string $compareOperator;
    public filterSelectorType $type;
}
function setupFilteredTable(mysqli $conn, string $rawSelect, filterSelector ...$filterSelectors)
{
    //Trim trainling ;
    $rawSelect = trim($rawSelect, ";");
    $rawSelect = trim($rawSelect);

    //Add WHERE
    $lastWhere = strrpos($rawSelect, "WHERE");
    $lastJoin = strrpos($rawSelect, "JOIN");
    $lastOn = strrpos($rawSelect, "ON");
    $lastSelect = strrpos($rawSelect, "SELECT");
    if (!$lastWhere) {
        $rawSelect .= " WHERE 1=1";
    } else {
        if ($lastJoin && $lastJoin > $lastWhere) {
            $rawSelect .= " WHERE 1=1";
        } else if ($lastOn && $lastOn > $lastWhere) {
            $rawSelect .= " WHERE 1=1";
        } else if ($lastSelect && $lastSelect > $lastWhere) {
            $rawSelect .= " WHERE 1=1";
        }
    }

    //Add filters
    echo "<fieldset><legend>Konfigurace filtrování</legend>";
    foreach ($filterSelectors as $key => $value) {
        //Prepare input
        $label = $value->displayName;
        $type = "";
        if($value->type == filterSelectorType::BOOLEAN || $value->type == filterSelectorType::BOOLEAN_NULL) {
            $type = "checkbox";
        } else if($value->type == filterSelectorType::DATE) {
            $type = "date";
        } else if($value->type == filterSelectorType::TIME) {
            $type = "time";
        } else if($value->type == filterSelectorType::DATETIME) {
            $type = "datetime";
        } else if($value->type == filterSelectorType::NUMBER) {
            $type = "number";
        } else if($value->type == filterSelectorType::TEXT) {
            $type = "text";
        } else if($value->type == filterSelectorType::SELECT) {
            $type = "select";
        } else if($value->type == filterSelectorType::TEXTAREA) {
            $type = "textarea";
        }
        echo "<form-input label='$label' ";

        //Get value
        $getter = $value->sqlName . $value->type->name;
        if (isset($_GET[$getter])) {
            $rawSelect .=  " AND " . $value->sqlName . $value->compareOperator . $_GET[$getter];
        }

        //Generate UI

    } 
    echo "</fieldset>";
}
?>
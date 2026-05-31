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

function getUserName(int $id): array
{
    global $conn;
    $res = $conn->query("SELECT name, surname FROM users_teamPropaganda WHERE id_users=" . $id)->fetch_assoc();
    return $res;
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
    public string $sqlCompareOperator;
    public filterSelectorType $type;
    public string $getter;
    public array|null $settings;
    public bool $isHaving;

    public function __construct(string $sqlName, string $displayName, string $getter, filterSelectorType $type, string $sqlCompareOperator, bool $isHaving = false, array|null $settings = null)
    {
        $this->sqlName = $sqlName;
        $this->displayName = $displayName;
        $this->getter = $getter;
        $this->type = $type;
        $this->sqlCompareOperator = $sqlCompareOperator;
        $this->settings = $settings;
        $this->isHaving = $isHaving;
    }
}

class filterDisplayer
{
    public string $displayName;
    public string $sqlName;
    public filterSelectorType $type;
    public bool $defaultVisible;

    /**
     * Summary of __construct
     * @param string $sqlName Prefix with ! to make it callable -> function($result), function must RETURN, not echo
     * @param string $displayName
     * @param filterSelectorType $type
     * @param bool $defaultVisible
     */
    public function __construct(string $sqlName, string $displayName, filterSelectorType $type, bool $defaultVisible)
    {
        $this->sqlName = $sqlName;
        $this->displayName = $displayName;
        $this->type = $type;
        $this->defaultVisible = $defaultVisible;
    }
}

/**
 * Summary of setupFilteredTable
 * Include <script type="module" src="../assets/phpFilters.js"></script> at the end.
 * @param mysqli $conn
 * @param string $rawSelect
 * @param string $bindKeys
 * @param array $bindValues
 * @param filterDisplayer[] $filterDisplayersRaw
 * @param filterSelector[] $filterSelectorsRaw
 * @return bool
 */
function setupFilteredTable(mysqli $conn, string $tableStyleClasses, string $rawSelect, string $rawFrom, string $rawWhere, string $rawGroupBy, string $rawHaving, string $rawOrderBy, string $bindKeys, array|null $bindValues, array $filterSelectorsRaw,array $filterDisplayersRaw): bool
{
    //Trim trainling ;
    $rawSelect = trim($rawSelect, ";");
    $rawSelect = trim($rawSelect);
    $rawFrom = trim($rawFrom, ";");
    $rawFrom = trim($rawFrom);
    $rawWhere = trim($rawWhere, ";");
    $rawWhere = trim($rawWhere);
    $rawGroupBy = trim($rawGroupBy, ";");
    $rawGroupBy = trim($rawGroupBy);
    $rawOrderBy = trim($rawOrderBy, ";");
    $rawOrderBy = trim($rawOrderBy);

    //Add WHERE
    //$lastWhere = strrpos($rawSelect, "WHERE");
    //$lastJoin = strrpos($rawSelect, "JOIN");
    //$lastOn = strrpos($rawSelect, "ON");
    //$lastSelect = strrpos($rawSelect, "SELECT");
    //if (!$lastWhere) {
    //    $rawSelect .= " WHERE 1=1";
    //} else {
    //    if ($lastJoin && $lastJoin > $lastWhere) {
    //        $rawSelect .= " WHERE 1=1";
    //    } else if ($lastOn && $lastOn > $lastWhere) {
    //        $rawSelect .= " WHERE 1=1";
    //    } else if ($lastSelect && $lastSelect > $lastWhere) {
    //        $rawSelect .= " WHERE 1=1";
    //    }
    //}

    //Convert filter selectors
    $filterSelectors = [];
    foreach ($filterSelectorsRaw as $key => $value) {
        if (str_contains($value->sqlName, ",") || str_contains($value->sqlName, "!")) {
            errorToConsole("SQL name can not contain \",\" or \"!\": " . $value->sqlName);
        }
        if (str_contains($value->getter, ",") || str_contains($value->getter, "!")) {
            errorToConsole("Getter name can not contain \",\" or \"!\": " . $value->getter);
        }
        $filterSelectors[$value->getter] = $value;
    }

    //Add filters
    $name = uniqid("filter-", true);
    $activeFilters = [];
    echo "<fieldset filter='" . $name . "'><legend>Konfigurace filtrování</legend>";
    foreach ($filterSelectors as $key => $value) {
        logToConsole("Checking: " . $value->getter);
        if (isset($_GET[$value->getter])) {
            logToConsole("Present: " . $value->getter);
            $activeFilters[$value->getter] = [true,$value->displayName];
            //Prepare input
            $type = "";
            if ($value->type == filterSelectorType::BOOLEAN || $value->type == filterSelectorType::BOOLEAN_NULL) {
                $type = "checkbox";
            } else if ($value->type == filterSelectorType::DATE) {
                $type = "date";
            } else if ($value->type == filterSelectorType::TIME) {
                $type = "time";
            } else if ($value->type == filterSelectorType::DATETIME) {
                $type = "datetime";
            } else if ($value->type == filterSelectorType::NUMBER) {
                $type = "number";
            } else if ($value->type == filterSelectorType::TEXT) {
                $type = "text";
            } else if ($value->type == filterSelectorType::SELECT) {
                $type = "select";
            } else if ($value->type == filterSelectorType::TEXTAREA) {
                $type = "textarea";
            }

            //Get value
            $label = $value->displayName;
            $getter = $value->getter;
            $get = $_GET[$getter];
            $list = isset($value->settings["listId"]) ? $value->settings["listId"] : "";
            $min = isset($value->settings["min"]) ? (" min='" . $value->settings["min"] . "'") : "";
            $max = isset($value->settings["max"]) ? (" max='" . $value->settings["max"] . "'") : "";
            echo "<form-input label='$label' getter='$getter' type='$type' original-value='$get' value='$get' do-change-check list='$list' . $min . $max></form-input>";

            //Build select
            $islike = $value->sqlCompareOperator == "LIKE" ? "%" : "";
            $add = ($value->isHaving ? $rawHaving : $rawWhere);
            $add .= (strlen($add) == 0 ? "" : " AND ") . $value->sqlName . " " . $value->sqlCompareOperator . " '" . $islike . $get . $islike . "'";
            if ($value->isHaving) {
                $rawHaving = $add;
            } else {
                $rawWhere = $add;
            }
        } else {
            $activeFilters[$value->getter] = [false,$value->displayName];
        }
    }

    //Convert filter displayers
    $filterDisplayers = [];
    foreach ($filterDisplayersRaw as $key => $value) {
        $filterDisplayers[$value->sqlName] = $value;
    }

    //Get displayed columns
    if (isset($_GET["!display"])) {
        $filterDisplayersGet = explode(",", $_GET["!display"]);
    } else {
        $filterDisplayersGet = null;
    }
    $activeDisplayers = [];
    foreach ($filterDisplayers as $key => $value) {
        if ($filterDisplayersGet == null) {
            $activeDisplayers[$value->sqlName] = [$value->defaultVisible, $value->displayName];
        } else {
            $activeDisplayers[$value->sqlName] = [in_array($value->sqlName, $filterDisplayersGet, true), $value->displayName];;
        }
    }

    //Place buttons
    $activeFiltersJson = json_encode($activeFilters);
    $activeDisplayersJson = json_encode($activeDisplayers);
    $page = isset($_GET["!page"]) ? intval($_GET["!page"]) : 0;
    $page = $page < 1 ? 1 : $page;
    $step = isset($_GET["!pageStep"]) ? intval($_GET["!pageStep"]) : 0;
    $step = $step < 1 ? 50 : $step;
    $disableFirstPage = $page == 1 ? "disabled" : "";
    echo "<div class='formButtonBoxHolder'>";
    echo "<div class='formButtonBox formJustifyLeft'>";
    echo "<button filters='$activeFiltersJson' class='btnManageFilters purkynkaButton'>Vybrat filtry</button>";
    echo "<button displayers='$activeDisplayersJson' class='btnDisplay purkynkaButton'>Vybrat zobrazované sloupce</button>";
    echo "<button class='btnFilter purkynkaButton'>Filtrovat</button>";
    echo "</div>";
    echo "<div class='formButtonBox formJustifyRight'>";
    echo "<button filters='$activeFiltersJson' class='btnFirstPage purkynkaButton' $disableFirstPage form-icon='!firstPage'></button>";
    echo "<button filters='$activeFiltersJson' class='btnPrevPage purkynkaButton' $disableFirstPage form-icon='!previousPage'></button>";
    echo "<button page='$page' class='btnChangePage purkynkaButton'>Strana: $page</button>";
    echo "<button step='$step' class='btnChangePageStep purkynkaButton'>Maximální počet položek: $step</button>";
    echo "<button filters='$activeFiltersJson' class='btnNextPage purkynkaButton' form-icon='!nextPage'></button>";
    echo "</div>";
    echo "</div>";
    echo "</fieldset>";

    //Get sorting
    $orderers = [];
    if (isset($_GET["!order"])) {
        foreach (explode(",", $_GET["!order"]) as $key => $value) {
            $valueTrimmed = strpos($value, "!") === 0 ? substr($value,1) : $value;
            if(!isset($filterDisplayers[$valueTrimmed])) {continue;}
            $desc = "";
            if(strpos($value, "!") === 0) {
                $value = substr($value, 1);
                $desc = " DESC";
                $orderers[$value] = "$key.D";
            } else {
                $orderers[$value] = "$key.A";
            }
            $rawOrderBy .= (strlen($rawOrderBy) == 0 ? "" : ", ") . $value . $desc;
        }
    } 

    //Perform select
    $sqlOffset = ($page-1) * $step;
    $sql = "SELECT " . $rawSelect . " FROM " . $rawFrom . (strlen($rawWhere) == 0 ? "" : (" WHERE " . $rawWhere)) . (strlen($rawGroupBy) == 0 ? "" : (" GROUP BY " . $rawGroupBy)) . (strlen($rawHaving) == 0 ? "" : (" HAVING " . $rawHaving)) . (strlen($rawOrderBy) == 0 ? "" : (" ORDER BY " . $rawOrderBy)) . " LIMIT $sqlOffset,$step";
    logToConsole($sql);
    $stmt = $conn->prepare($sql);
    if (strlen($bindKeys) > 0) {
        if ($bindValues == null || !$stmt->bind_param($bindKeys, ...$bindValues)) {
            $stmt->close();
            echo "<h1>Nelze získat informace ze SQL.</h1>";
            return false;
        }
    }
    if (!$stmt->execute()) {
        $stmt->close();
        echo "<h1>Nelze získat informace ze SQL.</h1>";
        return false;
    }
    $res = $stmt->get_result();
    if ($res == false) {
        $stmt->close();
        echo "<h1>Nelze získat informace ze SQL.</h1>";
        return false;
    }
    if ($res->num_rows == 0) {
        $stmt->close();
        echo "<h1>Nenalezeny žádné výsledky.</h1>";
        return false;
    }

    //Echo table header
    echo "<table class='$tableStyleClasses'>";
    echo "<tr>";
    foreach ($activeDisplayers as $key => $value) {
        if ($value[0]) {
            $order = isset($orderers[$key]) ? "order='$orderers[$key]'" : "";
            echo "<th filter='" . $name . "' $order getter='$key'>" . $filterDisplayers[$key]->displayName . "</th>";
        }
    }
    echo "</tr>";
    while ($result = $res->fetch_assoc()) {
        echo "<tr>";
        foreach ($activeDisplayers as $key => $value) {
            if ($value[0]) {
                if(strpos($key, "!") === 0) {
                    $call = substr($key,1);
                    echo "<td>" . $call($result) ."</td>";
                } else {
                    echo "<td>" . $result[$key] . "</td>";
                }
            }
        }
        echo "</tr>";
    }
    echo "</table>";
    $stmt->close();
    return true;
}
?>
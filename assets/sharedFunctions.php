<?php
const STANDARD_CZECH_DATETIME_FORMAT_FULL = 'd. m. Y H:i:s';
const STANDARD_CZECH_DATE_FORMAT_FULL = 'd. m. Y';
const STANDARD_CZECH_TIME_FORMAT_FULL = 'H:i:s';
const JS_TIME_FORMAT = 'Y-m-d\\TH:i';
const USE_LOG_COLOR = true;

enum userType: string
{
    case GENERIC = "Obecný";
    case KLAL = "KLAL";
    case NILE = "NILE";
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

enum userRole: string
{
    case ADMIN = "Správce systému";
    case ACCOUNTANT = "Účetní";
    case USER = "Uživatel";
    case ANY = "Obecný";
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
    case SELECTNUMERIC;
    case TEXTAREA;
}

enum filterCompareOperator: string
{
    case LIKE = "{COLUMN_NAME} LIKE ?";
    case EQUALS = "{COLUMN_NAME} = ?";
    case EQUALSNULLABLE = "((? IS NULL AND {COLUMN_NAME} IS NULL) OR {COLUMN_NAME} = ?)";
    case NOTEQUALS = "{COLUMN_NAME} != ?";
    case IS = "{COLUMN_NAME} IS NULL";
    case ISNOT = "{COLUMN_NAME} IS NOT NULL";
    case LESS = "{COLUMN_NAME} < ?";
    case LESSEQUALS = "{COLUMN_NAME} <= ?";
    case MORE = "{COLUMN_NAME} > ?";
    case MOREEQUALS = "{COLUMN_NAME} >= ?";
    case IN = "{COLUMN_NAME} IN (?)";
}

class filterSelector
{
    public string $displayName;
    public string $sqlName;
    public filterCompareOperator $sqlCompareOperator;
    public filterSelectorType $type;
    public string $getter;
    public array|null $settings;
    public bool $isHaving;
    /**
     * Summary of __construct
     * @param string $sqlName Needs full SQL name or HAVING with alias, Prefix with ! to make it callable -> function($result,$paramsForFunctions), function must RETURN value that will be compared to filter, not echo
     * @param string $displayName
     * @param string $getter
     * @param filterSelectorType $type
     * @param filterCompareOperator $sqlCompareOperator [SQL VALUE] OPERATOR [FILTER VALUE]
     * @param bool $isHaving
     * @param array|null $settings Configurate input field: list, min, max
     */
    public function __construct(string $sqlName, string $displayName, string $getter, filterSelectorType $type, filterCompareOperator $sqlCompareOperator, bool $isHaving = false, array|null $settings = null)
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
    public bool $defaultVisible;
    public filterSelectorType $valueFormat;
    public string $cellClasses;
    public string | null $cellValueFormatFunc;

    /**
     * Summary of __construct
     * @param string $sqlName Needs aliases, Prefix with ! to make it callable -> function($result), function must RETURN, not echo
     * @param string $displayName
     * @param bool $defaultVisible
     * @param null | callable $cellValueFormatFunc function($value), function must RETURN, not echo
     */
    public function __construct(string $sqlName, string $displayName, bool $defaultVisible, filterSelectorType $valueFormat = filterSelectorType::TEXT, string $cellClasses = "", callable|null $cellValueFormatFunc = null)
    {
        $this->sqlName = $sqlName;
        $this->displayName = $displayName;
        $this->defaultVisible = $defaultVisible;
        $this->valueFormat = $valueFormat;
        $this->cellClasses = $cellClasses;
        $this->cellValueFormatFunc = $cellValueFormatFunc;
    }
}

function questionmarkSolver(string $resultStatement, array $resultValues, string $resultKeys, string $statement1, array $values1, string $keys1, string $statement2, array $values2, string|null $keys2 = null): array
{
    $keys2 = ($keys2 == null ? str_repeat("s", count($values2)) : $keys2);
    $values1 = array_reverse($values1);
    $values2 = array_reverse($values2);
    $count1 = substr_count($statement1, "?");
    $count2 = substr_count($statement2, "?");
    $resultKeys .= substr($keys1, 0, $count1);
    $keys1 = substr($keys1, $count1);
    $resultStatement .= $statement1;
    for ($i = 0; $i < $count1; $i++) {
        $resultValues[] = array_pop($values1);
    }
    $resultKeys .= substr($keys2, 0, $count2);
    $keys2 = substr($keys2, $count2);
    if (strlen($statement2) != 0) {
        $resultStatement .= (strlen($statement1) == 0 ? "" : " AND ");
        $resultStatement .= $statement2;
        for ($i = 0; $i < $count2; $i++) {
            $resultValues[] = array_pop($values2);
        }
    }
    $values1 = array_reverse($values1);
    $values2 = array_reverse($values2);
    return ["statement" => $resultStatement, "values" => $resultValues, "keys" => $resultKeys, "values1" => $values1, "values2" => $values2, "keys1" => $keys1, "keys2" => $keys2];
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
function setupFilteredTable(mysqli $conn, mixed $paramsForFunctions, string $tableStyleClasses, string $rawSelect, string $rawFrom, string $rawWhere, string $rawGroupBy, string $rawHaving, string $rawOrderBy, string $bindKeys, array|null $bindValues, array $filterSelectorsRaw, array $filterDisplayersRaw): bool
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
        if ($value == null) {
            continue;
        }
        if (str_contains($value->sqlName, ",")) {
            errorToConsole("SQL name can not contain \",\": " . $value->sqlName);
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
    $valuesWhere = [];
    $valuesHaving = [];
    $filterWhere = "";
    $filterHaving = "";
    $filterKeysWhere = "";
    $filterKeysHaving = "";
    foreach ($filterSelectors as $key => $value) {
        //logToConsole("Checking: " . $value->getter);
        if (isset($_GET[$value->getter])) {
            //logToConsole("Present: " . $value->getter);
            $activeFilters[$value->getter] = [true, $value->displayName];
            //Prepare input
            $type = "";
            $keySQL = "s";
            if ($value->type == filterSelectorType::BOOLEAN || $value->type == filterSelectorType::BOOLEAN_NULL) {
                $type = "checkbox";
                $keySQL = "i";
            } else if ($value->type == filterSelectorType::DATE) {
                $type = "date";
                $keySQL = "s";
            } else if ($value->type == filterSelectorType::TIME) {
                $type = "time";
                $keySQL = "s";
            } else if ($value->type == filterSelectorType::DATETIME) {
                $type = "datetime-local";
                $keySQL = "s";
            } else if ($value->type == filterSelectorType::NUMBER) {
                $type = "number";
                $keySQL = "i";
            } else if ($value->type == filterSelectorType::TEXT) {
                $type = "text";
                $keySQL = "s";
            } else if ($value->type == filterSelectorType::SELECT) {
                $type = "select";
                $keySQL = "s";
            } else if ($value->type == filterSelectorType::SELECTNUMERIC) {
                $type = "select";
                $keySQL = "i";
            } else if ($value->type == filterSelectorType::TEXTAREA) {
                $type = "textarea";
                $keySQL = "s";
            }

            //Get value
            $label = $value->displayName;
            $getter = $value->getter;
            $get = $_GET[$getter];
            //logToConsole($get);
            if ($get == NULL) {
                $get = "null";
            }
            $get = $get == "NULL" ? NULL : $get;
            $get = $get == "null" ? "" : $get;
            $list = isset($value->settings["listId"]) ? $value->settings["listId"] : "";
            $min = isset($value->settings["min"]) ? (" min='" . $value->settings["min"] . "'") : "";
            $max = isset($value->settings["max"]) ? (" max='" . $value->settings["max"] . "'") : "";
            $filterFieldId = isset($value->settings["filterFieldId"]) ? ("filter-field-id=" . $value->settings["filterFieldId"]) : "";
            $getValueString = $get === NULL ? "NULL" : $get;
            //logToConsole($getValueString);
            echo "<form-input label='$label' getter='$getter' type='$type' original-value='$getValueString' value='$getValueString' do-change-check list='$list' $min $max $filterFieldId></form-input>";

            //Sort special types
            $comparator = $value->sqlCompareOperator->value;
            if ($value->type == filterSelectorType::BOOLEAN_NULL) {
                $getCheck = filter_var($get, FILTER_VALIDATE_BOOLEAN);
                $get = NULL;
                if ($value->sqlCompareOperator == filterCompareOperator::ISNOT) {
                    $comparator = $getCheck ? filterCompareOperator::ISNOT->value : filterCompareOperator::IS->value;
                } else if ($value->sqlCompareOperator == filterCompareOperator::IS) {
                    $comparator = $getCheck ? filterCompareOperator::IS->value : filterCompareOperator::ISNOT->value;
                }
            } else if ($value->type == filterSelectorType::BOOLEAN) {
                $getCheck = filter_var($get, FILTER_VALIDATE_BOOLEAN);
                $get = $getCheck ? 1 : 0;
            }

            //Build WHERE or HAVING
            if (strpos($value->sqlName, "!") !== 0) {
                //Prepare added values
                if ($value->sqlCompareOperator == filterCompareOperator::LIKE) {
                    $get = "%" . $get . "%";
                }
                $add = ($value->isHaving ? $filterHaving : $filterWhere);
                $add .= ((strlen($add) == 0) ? "" : " AND ");

                //Fill comparator
                $comparator = str_replace("{COLUMN_NAME}", $value->sqlName, $comparator);
                $countOfValues = substr_count($comparator, "?");
                $add .= $comparator;

                //Add to correct place
                if ($value->isHaving) {
                    $filterHaving = $add;
                    if ($value->type != filterSelectorType::BOOLEAN_NULL) {
                        for ($i = 0; $i < $countOfValues; $i++) {
                            $valuesHaving[] = $get;
                        }
                        $filterKeysHaving .= str_repeat($keySQL, $countOfValues);
                    }
                } else {
                    $filterWhere = $add;
                    if ($value->type != filterSelectorType::BOOLEAN_NULL) {
                        for ($i = 0; $i < $countOfValues; $i++) {
                            $valuesWhere[] = $get;
                        }
                        $filterKeysWhere .= str_repeat($keySQL, $countOfValues);
                    }
                }
            }
        } else {
            $activeFilters[$value->getter] = [false, $value->displayName];
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
        if ($value == null) {
            continue;
        }
        if ($filterDisplayersGet == null) {
            $activeDisplayers[$value->sqlName] = [$value->defaultVisible, $value];
        } else {
            $activeDisplayers[$value->sqlName] = [in_array($value->sqlName, $filterDisplayersGet, true), $value];
        }
    }
    foreach ($activeDisplayers as $key => $value) {
        $activeDisplayersForJson[$key] = [$value[0], $value[1]->displayName];
    }

    //Place buttons
    $activeFiltersJson = json_encode($activeFilters);
    $activeDisplayersJson = json_encode($activeDisplayersForJson);
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
            $valueTrimmed = strpos($value, "!") === 0 ? substr($value, 1) : $value;
            if (!isset($filterDisplayers[$valueTrimmed])) {
                continue;
            }
            $desc = "";
            if (strpos($value, "!") === 0) {
                $value = substr($value, 1);
                $desc = " DESC";
                $orderers[$value] = "$key.D";
            } else {
                $orderers[$value] = "$key.A";
            }
            $rawOrderBy .= (strlen($rawOrderBy) == 0 ? "" : ", ") . $value . $desc;
        }
    }

    //Perform joining of parts
    $sqlOffset = ($page - 1) * $step;
    ["statement" => $resultStatement, "values" => $resultValues, "keys" => $resultKeys, "values1" => $values1, "values2" => $values2, "keys1" => $keys1, "keys2" => $keys2] = questionmarkSolver("SELECT ", [], "", $rawSelect, $bindValues, $bindKeys, "", []);
    if (strlen($rawFrom) != 0) {
        $resultStatement .= " FROM ";
        ["statement" => $resultStatement, "values" => $resultValues, "keys" => $resultKeys, "values1" => $values1, "values2" => $values2, "keys1" => $keys1, "keys2" => $keys2] = questionmarkSolver($resultStatement, $resultValues, $resultKeys, $rawFrom, $values1, $keys1, "", []);
    }
    if (strlen($rawWhere) != 0 || strlen($filterWhere) != 0) {
        $resultStatement .= " WHERE ";
        ["statement" => $resultStatement, "values" => $resultValues, "keys" => $resultKeys, "values1" => $values1, "values2" => $values2, "keys1" => $keys1, "keys2" => $keys2] = questionmarkSolver($resultStatement, $resultValues, $resultKeys, $rawWhere, $values1, $keys1, $filterWhere, $valuesWhere, $filterKeysWhere);
    }
    if (strlen($rawGroupBy) != 0) {
        $resultStatement .= " GROUP BY ";
        ["statement" => $resultStatement, "values" => $resultValues, "keys" => $resultKeys, "values1" => $values1, "values2" => $values2, "keys1" => $keys1, "keys2" => $keys2] = questionmarkSolver($resultStatement, $resultValues, $resultKeys, $rawGroupBy, $values1, $keys1, "", []);
    }
    if (strlen($rawHaving) != 0 || strlen($filterHaving) != 0) {
        $resultStatement .= " HAVING ";
        ["statement" => $resultStatement, "values" => $resultValues, "keys" => $resultKeys, "values1" => $values1, "values2" => $values2, "keys1" => $keys1, "keys2" => $keys2] = questionmarkSolver($resultStatement, $resultValues, $resultKeys, $rawHaving, $values1, $keys1, $filterHaving, $valuesHaving, $filterKeysHaving);
    }
    if (strlen($rawOrderBy) != 0) {
        $resultStatement .= " ORDER BY ";
        ["statement" => $resultStatement, "values" => $resultValues, "keys" => $resultKeys, "values1" => $values1, "values2" => $values2, "keys1" => $keys1, "keys2" => $keys2] = questionmarkSolver($resultStatement, $resultValues, $resultKeys, $rawOrderBy, $values1, $keys1, "", []);
    }
    $resultStatement .= " LIMIT ";
    ["statement" => $resultStatement, "values" => $resultValues, "keys" => $resultKeys, "values1" => $values1, "values2" => $values2, "keys1" => $keys1, "keys2" => $keys2] = questionmarkSolver($resultStatement, $resultValues, $resultKeys, "?,?", [$sqlOffset, $step], "ii", "", []);

    //Convert to references
    $references = [];
    $references[] = $resultKeys;
    foreach ($resultValues as $key => $value) {
        $references[$key + 1] = &$resultValues[$key];
    }

    logToConsole($resultStatement);
    $stmt = $conn->prepare($resultStatement);
    if (strlen($resultKeys) > 0) {
        if (!call_user_func_array([$stmt, 'bind_param'], $references)) {
            $stmt->close();
            echo "<h1>Nelze získat informace ze SQL BIND.</h1>";
            echo "<script type='module' src='../assets/phpFilters.js'></script>";
            return false;
        }
    }
    if (!$stmt->execute()) {
        $stmt->close();
        echo "<h1>Nelze získat informace ze SQL EXECUTE.</h1>";
        echo "<script type='module' src='../assets/phpFilters.js'></script>";
        return false;
    }
    $res = $stmt->get_result();
    if ($res == false) {
        $stmt->close();
        echo "<h1>Nelze získat informace ze SQL.</h1>";
        echo "<script type='module' src='../assets/phpFilters.js'></script>";
        return false;
    }
    if ($res->num_rows == 0) {
        $stmt->close();
        echo "<h1>Nenalezeny žádné výsledky.</h1>";
        echo "<script type='module' src='../assets/phpFilters.js'></script>";
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

    //Get filters with functions
    $filterSelectorsFunctions = [];
    foreach ($filterSelectors as $key => $value) {
        if (isset($_GET[$value->getter])) {
            if (strpos($value->sqlName, "!") === 0) {
                $filterSelectorsFunctions[] = $value;
            }
        }
    }

    //Process SQL
    while ($result = $res->fetch_assoc()) {
        $compare = true;
        //logToConsole("Processing entry");
        //Compare filters
        if (count($filterSelectorsFunctions) != 0) {
            foreach ($filterSelectorsFunctions as $key => $value) {
                //logToConsole("Filtering");
                $compare = false;
                $call = substr($value->sqlName, 1);
                $callResult = $call($result, $paramsForFunctions);
                $get = $_GET[$value->getter];
                if ($value->sqlCompareOperator == filterCompareOperator::EQUALS) {
                    $compare = $callResult == $get;
                } else if ($value->sqlCompareOperator == filterCompareOperator::IS) {
                    $compare = $callResult === $get;
                } else if ($value->sqlCompareOperator == filterCompareOperator::LESS) {
                    $compare = $callResult < $get;
                } else if ($value->sqlCompareOperator == filterCompareOperator::LESSEQUALS) {
                    $compare = $callResult <= $get;
                } else if ($value->sqlCompareOperator == filterCompareOperator::MORE) {
                    $compare = $callResult > $get;
                } else if ($value->sqlCompareOperator == filterCompareOperator::MOREEQUALS) {
                    $compare = $callResult >= $get;
                } else if ($value->sqlCompareOperator == filterCompareOperator::NOTEQUALS) {
                    $compare = $callResult != $get;
                } else if ($value->sqlCompareOperator == filterCompareOperator::ISNOT) {
                    $compare = $callResult !== $get;
                } else if ($value->sqlCompareOperator == filterCompareOperator::LIKE) {
                    $compare = strpos($callResult, $get) !== false;
                } else {
                    errorToConsole("No compare funtion found for: " . $value->sqlCompareOperator->value);
                }
                if ($compare === false) {
                    break;
                }
            }
        }
        if ($compare === false) {
            continue;
        }

        //Put rows
        //logToConsole("Writing entry");
        echo "<tr>";
        foreach ($activeDisplayers as $key => $value) {
            if ($value[0]) {
                $cellClasses = $value[1]->cellClasses;
                if (strpos($key, "!") === 0) {
                    $call = substr($key, 1);
                    echo "<td class='$cellClasses'>" . $call($result, $paramsForFunctions) . "</td>";
                } else {
                    $formated = $result[$key];
                    //Try to format bool
                    if ($value[1]->valueFormat == filterSelectorType::BOOLEAN || $value[1]->valueFormat == filterSelectorType::BOOLEAN_NULL) {
                        $parsed = filter_var($formated, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                        if ($parsed !== null) {
                            $formated = $parsed ? "Ano" : "Ne";
                        }
                    }
                    //Try to format date
                    if ($value[1]->valueFormat == filterSelectorType::DATE) {
                        if ($formated != null) {
                            $formated = (new DateTime($formated))->format(STANDARD_CZECH_DATE_FORMAT_FULL);
                        }
                    }

                    //Try to format time
                    if ($value[1]->valueFormat == filterSelectorType::TIME) {
                        if ($formated != null) {
                            $formated = (new DateTime($formated))->format(STANDARD_CZECH_TIME_FORMAT_FULL);
                        }
                    }

                    //Try to format datetime
                    if ($value[1]->valueFormat == filterSelectorType::DATETIME) {
                        if ($formated != null) {
                            $formated = (new DateTime($formated))->format(STANDARD_CZECH_DATETIME_FORMAT_FULL);
                        }
                    }

                    //Try to format number
                    if ($value[1]->valueFormat == filterSelectorType::NUMBER) {
                        if ($formated == null) {
                            $formated = 0;
                        }
                    }

                    //Try to format using function
                    if ($value[1]->cellValueFormatFunc !== null) {
                        $call = $value[1]->cellValueFormatFunc;
                        $formated = $call($formated);
                    }

                    //Format NULL
                    if ($formated === NULL) {
                        $formated = "Není k dispozici";
                    }
                    echo "<td class='$cellClasses'>" . $formated . "</td>";
                }
            }
        }
        echo "</tr>";
    }
    echo "</table>";
    $stmt->close();
    echo "<script type='module' src='../assets/phpFilters.js'></script>";
    return true;
}
?>
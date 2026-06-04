<?php
require_once "../assets/sharedFunctions.php";
enum accessLevelEventNeedence
{
    case NEEDS_NONE;
    case NEEDS_EVENT;
    case NEEDS_SUBEVENT;
    case NEEDS_COMPANY_DAY;
}

enum accessLevelTitlebarButton
{
    case NONE;
    case LEFT;
    case RIGHT;
}

class accessLevel
{
    public userType $eventType;
    public accessLevelEventNeedence $eventNeedance;
    public array $accessRoles;
    public accessLevelTitlebarButton $titlebarButton;
    public string $titlebarButtonText;
    public string $titlebarButtonColorClass;
    public function __construct(userType $eventType, accessLevelEventNeedence $eventNeedance, array $accessRoles, accessLevelTitlebarButton $titlebarButton = accessLevelTitlebarButton::NONE, string $titlebarButtonText = "", string $titlebarButtonColorClass = "formOkColor")
    {
        $this->eventType = $eventType;
        $this->eventNeedance = $eventNeedance;
        $this->accessRoles = $accessRoles;
        $this->titlebarButton = $titlebarButton;
        $this->titlebarButtonText = $titlebarButtonText;
        $this->titlebarButtonColorClass = $titlebarButtonColorClass;
    }
}

$accessLevels = array(
    "admin.php" => new accessLevel(userType::GENERIC, accessLevelEventNeedence::NEEDS_NONE, array(userRole::ADMIN, userRole::ACCOUNTANT), accessLevelTitlebarButton::LEFT, "Hlavní menu"),
    "attendant.php" => new accessLevel(userType::KLAL, accessLevelEventNeedence::NEEDS_NONE, array(userRole::ADMIN)),
    "attendants.php" => new accessLevel(userType::KLAL, accessLevelEventNeedence::NEEDS_EVENT, array(userRole::ADMIN), accessLevelTitlebarButton::LEFT, "Zájemci"),
    "school.php" => new accessLevel(userType::KLAL, accessLevelEventNeedence::NEEDS_NONE, array(userRole::ADMIN)),
    "schools.php" => new accessLevel(userType::KLAL, accessLevelEventNeedence::NEEDS_NONE, array(userRole::ADMIN), accessLevelTitlebarButton::LEFT, "Školy"),
    "schoolsAll.php" => new accessLevel(userType::KLAL, accessLevelEventNeedence::NEEDS_NONE, array(userRole::ADMIN)),
    "classroom.php" => new accessLevel(userType::GENERIC, accessLevelEventNeedence::NEEDS_NONE, array(userRole::ADMIN)),
    "classrooms.php" => new accessLevel(userType::GENERIC, accessLevelEventNeedence::NEEDS_NONE, array(userRole::ADMIN), accessLevelTitlebarButton::LEFT, "Učebny"),
    "payments.php" => new accessLevel(userType::KLAL, accessLevelEventNeedence::NEEDS_NONE, array(userRole::ADMIN, userRole::ACCOUNTANT), accessLevelTitlebarButton::LEFT, "Platby"),
    "presets.php" => new accessLevel(userType::GENERIC, accessLevelEventNeedence::NEEDS_NONE, array(userRole::ADMIN), accessLevelTitlebarButton::LEFT, "Šablony"),
    "fs.php" => new accessLevel(userType::GENERIC, accessLevelEventNeedence::NEEDS_NONE, array(userRole::ADMIN), accessLevelTitlebarButton::LEFT, "Soubory"),
    "sendMail.php" => new accessLevel(userType::GENERIC, accessLevelEventNeedence::NEEDS_NONE, array(userRole::ADMIN), accessLevelTitlebarButton::LEFT, "Komunikace"),
    "event.php" => new accessLevel(userType::GENERIC, accessLevelEventNeedence::NEEDS_NONE, array(userRole::ADMIN)),
    "subevent.php" => new accessLevel(userType::GENERIC, accessLevelEventNeedence::NEEDS_NONE, array(userRole::ADMIN)),
    "companyDay.php" => new accessLevel(userType::NILE, accessLevelEventNeedence::NEEDS_NONE, array(userRole::ADMIN)),
    "user.php" => new accessLevel(userType::GENERIC, accessLevelEventNeedence::NEEDS_NONE, array(userRole::ADMIN)),
    "map.php" => new accessLevel(userType::NILE, accessLevelEventNeedence::NEEDS_COMPANY_DAY, array(userRole::ADMIN), accessLevelTitlebarButton::LEFT, "Mapa"),
    "events.php" => new accessLevel(userType::GENERIC, accessLevelEventNeedence::NEEDS_NONE, array(userRole::ADMIN, userRole::ACCOUNTANT), accessLevelTitlebarButton::RIGHT, "Správa událostí"),
    "users.php" => new accessLevel(userType::GENERIC, accessLevelEventNeedence::NEEDS_NONE, array(userRole::ADMIN), accessLevelTitlebarButton::RIGHT, "Správa uživatelů"),
    "logout.php" => new accessLevel(userType::GENERIC, accessLevelEventNeedence::NEEDS_NONE, array(userRole::ANY), accessLevelTitlebarButton::RIGHT, "Odhlásit se"),
    "accessDenied.php" => new accessLevel(userType::GENERIC, accessLevelEventNeedence::NEEDS_NONE, array(userRole::ANY)),
    "index.php" => new accessLevel(userType::GENERIC, accessLevelEventNeedence::NEEDS_NONE, array(userRole::ANY)),
);

function checkAccess(string $file, userRoleType $roleType): bool
{
    global $accessLevels;
    $roles = $accessLevels[$file]->accessRoles;
    $eventType = $accessLevels[$file]->eventType;
    if (isset($roles) && isset($eventType) && isset($roleType) && isset($roleType->role) && isset($roleType->type)) {
        return (in_array($roleType->role, $roles, true) || in_array(userRole::ANY, $roles, true)) && ($eventType == userType::GENERIC || $roleType->type == userType::GENERIC || $eventType == $roleType->type);
    }
    return false;
}

function setEventId($id): void
{
    setcookie("adminEventId", $id, time() + 60 * 60 * 24 * 30);
}
function setSubeventId($id): void
{
    setcookie("adminSubeventId", $id, time() + 60 * 60 * 24 * 30);
}
function setCompanyDayId($id): void
{
    setcookie("adminCompanyDayId", $id, time() + 60 * 60 * 24 * 30);
}

class titlebarSetupResult
{
    public string $message;
    public bool $allowView;
    public string $username;
    //public  bool $allowEdit;
    public userRoleType $roleType;
    public int|null $eventId;
    public int|null $subeventId;
    public int|null $companyDayId;

    public function __construct(string $message, bool $allowView, int|null $eventId, int|null $subeventId, int|null $companyDayId /*$allowEdit*/)
    {
        $this->allowView = $allowView;
        //$this->allowEdit = $allowEdit;
        $this->message = $message;
        $this->eventId = $eventId;
        $this->subeventId = $subeventId;
        $this->companyDayId = $companyDayId;
    }

    public function checkType(userType $targetType, bool $useIdsOnGeneric = false): bool
    {
        $type = $this->getUserType($useIdsOnGeneric);
        if ($type == userType::GENERIC) {
            return true;
        } else {
            return $type == $targetType;
        }
    }

    public function getUserType(bool $useIdsOnGeneric = false): userType
    {
        if ($this->roleType->type == userType::GENERIC) {
            if (!$useIdsOnGeneric) {
                return userType::GENERIC;
            }
            if ($this->eventId != null || $this->subeventId != null) {
                return userType::KLAL;
            }
            if ($this->companyDayId != null) {
                return userType::NILE;
            }
            return userType::GENERIC;
        }
        return $this->roleType->type;
    }
}

function checkGenericCompatibility(titlebarSetupResult $result, userType $eventType): bool
{
    if (($result->eventId != null || $result->subeventId != null) && $eventType == userType::NILE) {
        return false;
    }
    if ($result->companyDayId != null && $eventType == userType::KLAL) {
        return false;
    }
    return true;
}

function setupTitlebarAdmin(mysqli $conn, string $page): titlebarSetupResult
{
    //DEBUG
    $_SESSION["userId"] = 4;

    //Get global variables + user role
    global $accessLevels;
    require_once "../assets/sharedFunctions.php";
    $roleType = getUserRoleType($conn, $_SESSION["userId"]);
    $username = join(" ", getUserName($_SESSION["userId"]));

    //Check access level
    if (!checkAccess($page, $roleType)) {
        header("Location: ./accessDenied.php");
        $result = new titlebarSetupResult("", false, null, null, null);
        $result->roleType = $roleType;
        return $result;
    }

    //Check actions
    $accessLevel = $accessLevels[$page];
    $result = setupTitlebarAdminAction($conn, $accessLevel);
    $result->roleType = $roleType;
    $result->username = $username;

    //Check for invalid combinations
    if (($result->eventId != null || $result->subeventId != null) && $result->getUserType() == userType::NILE) {
        header("Location: ./events.php?invalidCombination=1");
        return new titlebarSetupResult("NENÍ", false, null, null, null);
    }
    if ($result->companyDayId != null && $result->getUserType() == userType::KLAL) {
        header("Location: ./events.php?invalidCombination=1");
        return new titlebarSetupResult("NENÍ", false, null, null, null);
    }
    if (!checkGenericCompatibility($result, $accessLevel->eventType)) {
        header("Location: ./accessDenied.php");
        return new titlebarSetupResult("NENÍ", false, null, null, null);
    }

    //Prepare HTML

    //======
    //fix formJustifyRight
    //======
    echo '<a href="./events.php" style="all: unset; cursor: pointer;"><h1> Akce: ' . $result->message . '</h1></a><h1 class="formJustifyRight">' . $result->username . '</h1>';
    echo "<div class='formButtonBoxHolder'>";

    //Generate buttons
    $buttonsLeftHtml = "";
    $buttonsRightHtml = "";
    $counter = 100;
    foreach ($accessLevels as $key => $value) {
        if ($value->titlebarButton != accessLevelTitlebarButton::NONE) {
            if (checkAccess($key, $roleType) && checkGenericCompatibility($result, $value->eventType)) {
                $text = $value->titlebarButtonText;
                $colorClass = $page == $key ? "purkynkaButtonGreen" : "";
                $line = "<a tabindex='-1' href='$key'><button class='formButton purkynkaButton $colorClass' tabindex='$counter'>$text</button></a>";
                if ($value->titlebarButton == accessLevelTitlebarButton::LEFT) {
                    $buttonsLeftHtml .= $line;
                } else {
                    $buttonsRightHtml .= $line;
                }
                $counter++;
            }
        }
    }

    //Generate buttons HTML
    echo "<div class='formButtonBox formJustifyLeft'>";
    echo $buttonsLeftHtml;
    echo "</div>";
    echo "<div class='formButtonBox formJustifyRight'>";
    echo $buttonsRightHtml;
    echo "</div>";
    echo "</div>";
    return $result;
}
function setupTitlebarAdminAction(mysqli $conn, accessLevel $accessLevel): titlebarSetupResult
{
    //Check if already redirected due to noEventId
    if (isset($_GET["noEventId"])) {
        return new titlebarSetupResult("NENÍ", true, null, null, null);
    }

    //Check if already redirected due to noCompanyDayId
    if (isset($_GET["noCompanyDayId"])) {
        return new titlebarSetupResult("NENÍ", true, null, null, null);
    }

    //Check if already redirected due to invalidCombination
    if (isset($_GET["invalidCombination"])) {
        return new titlebarSetupResult("NEPLATNÁ KOMBINACE", true, null, null, null);
    }

    //Check for invalid combinations
    if ((isset($_COOKIE["adminEventId"]) || isset($_COOKIE["adminSubeventId"])) && isset($_COOKIE["adminCompanyDayId"])) {
        header("Location: ./events.php?invalidCombination=1");
        return new titlebarSetupResult("NENÍ", false, null, null, null);
    }

    //Check if event cookie exist and refresh it
    if (isset($_COOKIE["adminEventId"])) {
        setEventId($_COOKIE["adminEventId"]);
        //Check if event exists
        $name = 0;
        $stmt = $conn->prepare("SELECT name FROM events_teamPropaganda WHERE id_events=?;");
        if (!$stmt->bind_param("i", $_COOKIE["adminEventId"]) || !$stmt->execute() || !$stmt->store_result() || !$stmt->bind_result($name) || !$stmt->fetch() || !$stmt->close() || $name == "") {
            if ($accessLevel->eventNeedance == accessLevelEventNeedence::NEEDS_EVENT || $accessLevel->eventNeedance == accessLevelEventNeedence::NEEDS_SUBEVENT) {
                header("Location: ./events.php?noEventId=1");
                return new titlebarSetupResult("NENÍ", false, null, null, null);
            }
            setEventId("");
            setSubeventId("");
            return new titlebarSetupResult("NENÍ", true, null, null, null);
        }

        //Check if already redirected due to noSubeventId
        if (isset($_GET["noSubeventId"])) {
            return new titlebarSetupResult($name, true, $_COOKIE["adminEventId"], null, null);
        }

        //Check if event subcookie exist and refresh it
        if (!isset($_COOKIE["adminSubeventId"])) {
            if ($accessLevel->eventNeedance == accessLevelEventNeedence::NEEDS_SUBEVENT) {
                header("Location: ./events.php?noSubeventId=1");
                return new titlebarSetupResult($name, false, $_COOKIE["adminEventId"], null, null);
            }
            return new titlebarSetupResult($name, true, $_COOKIE["adminEventId"], null, null);
        }
        setSubeventId($_COOKIE["adminSubeventId"]);

        //Check if subevent exists
        $date = "";
        $stmt = $conn->prepare("SELECT date FROM subevents_teamPropaganda WHERE id_subevents=?;");
        if (!$stmt->bind_param("i", $_COOKIE["adminSubeventId"]) || !$stmt->execute() || !$stmt->store_result() || !$stmt->bind_result($date) || !$stmt->fetch() || !$stmt->close() || $date == "") {
            if ($accessLevel->eventNeedance == accessLevelEventNeedence::NEEDS_SUBEVENT) {
                header("Location: ./events.php?noSubeventId=1");
                return new titlebarSetupResult($name, false, $_COOKIE["adminEventId"], null, null);
            }
            setSubeventId("");
            return new titlebarSetupResult($name, true, $_COOKIE["adminEventId"], null, null);
        }

        //All OK
        return new titlebarSetupResult($name . " → " . DateTime::createFromFormat('Y-m-d', $date)->format(STANDARD_CZECH_DATE_FORMAT_FULL), true, $_COOKIE["adminEventId"], $_COOKIE["adminSubeventId"], null);
    } else if (isset($_COOKIE["adminCompanyDayId"])) {
        //Check if company day cookie exist and refresh it
        setCompanyDayId($_COOKIE["adminCompanyDayId"]);
        //Check if event exists
        $name = "";
        $date = "";
        $stmt = $conn->prepare("SELECT name, date FROM company_days_teamPropaganda WHERE id_company_days=?;");
        if (!$stmt->bind_param("i", $_COOKIE["adminCompanyDayId"]) || !$stmt->execute() || !$stmt->store_result() || !$stmt->bind_result($name, $date) || !$stmt->fetch() || !$stmt->close() || $name == "") {
            if ($accessLevel->eventNeedance == accessLevelEventNeedence::NEEDS_COMPANY_DAY) {
                header("Location: ./events.php?noCompanyDayId=1");
                return new titlebarSetupResult("NENÍ", false, null, null, null);
            } else {
                setCompanyDayId("");
                return new titlebarSetupResult("NENÍ", true, null, null, null);
            }
        } else {
            //All OK
            return new titlebarSetupResult($name . " → " . DateTime::createFromFormat('Y-m-d', $date)->format(STANDARD_CZECH_DATE_FORMAT_FULL), true, null, null, $_COOKIE["adminCompanyDayId"]);
        }
    } else {
        if ($accessLevel->eventNeedance == accessLevelEventNeedence::NEEDS_EVENT) {
            header("Location: ./events.php?noEventId=1");
            return new titlebarSetupResult("NENÍ", false, null, null, null);
        } else if ($accessLevel->eventNeedance == accessLevelEventNeedence::NEEDS_SUBEVENT) {
            header("Location: ./events.php?noSubeventId=1");
            return new titlebarSetupResult("NENÍ", false, $_COOKIE["adminEventId"], null, null);
        } else if ($accessLevel->eventNeedance == accessLevelEventNeedence::NEEDS_COMPANY_DAY) {
            header("Location: ./events.php?noCompanyDayId=1");
            return new titlebarSetupResult("NENÍ", false, null, null, null);
        }
        setEventId("");
        setSubeventId("");
        setCompanyDayId("");
        return new titlebarSetupResult("NENÍ", true, null, null, null);
    }
}

<?php
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
    "admin.php" => new accessLevel(userType::GENERIC, accessLevelEventNeedence::NEEDS_NONE, array(userRole::ADMIN, userRole::ACCOUNTANT), accessLevelTitlebarButton::RIGHT, "Hlavní menu"),
    "attendant.php" => new accessLevel(userType::KLAL, accessLevelEventNeedence::NEEDS_NONE, array(userRole::ADMIN)),
    "attendants.php" => new accessLevel(userType::KLAL, accessLevelEventNeedence::NEEDS_EVENT, array(userRole::ADMIN), accessLevelTitlebarButton::RIGHT, "Zájemci"),
    "school.php" => new accessLevel(userType::KLAL, accessLevelEventNeedence::NEEDS_NONE, array(userRole::ADMIN)),
    "schools.php" => new accessLevel(userType::KLAL, accessLevelEventNeedence::NEEDS_EVENT, array(userRole::ADMIN), accessLevelTitlebarButton::RIGHT, "Školy"),
    "schoolsAll.php" => new accessLevel(userType::KLAL, accessLevelEventNeedence::NEEDS_NONE, array(userRole::ADMIN)),
    "classroom.php" => new accessLevel(userType::GENERIC, accessLevelEventNeedence::NEEDS_NONE, array(userRole::ADMIN)),
    "classrooms.php" => new accessLevel(userType::GENERIC, accessLevelEventNeedence::NEEDS_NONE, array(userRole::ADMIN), accessLevelTitlebarButton::RIGHT, "Učebny"),
    "payments.php" => new accessLevel(userType::KLAL, accessLevelEventNeedence::NEEDS_NONE, array(userRole::ADMIN), accessLevelTitlebarButton::RIGHT, "Platby"),
    "presets.php" => new accessLevel(userType::GENERIC, accessLevelEventNeedence::NEEDS_NONE, array(userRole::ADMIN), accessLevelTitlebarButton::RIGHT, "Šablony"),
    "fs.php" => new accessLevel(userType::GENERIC, accessLevelEventNeedence::NEEDS_NONE, array(userRole::ADMIN), accessLevelTitlebarButton::RIGHT, "Soubory"),
    "sendMail.php" => new accessLevel(userType::GENERIC, accessLevelEventNeedence::NEEDS_NONE, array(userRole::ADMIN), accessLevelTitlebarButton::RIGHT, "Komunikace"),
    "event.php" => new accessLevel(userType::KLAL, accessLevelEventNeedence::NEEDS_NONE, array(userRole::ADMIN)),
    "subevent.php" => new accessLevel(userType::KLAL, accessLevelEventNeedence::NEEDS_NONE, array(userRole::ADMIN)),
    "user.php" => new accessLevel(userType::GENERIC, accessLevelEventNeedence::NEEDS_NONE, array(userRole::ADMIN)),
    "users.php" => new accessLevel(userType::GENERIC, accessLevelEventNeedence::NEEDS_NONE, array(userRole::ADMIN), accessLevelTitlebarButton::LEFT, "Správa uživatelů"),
    "events.php" => new accessLevel(userType::GENERIC, accessLevelEventNeedence::NEEDS_NONE, array(userRole::ADMIN, userRole::ACCOUNTANT), accessLevelTitlebarButton::LEFT, "Správa událostí"),
    "logout.php" => new accessLevel(userType::GENERIC, accessLevelEventNeedence::NEEDS_NONE, array("*"), accessLevelTitlebarButton::LEFT, "Odhlásit se"),
    "accessDenied.php" => new accessLevel(userType::GENERIC, accessLevelEventNeedence::NEEDS_NONE, array("*")),
    "index.php" => new accessLevel(userType::GENERIC, accessLevelEventNeedence::NEEDS_NONE, array("*")),
);

function checkAccess(string $file, userRoleType $roleType): bool
{
    global $accessLevels;
    $roles = $accessLevels[$file]->accessRoles;
    $eventType = $accessLevels[$file]->eventType;
    if (isset($roles) && isset($eventType) && isset($roleType) && isset($roleType->role) && isset($roleType->type)) {
        return (in_array($roleType->role, $roles, true) || in_array("*", $roles, true)) && ($eventType == userType::GENERIC || $roleType->type == $roleType->type);
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

class titlebarSetupResult
{
    public string $message;
    public bool $allowView;
    //public  bool $allowEdit;
    public userRoleType $roleType;
    public int|null $eventId;
    public int|null $subeventId;
    public int|null $companyDaysId;

    public function __construct(string $message, bool $allowView, int|null $eventId, int|null $subeventId /*$allowEdit*/)
    {
        $this->allowView = $allowView;
        //$this->allowEdit = $allowEdit;
        $this->message = $message;
        $this->eventId = $eventId;
        $this->subeventId = $subeventId;
    }
}

function setupTitlebarAdmin(mysqli $conn, string $page): titlebarSetupResult
{
    //DEBUG
    $_SESSION["userId"] = 4;

    //Get global variables + user role
    global $accessLevels;
    require_once "../assets/sharedFunctions.php";
    $roleType = getUserRoleType($conn, $_SESSION["userId"]);

    //Check access level
    if (!checkAccess($page, $roleType)) {
        header("Location: ./accessDenied.php");
        $result = new titlebarSetupResult("", false, null, null);
        $result->roleType = $roleType;
        return $result;
    }

    //Prepare HTML
    $result = setupTitlebarAdminAction($conn, $accessLevels[$page]);
    $result->roleType = $roleType;
    echo '<h1> Akce: ' . $result->message . '</h1>';
    echo "<div class='formButtonBoxHolder'>";

    //Generate buttons
    $buttonsLeftHtml = "";
    $buttonsRightHtml = "";
    foreach ($accessLevels as $key => $value) {
        if ($value->hasTitlebarButton) {
            if (checkAccess($key, $role)) {
                $text = $value->titlebarButtonText;
                $colorClass = $page == $key ? "purkynkaButtonGreen" : "";
                $line = "<a href='$key'><button class='formButton purkynkaButton $colorClass'>$text</button></a>";
                if ($value->hasTitlebarButtonLeft) {
                    $buttonsLeftHtml .= $line;
                } else {
                    $buttonsRightHtml .= $line;
                }
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
        return new titlebarSetupResult("NENÍ", true, null, null);
    }

    //Check if already redirected due to noCompanyDayId
    if (isset($_GET["noCompanyDayId"])) {
        return new titlebarSetupResult("NENÍ", true, null, null);
    }

    //Check if event cookie exist and refresh it
    if (!isset($_COOKIE["adminEventId"])) {
        if ($accessLevel->eventNeedance == accessLevelEventNeedence::NEEDS_EVENT || $accessLevel->eventNeedance == accessLevelEventNeedence::NEEDS_SUBEVENT) {
            header("Location: ./events.php?noEventId=1");
            return new titlebarSetupResult("NENÍ", false, null, null);
        }
        setSubeventId("");
        return new titlebarSetupResult("NENÍ", true, null, null);
    }else {
        setEventId($_COOKIE["adminEventId"]);
    }

    //Check if event exists
    $name = 0;
    $stmt = $conn->prepare("SELECT name FROM events_teamPropaganda WHERE id_events=?;");
    if (!$stmt->bind_param("i", $_COOKIE["adminEventId"]) || !$stmt->execute() || !$stmt->store_result() || !$stmt->bind_result($name) || !$stmt->fetch() || !$stmt->close() || $name == "") {
        if ($accessLevel->eventNeedance == accessLevelEventNeedence::NEEDS_EVENT || $accessLevel->eventNeedance == accessLevelEventNeedence::NEEDS_SUBEVENT) {
            header("Location: ./events.php?noEventId=1");
            return new titlebarSetupResult("NENÍ", false, null, null);
        }
        setEventId("");
        setSubeventId("");
        return new titlebarSetupResult("NENÍ", true, null, null);
    }

    //Check if already redirected due to noSubeventId
    if (isset($_GET["noSubeventId"])) {
        return new titlebarSetupResult($name, true, $_COOKIE["adminEventId"], null);
    }

    //Check if event subcookie exist and refresh it
    if (!isset($_COOKIE["adminSubeventId"])) {
        if ($accessLevel->eventNeedance == accessLevelEventNeedence::NEEDS_SUBEVENT) {
            header("Location: ./events.php?noSubeventId=1");
            return new titlebarSetupResult($name, false, $_COOKIE["adminEventId"], null);
        }
        return new titlebarSetupResult($name, true, $_COOKIE["adminEventId"], null);
    }
    setSubeventId($_COOKIE["adminSubeventId"]);

    //Check if subevent exists
    $date = "";
    $stmt = $conn->prepare("SELECT date FROM subevents_teamPropaganda WHERE id_subevents=?;");
    if (!$stmt->bind_param("i", $_COOKIE["adminSubeventId"]) || !$stmt->execute() || !$stmt->store_result() || !$stmt->bind_result($date) || !$stmt->fetch() || !$stmt->close() || $date == "") {
        if ($accessLevel->eventNeedance == accessLevelEventNeedence::NEEDS_SUBEVENT) {
            header("Location: ./events.php?noSubeventId=1");
            return new titlebarSetupResult($name, false, $_COOKIE["adminEventId"], null);
        }
        setSubeventId("");
        return new titlebarSetupResult($name, true, $_COOKIE["adminEventId"], null);
    }

    //All OK
    return new titlebarSetupResult($name . " → " . DateTime::createFromFormat('Y-m-d', $date)->format("d. m. Y"), true, $_COOKIE["adminEventId"], $_COOKIE["adminSubeventId"]);
}

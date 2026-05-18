<?php
class accessLevel
{
    public readonly bool $needsEvent;
    public readonly bool $needsSubEvent;
    public readonly array $accessGroups;
    public readonly bool $hasTitlebarButton;
    public readonly bool $hasTitlebarButtonLeft;
    public readonly string $titlebarButtonText;
    public readonly string $titlebarButtonColorClass;
    public function __construct(array $accessGroup, bool $needsEvent = true, bool $needsSubEvent = false, bool $hasTitlebarButton = false, bool $hasTitlebarButtonLeft = true, string $titlebarButtonText = "", string $titlebarButtonColorClass = "formOkColor")
    {
        $this->needsEvent = $needsEvent;
        $this->needsSubEvent = $needsSubEvent;
        $this->accessGroups = $accessGroup;
        $this->hasTitlebarButton = $hasTitlebarButton;
        $this->hasTitlebarButtonLeft = $hasTitlebarButtonLeft;
        $this->titlebarButtonText = $titlebarButtonText;
        $this->titlebarButtonColorClass = $titlebarButtonColorClass;
    }
}

$accessLevels = array(
    "admin.php" => new accessLevel(array("admin", "accountant"), false, false, true, true, "Hlavní menu"),
    "attendant.php" => new accessLevel(array("admin")),
    "attendants.php" => new accessLevel(array("admin"), true, false, true, true, "Zájemci"),
    "school.php" => new accessLevel(array("admin")),
    "schools.php" => new accessLevel(array("admin"), true, false, true, true, "Školy"),
    "schoolsAll.php" => new accessLevel(array("admin")),
    "classroom.php" => new accessLevel(array("admin"), false),
    "classrooms.php" => new accessLevel(array("admin"), false, false, true, true, "Učebny"),
    "payments.php" => new accessLevel(array("admin", "accountant"), true, false, true, true, "Platby"),
    "presets.php" => new accessLevel(array("admin"), false, false, true, true, "Šablony"),
    "event.php" => new accessLevel(array("admin"), false),
    "subevent.php" => new accessLevel(array("admin"), false),
    "users.php" => new accessLevel(array("admin"), false, false, true, false, "Správa uživatelů", "formInfoColor"),
    "events.php" => new accessLevel(array("admin", "accountant"), false, false, true, false, "Změnit událost", "formWarnColor"),
    "logout.php" => new accessLevel(array("*"), false, false, true, false, "Odhlásit se", "formErrorColor"),
    "accessDenied.php" => new accessLevel(array("*"), false),
    "index.php" => new accessLevel(array("*"), false),
    "fs.php" => new accessLevel(array("admin"), false)
);

function checkAccess(string $file, string $level): bool
{
    global $accessLevels;
    $levels = $accessLevels[$file]->accessGroups;
    if (isset($levels)) {
        return in_array($level, $levels, true) || in_array("*", $levels, true);
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
    public readonly string $message;
    public readonly bool $allowView;
    //public readonly bool $allowEdit;

    public string $role;

    public function __construct($message, $allowView /*$allowEdit*/)
    {
        $this->allowView = $allowView;
        //$this->allowEdit = $allowEdit;
        $this->message = $message;
    }
}

function setupTitlebarAdmin(mysqli $conn, string $page): titlebarSetupResult
{
    //DEBUG
    $_SESSION["userId"] = 4;

    //Get global variables + user role
    global $accessLevels;
    require_once "../assets/sharedFunctions.php";
    $role = getUserRole($conn, $_SESSION["userId"]);

    //Check access level
    if (!checkAccess($page, $role)) {
        header("Location: ./accessDenied.php");
        $result = new titlebarSetupResult("", false);
        $result->role = $role;
        return $result;
    }

    //Prepare HTML
    $result = setupTitlebarAdminAction($conn, $accessLevels[$page]);
    $result->role = $role;
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
        return new titlebarSetupResult("NENÍ", true);
    }

    //Check if event cookie exist and refresh it
    if (!isset($_COOKIE["adminEventId"])) {
        if ($accessLevel->needsEvent) {
            header("Location: ./events.php?noEventId=1");
            return new titlebarSetupResult("NENÍ", false);
        }
        setSubeventId("");
        return new titlebarSetupResult("NENÍ", true);
    }
    setEventId($_COOKIE["adminEventId"]);

    //Check if event exists
    $name = 0;
    $stmt = $conn->prepare("SELECT name FROM events_teamPropaganda WHERE id_events=?;");
    $stmt->bind_param("i", $_COOKIE["adminEventId"]);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($name);
    if (!$stmt->fetch() || $name == "") {
        if ($accessLevel->needsEvent) {
            header("Location: ./events.php?noEventId=1");
            return new titlebarSetupResult("NENÍ", false);
        }
        setEventId("");
        setSubeventId("");
        return new titlebarSetupResult("NENÍ", true);
    }

    //Check if already redirected due to noSubeventId
    if (isset($_GET["noSubeventId"])) {
        return new titlebarSetupResult($name, true);
    }

    //Check if event subcookie exist and refresh it
    if (!isset($_COOKIE["adminSubeventId"])) {
        if ($accessLevel->needsSubEvent) {
            header("Location: ./events.php?noSubeventId=1");
            return new titlebarSetupResult($name, false);
        }
        return new titlebarSetupResult($name, true);
    }
    setSubeventId($_COOKIE["adminSubeventId"]);

    //Check if subevent exists
    $date = "";
    $stmt = $conn->prepare("SELECT date FROM subevents_teamPropaganda WHERE id_subevents=?;");
    $stmt->bind_param("i", $_COOKIE["adminSubeventId"]);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($date);
    if (!$stmt->fetch() || $date == "") {
        if ($accessLevel->needsSubEvent) {
            header("Location: ./events.php?noSubeventId=1");
            return new titlebarSetupResult($name, false);
        }
        setSubeventId("");
        return new titlebarSetupResult($name, true);
    }

    //All OK
    return new titlebarSetupResult($name . " → " . DateTime::createFromFormat('Y-m-d', $date)->format("d. m. Y"), true);
}

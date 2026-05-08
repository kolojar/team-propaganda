<?php
$accessLevels = array(
    "admin.php" => array("admin", "accountant"),
    "attendant.php" => array("admin", "accountant"),
    "attendants.php" => array("admin", "accountant"),
    "school.php" => array("admin"),
    "schools.php" => array("admin"),
    "schoolsAll.php" => array("admin"),
    "classroom.php" => array("admin"),
    "classrooms.php" => array("admin"),
    "event.php" => array("admin"),
    "events.php" => array("admin", "accountant"),
    "accessDenied.php" => array("user", "accountant", "admin")
);

$eventLevels = array(
    "accessDenied.php" => new eventLevel(false, false),
    "admin.php" => new eventLevel(false, false),
    "attendant.php" => new eventLevel(false),
    "attendants.php" => new eventLevel(false),
    "school.php" => new eventLevel(false),
    "schools.php" => new eventLevel(false),
    "schoolsAll.php" => new eventLevel(false),
    "classroom.php" => new eventLevel(false, false),
    "classrooms.php" => new eventLevel(false, false),
    "event.php" => new eventLevel(false, false),
    "events.php" => new eventLevel(false, false),
);

$leftTitlebarButtons = array(
    new titlebarButton("admin.php", "Hlavní menu"),
    new titlebarButton("attendants.php", "Zájemci"),
    new titlebarButton("classrooms.php", "Učebny"),
    new titlebarButton("schools.php", "Školy"),
);

$rightTitlebarButtons = array(
    new titlebarButton("users.php", "Správa uživatelů", "formInfoColor"),
    new titlebarButton("events.php", "Změnit událost", "formWarnColor"),
    new titlebarButton("logout.php", "Odhlásit se", "formErrorColor"),
);

function checkAccess(string $file, string $level): bool
{
    global $accessLevels;
    $levels = $accessLevels[$file];
    if (isset($levels)) {
        return in_array($level, $levels, true);
    }
    return false;
}

function getUserRole(mysqli $conn, int $id): string
{
    $stmt = $conn->prepare("SELECT role FROM users WHERE id_users=? LIMIT 1;");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($role);
    $stmt->fetch();
    return $role;
}
class eventLevel
{
    public readonly bool $needsEvent;
    public readonly bool $needsSubEvent;
    public function __construct($needsSubEvent = true, $needsEvent = true)
    {
        $this->needsEvent = $needsEvent;
        $this->needsSubEvent = $needsSubEvent;
    }
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

class titlebarButton
{
    public readonly string $file;
    public readonly string $text;
    public readonly string $colorClass;
    public function __construct(string $file, string $text, string $colorClass = "formOkColor")
    {
        $this->file = $file;
        $this->text = $text;
        $this->colorClass = $colorClass;
    }
}

function setupTitlebar(mysqli $conn, string $page): titlebarSetupResult
{
    //DEBUG
    $_SESSION["userId"] = 4;

    //Get global variables + user role
    global $eventLevels;
    global $leftTitlebarButtons;
    global $rightTitlebarButtons;
    $role = getUserRole($conn, $_SESSION["userId"]);

    //Check access level
    if (!checkAccess($page, $role)) {
        header("Location: ./accessDenied.php");
        $result = new titlebarSetupResult("", false);
        $result->role = $role;
        return $result;
    }

    //Prepare HTML
    $result = setupTitlebarAction($conn, $eventLevels[$page]);
    $result->role = $role;
    echo '<h1> Akce: ' . $result->message . '</h1>';
    echo "<div class='formButtonBoxHolder'>";

    //Generate buttons on left
    echo "<div class='formButtonBox formJustifyLeft'>";
    foreach ($leftTitlebarButtons as $key1 => $value) {
        $file = $value->file;
        if (checkAccess($file, $role)) {
            $text = $value->text;
            $colorClass = $value->colorClass;
            echo "<a href='$file'><button class='formButton $colorClass'>$text</button></a>";
        }
    }
    echo "</div>";

    //Generate buttons on right
    echo "<div class='formButtonBox formJustifyRight'>";
    foreach ($rightTitlebarButtons as $key2 => $value) {
        $file = $value->file;
        if (checkAccess($file, $role)) {
            $text = $value->text;
            $colorClass = $value->colorClass;
            echo "<a href='$file'><button class='formButton $colorClass'>$text</button></a>";
        }
    }
    echo "</div>";
    echo "</div>";
    return $result;
}
function setupTitlebarAction(mysqli $conn, eventLevel $eventLevel): titlebarSetupResult
{
    //Check if already redirected due to noEventId
    if (isset($_GET["noEventId"])) {
        return new titlebarSetupResult("NENÍ", true);
    }

    //Check if event cookie exist and refresh it
    if (!isset($_COOKIE["adminEventId"])) {
        if ($eventLevel->needsEvent) {
            header("Location: ./events.php?noEventId=1");
            return new titlebarSetupResult("NENÍ", false);
        }
        setSubeventId("");
        return new titlebarSetupResult("NENÍ", true);
    }
    setEventId($_COOKIE["adminEventId"]);

    //Check if event exists
    $name = 0;
    $stmt = $conn->prepare("SELECT name FROM events WHERE id_events=?;");
    $stmt->bind_param("i", $_COOKIE["adminEventId"]);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($name);
    if (!$stmt->fetch() || $name == "") {
        if ($eventLevel->needsEvent) {
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
        if ($eventLevel->needsSubEvent) {
            header("Location: ./events.php?noSubeventId=1");
            return new titlebarSetupResult($name, false);
        }
        return new titlebarSetupResult($name, true);
    }
    setSubeventId($_COOKIE["adminSubeventId"]);

    //Check if subevent exists
    $date = "";
    $stmt = $conn->prepare("SELECT subevents.date FROM subevents WHERE id_subevents=?;");
    $stmt->bind_param("i", $_COOKIE["adminSubeventId"]);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($date);
    if (!$stmt->fetch() || $date == "") {
        if ($eventLevel->needsSubEvent) {
            header("Location: ./events.php?noSubeventId=1");
            return new titlebarSetupResult($name, false);
        }
        setSubeventId("");
        return new titlebarSetupResult($name, true);
    }

    //All OK
    return new titlebarSetupResult($name . " → " . DateTime::createFromFormat('Y-m-d', $date)->format("d. m. Y"), true);
}
?>
<?php
function setEventId($id): void
{
    setcookie("adminEventId", $id, time() + 60 * 60 * 24 * 30);
}
function setSubeventId($id): void
{
    setcookie("adminSubeventId", $id, time() + 60 * 60 * 24 * 30);
}
function setupTitlebarAction($conn, $forceSubEvent = false, $allowNone = false): string
{
    //Check if already redirected due to noEventId
    if (isset($_GET["noEventId"])) {
        return "NENÍ";
    }

    //Check if event cookie exist and refresh it
    if (!isset($_COOKIE["adminEventId"])) {
        if (!$allowNone) {
            header("Location: ./events.php?noEventId=1");
        }
        setSubeventId("");
        return "NENÍ";
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
        if (!$allowNone) {
            header("Location: ./events.php?noEventId=1");
        }
        setEventId("");
        setSubeventId("");
        return "NENÍ";
    }

    //Check if already redirected due to noSubeventId
    if (isset($_GET["noSubeventId"])) {
        return $name;
    }

    //Check if event subcookie exist and refresh it
    if (!isset($_COOKIE["adminSubeventId"])) {
        if ($forceSubEvent) {
            header("Location: ./events.php?noSubeventId=1");
        }
        return $name;
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
        if ($forceSubEvent) {
            header("Location: ./events.php?noSubeventId=1");
        }
        setSubeventId("");
        return $name;
    }

    //All OK
    return $name . " → " . DateTime::createFromFormat('Y-m-d', $date)->format("d. m. Y");
}
?>
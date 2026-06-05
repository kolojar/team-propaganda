<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/sharedFunctions.php";

function loadJsonSettings(): mixed
{
    //Read JSON    
    $jsonString = file_get_contents(__DIR__ ."/settings.json");
    if ($jsonString == false) {
        return null;
    }

    //Parse JSON
    return json_decode($jsonString, true);
}

function getJsonSetting($setting): null|string
{
    //Load JSON    
    $json = loadJsonSettings();
    if ($json == null) {
        return null;
    }

    //Check if exists
    if (!isset($json[$setting])) {
        return null;
    }
    return $json[$setting];
}

function updateJsonSetting(string $setting, string $value): bool
{
    //Load JSON
    $json = loadJsonSettings();
    if ($json == null) {
        false;
    }

    //Write JSON setting
    $json[$setting] = $value;

    //Encode JSON
    $jsonString = json_encode($json);
    if ($jsonString == false) {
        return false;
    }

    //Write JSON
    $result = file_put_contents(__DIR__ ."/settings.json", $jsonString);
    if ($result == false) {
        return false;
    }
    return true;
}

if (isset($_POST["action"])) {
    if ($_POST["action"] == "getBankAccount") {
        //Get setting
        $setting = getJsonSetting("bankAccount");
        if ($setting == null) {
            http_response_code(400);
            echo "Internal server error - Failed to get setting";
            die();
        }

        //Echo
        echo $setting;
        die();
    }
}
?>
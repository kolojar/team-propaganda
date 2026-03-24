<?php
session_start();
//echo $_POST["code"] . " " . $_SESSION["verify"];
if (isset($_POST["code"]) && isset($_SESSION["verify"]) && $_POST["code"] == $_SESSION["verify"]) {
    //when confirmed
    echo "true";
} else {
    echo "false";
}

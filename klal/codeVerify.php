<?php
session_start();
if (isset($_POST["code"]) && isset($_SESSION["verifyCode"]) && $_POST["code"] == $_SESSION["verifyCode"]) {
    //when confirmed
    echo "true";
} else {
    echo "false";
}

<?php
require "../assets/config.php";
session_start();
$_SESSION["userId"] = null;
$_SESSION["companyId"] = null;
header("Location: ./");
die;

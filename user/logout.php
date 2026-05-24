<?php
require "../assets/config.php";
session_start();
$_SESSION["userId"] = null;
header("Location: ./");
die;

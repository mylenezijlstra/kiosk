<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


// $host = "localhost";
// $user = "u240381_kiosk";
// $pass = "d2KJgVZSdvr42CquP4Ht";
// $dbname = "u240381_kiosk";

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "kiosk";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

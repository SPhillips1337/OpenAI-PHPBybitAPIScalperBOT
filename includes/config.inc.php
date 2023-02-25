<?php
// debug enabled
ini_set('display_errors',1);
error_reporting(E_ALL);

// connect to mysql db BybitScalper
$db_user = 'db_user';
$db_pass = 'db_pass';
$db_host = 'localhost';
$db_name = 'BybitScalper';


    // Bybit API Key
    $api_key = 'your_bybit_api_key';
    // Bybit API Secret
    $api_secret = 'your_bybit_api_secret';


// connect to database
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
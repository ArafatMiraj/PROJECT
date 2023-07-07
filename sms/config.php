<?php


// Database connection parameters
error_reporting(0);
ini_set('display_errors', 0);
$db_user = 'sms';
$db_password = 'sms';
$db_host = 'localhost/XE';

// Establish Oracle database connection
$conn = oci_connect($db_user, $db_password, $db_host);
if (!$conn) {
    $e = oci_error();
    echo "Failed to connect to Oracle: " . $e['message'];
    exit;
}


?>

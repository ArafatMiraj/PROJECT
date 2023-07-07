<?php
session_start();

require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: logout.php');
    exit();
}

if ($_SESSION['user_role'] !== 'admin') {
    header('Location: house.php');
    exit();
}



$flat_id = $_GET['flat_id'];

// Delete the flat record
$sql = "DELETE FROM flat WHERE flat_no = :flat_id";
$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ":flat_id", $flat_id);
oci_execute($stmt);

$_SESSION['success'] = 'Flat Deleted';

header('Location: flat.php');
exit();
?>

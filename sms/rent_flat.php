<?php
session_start();

require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: logout.php');
    exit();
}

if (!isset($_GET['flat_no'])) {
    header('Location: dashboard.php');
    exit();
}

$flat_no = $_GET['flat_no'];

// Check if the flat is available for rent
$stmt = oci_parse($conn, "SELECT * FROM flat WHERE flat_no = :flat_no AND renter_id IS NULL");
oci_bind_by_name($stmt, ":flat_no", $flat_no);
oci_execute($stmt);
$flat = oci_fetch_assoc($stmt);

if (!$flat) {
    header('Location: dashboard.php');
    exit();
}

$renter_id = $_SESSION['user_id'];
$owner_id = $flat['OWNER_ID'];
$house_id = $flat['HOUSE_ID'];

// Check if the rent request already exists
$stmt = oci_parse($conn, "SELECT * FROM RENTREQUEST WHERE flat_no = :flat_no AND renter_id = :renter_id");
oci_bind_by_name($stmt, ":flat_no", $flat_no);
oci_bind_by_name($stmt, ":renter_id", $renter_id);
oci_execute($stmt);
$existingRequest = oci_fetch_assoc($stmt);

if ($existingRequest) {
    $_SESSION['error'] = 'Rent request already submitted for this flat';
    header('Location: dashboard.php');
    exit();
}

// Insert the rent request into the RENTREQUEST table
$stmt = oci_parse($conn, "INSERT INTO RENTREQUEST (house_id, flat_no, renter_id, owner_id) 
                         VALUES (:house_id, :flat_no, :renter_id, :owner_id)");
oci_bind_by_name($stmt, ":house_id", $house_id);
oci_bind_by_name($stmt, ":flat_no", $flat_no);
oci_bind_by_name($stmt, ":renter_id", $renter_id);
oci_bind_by_name($stmt, ":owner_id", $owner_id);
oci_execute($stmt);

$_SESSION['success'] = 'Rent request submitted successfully';

header('Location: dashboard.php');
exit();
?>
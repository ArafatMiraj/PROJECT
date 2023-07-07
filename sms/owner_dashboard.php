<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: logout.php');
    exit();
}
$owner_id = $_SESSION['user_id']; // Get the user_id from the session

// Get total number of houses
$stmt = oci_parse($conn, "SELECT COUNT(*) AS total_houses FROM house WHERE OWNER_ID=:owner_id");
oci_bind_by_name($stmt, ":owner_id", $owner_id); // Bind the owner_id

oci_execute($stmt);
$total_houses = oci_fetch_assoc($stmt)['TOTAL_HOUSES'];

// Get total number of flats
$stmt = oci_parse($conn, "SELECT COUNT(*) AS total_flats FROM flat WHERE OWNER_ID=:owner_id");
oci_bind_by_name($stmt, ":owner_id", $owner_id); // Bind the owner_id

oci_execute($stmt);
$total_flats = oci_fetch_assoc($stmt)['TOTAL_FLATS'];

// Get number of rented flats
$stmt = oci_parse($conn, "SELECT COUNT(*) AS rented_flats FROM flat WHERE renter_id IS NOT NULL AND OWNER_ID=:owner_id");
oci_bind_by_name($stmt, ":owner_id", $owner_id); // Bind the owner_id

oci_execute($stmt);
$rented_flats = oci_fetch_assoc($stmt)['RENTED_FLATS'];

$stmt = oci_parse($conn, "SELECT COUNT(*) AS non_rented_flats FROM flat WHERE renter_id IS NULL AND OWNER_ID = :owner_id");
oci_bind_by_name($stmt, ":owner_id", $owner_id);
oci_execute($stmt);

$row = oci_fetch_assoc($stmt);
$non_rented_flats = $row['NON_RENTED_FLATS'];




oci_execute($stmt);
$non_rented_flats = oci_fetch_assoc($stmt)['NON_RENTED_FLATS'];

// Get total number of renters
$stmt = oci_parse($conn, "SELECT COUNT(*) AS total_renters FROM flat WHERE renter_id >0 AND OWNER_ID=:owner_id");
oci_bind_by_name($stmt, ":owner_id", $owner_id); // Bind the owner_id

oci_execute($stmt);
$total_renters = oci_fetch_assoc($stmt)['TOTAL_RENTERS'];

// Get number of pending complaints
$stmt = oci_parse($conn, "SELECT COUNT(*) AS pending_complaints FROM forum WHERE status = 'Pending' AND OWNER_ID=:owner_id");
oci_bind_by_name($stmt, ":owner_id", $owner_id); // Bind the owner_id

oci_execute($stmt);
$pending_complaints = oci_fetch_assoc($stmt)['PENDING_COMPLAINTS'];

// Get number of solved complaints
$stmt = oci_parse($conn, "SELECT COUNT(*) AS solved_complaints FROM forum WHERE status = 'Solved' AND OWNER_ID=:owner_id");
oci_bind_by_name($stmt, ":owner_id", $owner_id); // Bind the owner_id

oci_execute($stmt);
$solved_complaints = oci_fetch_assoc($stmt)['SOLVED_COMPLAINTS'];

// Get total due bill amount
$stmt = oci_parse($conn, "SELECT SUM(due_amount) AS total_due_bills FROM bill");
oci_execute($stmt);
$total_due_bills = oci_fetch_assoc($stmt)['TOTAL_DUE_BILLS'];

// Get total number of guards
$stmt = oci_parse($conn, "SELECT COUNT(*) AS total_guards
FROM guard g
JOIN house h ON g.house_id = h.house_id
JOIN users u ON h.owner_id = u.id
WHERE u.id = :owner_id");
oci_bind_by_name($stmt, ":owner_id", $owner_id); // Bind the owner_id

oci_execute($stmt);
$total_guards = oci_fetch_assoc($stmt)['TOTAL_GUARDS'];

include('header.php');
?>

<!DOCTYPE html>
<html>
<head>
    <title>Owner Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            padding: 10px 20px;
        }
    </style>
</head>
<body>
    <div class="container-fluid px-4">
        <h1 class="mt-4">Owner Dashboard</h1>
        <div class="row">
            
            <div class="col-md-6 col-lg-3">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title"><i class="bi bi-house-door"></i> Total Houses</h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text"><?php echo $total_houses; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title"><i class="bi bi-building"></i> Total Flats</h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text"><?php echo $total_flats; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title"><i class="bi bi-people-fill"></i> Rented Flats</h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text"><?php echo $rented_flats; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title"><i class="bi bi-door-open-fill"></i> Non-Rented Flats</h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text"><?php echo $non_rented_flats; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title"><i class="bi bi-person-fill"></i> Total Renters</h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text"><?php echo $total_renters; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title"><i class="bi bi-exclamation-triangle-fill"></i> Pending Complaints</h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text"><?php echo $pending_complaints; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title"><i class="bi bi-check-circle-fill"></i> Solved Complaints</h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text"><?php echo $solved_complaints; ?></p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title"><i class="bi bi-shield-fill-check"></i> Total Guards</h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text"><?php echo $total_guards; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>


<?php
include('footer.php');
?>

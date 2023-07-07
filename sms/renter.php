<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) ) {
    header('Location: logout.php');
    exit();
}

$owner_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

if ($user_role == 'admin') {
    $stmt = oci_parse($conn, "SELECT u.id, u.name, u.email, u.contact_number, h.house_name, f.floor_no, f.flat_no
                            FROM flat f
                            JOIN house h ON f.house_id = h.house_id
                            JOIN users u ON f.renter_id = u.id
                            WHERE f.owner_id = :owner_id");
    oci_bind_by_name($stmt, ":owner_id", $owner_id);
    oci_execute($stmt);
} elseif ($user_role == 'superadmin') {
    $stmt = oci_parse($conn, "SELECT u.id, u.name, u.email, u.contact_number, h.house_name, f.floor_no, f.flat_no
                            FROM flat f
                            JOIN house h ON f.house_id = h.house_id
                            JOIN users u ON f.renter_id = u.id");
    oci_execute($stmt);
}

$renters = oci_fetch_all($stmt, $rentersArr, null, null, OCI_FETCHSTATEMENT_BY_ROW);

include('header.php');
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Renter Dashboard</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Renter Dashboard</li>
    </ol>
    
    <?php if ($renters > 0): ?>
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-table me-1"></i>
                Renter List
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="renterTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Contact Number</th>
                                <th>House Name</th>
                                <th>Floor No</th>
                                <th>Flat No</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rentersArr as $renter): ?>
                                <tr>
                                    <td><?php echo $renter['ID']; ?></td>
                                    <td><?php echo $renter['NAME']; ?></td>
                                    <td><?php echo $renter['EMAIL']; ?></td>
                                    <td><?php echo $renter['CONTACT_NUMBER']; ?></td>
                                    <td><?php echo $renter['HOUSE_NAME']; ?></td>
                                    <td><?php echo $renter['FLOOR_NO']; ?></td>
                                    <td><?php echo $renter['FLAT_NO']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-info">No renters found.</div>
    <?php endif; ?>
</div>

<?php include('footer.php'); ?>

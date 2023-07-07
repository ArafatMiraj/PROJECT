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
                            JOIN users u ON f.owner_id = u.id
                            WHERE f.owner_id = :owner_id");
    oci_bind_by_name($stmt, ":owner_id", $owner_id);
    oci_execute($stmt);
} elseif ($user_role == 'superadmin') {
    $stmt = oci_parse($conn, "SELECT u.id, u.name, u.email, u.contact_number FROM users u");
    oci_execute($stmt);
}

$owners = oci_fetch_all($stmt, $ownersArr, null, null, OCI_FETCHSTATEMENT_BY_ROW);

include('header.php');
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Owner Dashboard</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Owner Dashboard</li>
    </ol>
    
    <?php if ($owners > 0): ?>
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-table me-1"></i>
                Owner List
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="ownerTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Contact Number</th>
                                
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ownersArr as $owner): ?>
                                <tr>
                                    <td><?php echo $owner['ID']; ?></td>
                                    <td><?php echo $owner['NAME']; ?></td>
                                    <td><?php echo $owner['EMAIL']; ?></td>
                                    <td><?php echo $owner['CONTACT_NUMBER']; ?></td>
                                   
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-info">No owners found.</div>
    <?php endif; ?>
</div>

<?php include('footer.php'); ?>

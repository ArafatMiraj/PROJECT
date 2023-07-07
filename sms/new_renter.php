<?php
session_start();
require_once 'config.php';

// Check if the user is logged in and has the role of admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: logout.php');
    exit();
}

include('header.php');

// Check if the rent request is approved
if (isset($_POST['approve_request'])) {
    $renter_id = $_POST['renter_id'];
    $flat_no = $_POST['flat_no'];

    // Update the flat table with the renter ID
    $stmt = oci_parse($conn, "UPDATE flat SET renter_id = :renter_id WHERE flat_no = :flat_no");
    oci_bind_by_name($stmt, ":renter_id", $renter_id);
    oci_bind_by_name($stmt, ":flat_no", $flat_no);
    oci_execute($stmt);

    // Delete the rent request from the table
    $stmt = oci_parse($conn, "DELETE FROM rentrequest WHERE renter_id = :renter_id AND flat_no = :flat_no");
    oci_bind_by_name($stmt, ":renter_id", $renter_id);
    oci_bind_by_name($stmt, ":flat_no", $flat_no);
    oci_execute($stmt);

    // Redirect back to the same page to refresh the content
    header('Location: new_renter.php');
    exit();
}

// Check if the rent request is rejected
if (isset($_POST['reject_request'])) {
    $renter_id = $_POST['renter_id'];
    $flat_no = $_POST['flat_no'];

    // Delete the rent request from the table
    $stmt = oci_parse($conn, "DELETE FROM rentrequest WHERE renter_id = :renter_id AND flat_no = :flat_no");
    oci_bind_by_name($stmt, ":renter_id", $renter_id);
    oci_bind_by_name($stmt, ":flat_no", $flat_no);
    oci_execute($stmt);

    // Redirect back to the same page to refresh the content
    header('Location: new_renter.php');
    exit();
}
$owner_id = $_SESSION['user_id'];
// Retrieve the rent requests with renter and flat information
$stmt = oci_parse($conn, "SELECT r.*, u.name AS renter_name, u.contact_number AS renter_contact, f.flat_no, f.floor_no, f.flat_size, f.furnish_type, f.flat_rent, h.house_name
                        FROM rentrequest r
                        INNER JOIN users u ON r.renter_id = u.id
                        INNER JOIN flat f ON r.flat_no = f.flat_no
                        INNER JOIN house h ON r.house_id = h.house_id AND f.owner_id = :owner_id");
                        oci_bind_by_name($stmt, ":owner_id", $owner_id); // Bind the owner_id

oci_execute($stmt);
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">New Renter Requests</h1>
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Rent Requests</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Renter Name</th>
                            <th>Renter Contact</th>
                            <th>Flat No</th>
                            <th>Floor No</th>
                            <th>Flat Size</th>
                            <th>Furnish Type</th>
                            <th>Flat Rent</th>
                            <th>House Name</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = oci_fetch_assoc($stmt)): ?>
                            <tr>
                                <td><?php echo $row['RENTER_NAME']; ?></td>
                                <td><?php echo $row['RENTER_CONTACT']; ?></td>
                                <td><?php echo $row['FLAT_NO']; ?></td>
                                <td><?php echo $row['FLOOR_NO']; ?></td>
                                <td><?php echo $row['FLAT_SIZE']; ?></td>
                                <td><?php echo $row['FURNISH_TYPE']; ?></td>
                                <td><?php echo $row['FLAT_RENT']; ?></td>
                                <td><?php echo $row['HOUSE_NAME']; ?></td>
                                <td>
                                    <form method="POST">
                                        <input type="hidden" name="renter_id" value="<?php echo $row['RENTER_ID']; ?>">
                                        <input type="hidden" name="flat_no" value="<?php echo $row['FLAT_NO']; ?>">
                                        <button type="submit" name="approve_request" class="btn btn-success">Approve</button>
                                        <button type="submit" name="reject_request" class="btn btn-danger">Reject</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>

<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: logout.php');
    exit();
}
$owner_id = $_SESSION['user_id'];
// Fetch guards information with house names
$stmt = oci_parse($conn, "SELECT G.Guard_ID, G.District, G.Name, G.Contact_info, G.Gender, H.House_Name 
                        FROM GUARD G
                        INNER JOIN HOUSE H ON G.House_ID = H.House_ID AND H.owner_id = :owner_id");
oci_bind_by_name($stmt, ":owner_id", $owner_id); // Bind the owner_id

oci_execute($stmt);
$guards = oci_fetch_all($stmt, $guardsArr, null, null, OCI_FETCHSTATEMENT_BY_ROW);

include('header.php');
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Guard Management</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Guard Management</li>
    </ol>
    <?php
    if (isset($_SESSION['success'])) {
        echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
        unset($_SESSION['success']);
    }
    ?>
    <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col col-6">
                    <h5 class="card-title">Guard Management</h5>
                </div>
                <div class="col col-6">
                    <div class="float-end"><a href="manage_guard.php" class="btn btn-success btn-sm">Manage Guards</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($guards)) { ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="guards-table">
                        <thead>
                            <tr>
                                <th>Guard ID</th>
                                <th>District</th>
                                <th>Name</th>
                                <th>Contact Info</th>
                                <th>Gender</th>
                                <th>House Name</th>
                                <?php if ($_SESSION['role'] == 'admin') { ?>

                                    <th>Action</th>
                                <?php } ?>

                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($guardsArr as $guard) { ?>
                                <tr>
                                    <td>
                                        <?php echo $guard['GUARD_ID']; ?>
                                    </td>
                                    <td>
                                        <?php echo $guard['DISTRICT']; ?>
                                    </td>
                                    <td>
                                        <?php echo $guard['NAME']; ?>
                                    </td>
                                    <td>
                                        <?php echo $guard['CONTACT_INFO']; ?>
                                    </td>
                                    <td>
                                        <?php echo $guard['GENDER']; ?>
                                    </td>
                                    <td>
                                        <?php echo $guard['HOUSE_NAME']; ?>
                                    </td>
                                    <td>
                                        <?php if ($_SESSION['role'] == 'admin') { ?>
                                            <a href="manage_guard.php?edit_guard=<?php echo $guard['GUARD_ID']; ?>"
                                                class="btn btn-sm btn-primary">Edit</a>
                                            <a href="manage_guard.php?delete_guard=<?php echo $guard['GUARD_ID']; ?>"
                                                class="btn btn-sm btn-danger"
                                                onclick="return confirm('Are you sure you want to delete this guard?')">Delete</a>
                                        <?php } ?>
                                    </td>

                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } else { ?>
                <div class="alert alert-info">No guard information found.</div>
            <?php } ?>
        </div>
    </div>
</div>

<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.2/js/dataTables.bootstrap5.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.2/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" type="text/css"
    href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.2.0/css/bootstrap.min.css">

<script>
    $(document).ready(function () {
        $('#guards-table').DataTable();
    });
</script>

<?php
include('footer.php');
?>
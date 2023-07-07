<?php
session_start();

require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: logout.php');
    exit();
}

if ($_SESSION['user_role'] !== 'superadmin') {
    header('Location: logout.php');
    exit();
}

// Retrieve admin dashboard information from the view
$stmt = oci_parse($conn, "SELECT * FROM admin_dashboard_view");
oci_execute($stmt);
$dashboard_info = oci_fetch_assoc($stmt);

include('header.php');
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Admin Dashboard</h1>
    <div class="row">
        <div class="col-lg-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <h5 class="card-title">Total Owners</h5>
                    <h2>
                        <?php echo $dashboard_info['TOTAL_OWNERS']; ?>
                    </h2>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <h5 class="card-title">Total Renters</h5>
                    <h2>
                        <?php echo $dashboard_info['TOTAL_RENTERS']; ?>
                    </h2>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <h5 class="card-title">Total Houses</h5>
                    <h2>
                        <?php echo $dashboard_info['TOTAL_HOUSES']; ?>
                    </h2>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card bg-info text-white mb-4">
                <div class="card-body">
                    <h5 class="card-title">Total Flats</h5>
                    <h2>
                        <?php echo $dashboard_info['TOTAL_FLATS']; ?>
                    </h2>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Total Amount Paid</h5>
                    <h2>
                        <?php echo $dashboard_info['TOTAL_AMOUNT_PAID']; ?>
                    </h2>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Total Problems Solved</h5>
                    <h2>
                        <?php echo $dashboard_info['TOTAL_PROBLEMS_SOLVED']; ?>
                    </h2>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Total Problems Pending</h5>
                    <h2>
                        <?php echo $dashboard_info['TOTAL_PROBLEMS_PENDING']; ?>
                    </h2>
                </div>
            </div>
        </div>
    </div>

</div>

<?php
include('footer.php');
?>
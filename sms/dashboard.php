<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: logout.php');
    exit();
}

if ($_SESSION['user_role'] === 'admin') {
    header('Location: owner_dashboard.php');
    exit();
}
if ($_SESSION['user_role'] === 'superadmin') {
    header('Location: admin_dashboard.php');
    exit();
}

// Check if the renter has rented a flat
$stmt = oci_parse($conn, "SELECT f.flat_no, f.house_id, h.house_name, u.name AS owner_name, u.contact_number AS owner_contact,  g.name as Guard_Name, g.contact_info as Guard_Contact
FROM flat f 
LEFT JOIN house h ON f.house_id = h.house_id 
LEFT JOIN users u ON h.owner_id = u.id 
LEFT JOIN guard g ON h.house_id = g.house_id 
WHERE f.renter_id = :renter_id");

oci_bind_by_name($stmt, ":renter_id", $_SESSION['user_id']);
oci_execute($stmt);
$rented_flat = oci_fetch_assoc($stmt);


if (isset($_POST['show_all_flats'])) {
    $sql = "SELECT * FROM flat_search_results";
    $stmt = oci_parse($conn, $sql);
    oci_execute($stmt);
    $flats = oci_fetch_all($stmt, $flatsArr, null, null, OCI_FETCHSTATEMENT_BY_ROW);
}


if (isset($_POST['search_flats'])) {
    $flat_size_from = $_POST['flat_size_from'];
    $flat_size_to = $_POST['flat_size_to'];
    $room_no = $_POST['room_no'];
    $rent_from = $_POST['rent_from'];
    $rent_to = $_POST['rent_to'];

    $sql = "SELECT * FROM flat_search_results 
            WHERE flat_size BETWEEN :flat_size_from AND :flat_size_to 
            AND room_no = :room_no 
            AND flat_rent BETWEEN :rent_from AND :rent_to";
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ":flat_size_from", $flat_size_from);
    oci_bind_by_name($stmt, ":flat_size_to", $flat_size_to);
    oci_bind_by_name($stmt, ":room_no", $room_no);
    oci_bind_by_name($stmt, ":rent_from", $rent_from);
    oci_bind_by_name($stmt, ":rent_to", $rent_to);
    oci_execute($stmt);
    $flats = oci_fetch_all($stmt, $flatsArr, null, null, OCI_FETCHSTATEMENT_BY_ROW);
}

include('header.php');
?>

<div class="container-fluid px-4">
    <?php if ($rented_flat): ?>
        <h1 class="mt-4">Renter Dashboard</h1>
        <div class="row">
            <div class="col-md-6">
                <?php
                // Check if the user is a renter
                if ($_SESSION['user_role'] === 'user') {
                    // Retrieve the renter's ID from the session
                    $renterId = $_SESSION['user_id'];

                    // Query to check if the renter has unpaid bills
                    $query = "SELECT COUNT(*) AS unpaid_count FROM bills
              WHERE flat_id IN (SELECT flat_no FROM flat WHERE renter_id = :renter_id)
              AND bill_status = 'unpaid'";

                    $stmt = oci_parse($conn, $query);
                    oci_bind_by_name($stmt, ":renter_id", $renterId);
                    oci_execute($stmt);

                    $row = oci_fetch_assoc($stmt);
                    $unpaidCount = $row['UNPAID_COUNT'];

                    // Display alert if there are unpaid bills
                    if ($unpaidCount > 0) {
                        echo '<div class="alert alert-warning">Please pay your bill.  <a href="bills.php">Click Here!</a>.</div>';
                    }
                }
                ?>

                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Rented Flat Information</h5>
                        <p><strong>House Name:</strong>
                            <?php echo $rented_flat['HOUSE_NAME']; ?>
                        </p>
                        <p><strong>Owner Name:</strong>
                            <?php echo $rented_flat['OWNER_NAME']; ?>
                        </p>
                        <p><strong>Owner Contact:</strong>
                            <?php echo $rented_flat['OWNER_CONTACT']; ?>
                        </p>
                        <p><strong>Payment Status:</strong>
                            <?php
                            if ($_SESSION['user_role'] == 'user') {
                                $renterId = $_SESSION['user_id'];
                                $query = "SELECT bill_status FROM (
                                    SELECT bill_status FROM bills 
                                    WHERE flat_id IN (SELECT flat_no FROM flat WHERE renter_id = :renter_id) 
                                    ORDER BY paid_date DESC
                                ) WHERE ROWNUM = 1";

                                $stmt = oci_parse($conn, $query);
                                oci_bind_by_name($stmt, ":renter_id", $renterId);
                                oci_execute($stmt);
                                $row = oci_fetch_assoc($stmt);
                                if ($row) {
                                    echo $row['BILL_STATUS'];
                                } else {
                                    echo "No bills found";
                                }
                            } else {
                                echo $rented_flat['PAYMENT_STATUS'];
                            }
                            ?>
                        </p>

                        <a href="complain.php?flat_no=<?php echo $rented_flat['FLAT_NO']; ?>" class="btn btn-primary">Add
                            Complain</a>
                        <a href="bills.php?flat_no=<?php echo $rented_flat['FLAT_NO']; ?>" class="btn btn-primary">View
                            Bill</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Guard Information</h5>
                        <p><strong>Guard Name:</strong>
                            <?php echo $rented_flat['GUARD_NAME']; ?>
                        </p>
                        <p><strong>Guard Contact:</strong>
                            <?php echo $rented_flat['GUARD_CONTACT']; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <h1 class="mt-4">Renter Dashboard</h1>
        <div class="row">
            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="card-body">
                        <?php
                        if (isset($_SESSION['success'])) {
                            echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
                            unset($_SESSION['success']);
                        } else if (isset($_SESSION['error'])) {
                            echo '<div class="alert alert-warning">' . $_SESSION['error'] . '</div>';
                            unset($_SESSION['error']);
                        }
                        ?>
                        <h5 class="card-title">Search for Flats</h5>

                        <form id="search-flats-form" method="POST">
                            <div class="row">

                                <div class="col-md-2">
                                    <label for="flat-size-from" class="form-label">Flat Size (From)</label>
                                    <input type="number" class="form-control" id="flat-size-from" name="flat_size_from">
                                </div>
                                <div class="col-md-2">
                                    <label for="flat-size-to" class="form-label">Flat Size (To)</label>
                                    <input type="number" class="form-control" id="flat-size-to" name="flat_size_to">
                                </div>
                                <div class="col-md-2">
                                    <label for="room-no" class="form-label">Number of Rooms</label>
                                    <select class="form-select" id="room-no" name="room_no">
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                        <option value="5">5</option>
                                    </select>
                                </div>


                                <div class="col-md-2">
                                    <label for="rent-from" class="form-label">Rent (From)</label>
                                    <input type="number" class="form-control" id="rent-from" name="rent_from">
                                </div>
                                <div class="col-md-2">
                                    <label for="rent-to" class="form-label">Rent (To)</label>
                                    <input type="number" class="form-control" id="rent-to" name="rent_to">
                                </div>
                                <div class="col-md-2">
                                    <hr>
                                    <button type="submit" name="search_flats" class="btn btn-primary">Search</button>
                                </div>
                            </div>
                            <br>

                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <?php if (isset($flats) && $flats > 0): ?>
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Search Results</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped" id="search-results-table">
                                    <thead>
                                        <tr>
                                            <th>House Name</th>
                                            <th>Flat No</th>
                                            <th>Flat Size</th>
                                            <th>Room No</th>
                                            <th>Furnish Type</th>
                                            <th>Rent</th>
                                            <th>Owner Name</th>
                                            <th>Owner Contact</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($flatsArr as $flat): ?>
                                            <tr>
                                                <td>
                                                    <?php echo $flat['HOUSE_NAME']; ?>
                                                </td>
                                                <td>
                                                    <?php echo $flat['FLAT_NO']; ?>
                                                </td>
                                                <td>
                                                    <?php echo $flat['FLAT_SIZE']; ?>
                                                </td>
                                                <td>
                                                    <?php echo $flat['ROOM_NO']; ?>
                                                </td>
                                                <td>
                                                    <?php echo $flat['FURNISH_TYPE']; ?>
                                                </td>
                                                <td>
                                                    <?php echo $flat['FLAT_RENT']; ?>
                                                </td>
                                                <td>
                                                    <?php echo $flat['OWNER_NAME']; ?>
                                                </td>
                                                <td>
                                                    <?php echo $flat['OWNER_CONTACT']; ?>
                                                </td>
                                                <td>
                                                    <a href="rent_flat.php?flat_no=<?php echo $flat['FLAT_NO']; ?>"
                                                        class="btn btn-primary">Rent Flat</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>


                <?php else: ?>
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">No Flats Available</h5>
                            <p>There are no flats available with the criteria.</p>
                            <div class="text-center">
                                <form method="POST">
                                    <button type="submit" name="show_all_flats" class="btn btn-primary">Show All Available
                                        Flats</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include('footer.php'); ?>
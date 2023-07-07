<?php
session_start();

require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: logout.php');
    exit();
}

/*if (!isset($_GET['house_id'])) {
    header('Location: house.php');
    exit();
}
*/
$house_id = $_GET['house_id'];

// Fetch house details
$stmt = oci_parse($conn, "SELECT * FROM house WHERE house_id = :house_id");
oci_bind_by_name($stmt, ":house_id", $house_id);
oci_execute($stmt);
$house = oci_fetch_assoc($stmt);

// Fetch flats for the house
$stmt = oci_parse($conn, "SELECT * FROM flat WHERE house_id = :house_id");
oci_bind_by_name($stmt, ":house_id", $house_id);
oci_execute($stmt);
$flats = oci_fetch_all($stmt, $flatsArr, null, null, OCI_FETCHSTATEMENT_BY_ROW);

include('header.php');
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Flats for
        <?php echo $house['HOUSE_NAME']; ?>
    </h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="house.php">Houses Management</a></li>
        <li class="breadcrumb-item"><a href="flat.php">Flats Management</a></li>
        <li class="breadcrumb-item active">Flats for
            <?php echo $house['HOUSE_NAME']; ?>
        </li>
    </ol>
    <?php if (empty($flatsArr)): ?>
        <div class="alert alert-info">No flat information found for
            <?php echo $house['HOUSE_NAME']; ?>
        </div>
        <?php if ($_SESSION['user_role'] == 'admin'): ?>
            <a href="add_flat.php?house_id=<?php echo $house_id; ?>" class="btn btn-primary">Add Flat</a>
        <?php endif; ?>
    <?php else: ?>
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Flats for
                    <?php echo $house['HOUSE_NAME']; ?>
                </h5>
                <?php if ($_SESSION['user_role'] == 'admin'): ?>
                    <a href="add_flat.php?house_id=<?php echo $house_id; ?>" class="btn btn-primary " >Add Flat</a>
                <?php endif; ?>
            </div>


            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="flats-table">
                        <thead>
                            <tr>
                                <th>Flat Number</th>
                                <th>Floor Number</th>
                                <th>Flat Size</th>
                                <th>Total Room</th>
                                <th>Furnish Type</th>
                                <th>Flat Rent</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($flatsArr as $flat): ?>
                                <tr>
                                    <td>
                                        <?php echo $flat['FLAT_NO']; ?>
                                    </td>
                                    <td>
                                        <?php echo $flat['FLOOR_NO']; ?>
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
                                        <?php if ($_SESSION['user_role'] == 'admin' || $_SESSION['user_role'] == 'superadmin'): ?>
                                            <?php echo $flat['RENTER_NAME']; ?>
                                        <?php else: ?>
                                            <a href="rent_flat.php?flat_id=<?php echo $flat['FLAT_NO']; ?>"
                                                class="btn btn-primary">Rent Flat</a>
                                        <?php endif; ?>
                                        <?php if ($_SESSION['user_role'] == 'admin' ): ?>
                                            <a href="edit_flat.php?flat_id=<?php echo $flat['FLAT_NO']; ?>"
                                                class="btn btn-sm btn-primary">Edit</a>
                                            <a href="delete_flat.php?flat_id=<?php echo $flat['FLAT_NO']; ?>"
                                                class="btn btn-sm btn-danger">Delete</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    <?php endif; ?>
</div>

<?php
include('footer.php');
?>
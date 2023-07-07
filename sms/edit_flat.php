<?php
session_start();

require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: logout.php');
    exit();
}

$flat_id = $_GET['flat_id'];

// Retrieve the flat details
$stmt = oci_parse($conn, "SELECT * FROM flat WHERE flat_no = :flat_id");
oci_bind_by_name($stmt, ":flat_id", $flat_id);
oci_execute($stmt);
$flat = oci_fetch_assoc($stmt);


if (isset($_POST['update_flat'])) {
    // Validate the form data
    $flat_rent = $_POST['flat_rent'];
    $furnish_type = $_POST['furnish_type'];
    $room_no = $_POST['room_no'];

    // If the form data is valid, update the flat record
    $sql = "UPDATE flat SET flat_rent = :flat_rent, furnish_type = :furnish_type, room_no = :room_no WHERE flat_no = :flat_id";
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ":flat_rent", $flat_rent);
    oci_bind_by_name($stmt, ":furnish_type", $furnish_type);
    oci_bind_by_name($stmt, ":room_no", $room_no);
    oci_bind_by_name($stmt, ":flat_id", $flat_id);
    oci_execute($stmt);

    $_SESSION['success'] = 'Flat Updated';

    header('Location: house.php');
    exit();
}

include('header.php');
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Edit Flat</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="house.php">House Management</a></li>
        <li class="breadcrumb-item"><a href="flats.php?house_id=<?php echo $house_id; ?>">Flats for <?php echo $house['house_name']; ?></a></li>
        <li class="breadcrumb-item active">Edit Flat</li>
    </ol>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Edit Flat</h5>
            </div>
            <div class="card-body">
                <form id="edit-flat-form" method="POST">
                    <div class="mb-3">
                        <label for="flat-rent" class="form-label">Flat Rent</label>
                        <input type="number" class="form-control" id="flat-rent" name="flat_rent" required value="<?php echo $flat['FLAT_RENT']; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="furnish-type" class="form-label">Furnish Type</label>
                        <select class="form-select" id="furnish-type" name="furnish_type" required>
                            <option value="furnished" <?php if ($flat['FURNISH_TYPE'] === 'furnished') echo 'selected'; ?>>Furnished</option>
                            <option value="not-furnished" <?php if ($flat['FURNISH_TYPE'] === 'not-furnished') echo 'selected'; ?>>Not Furnished</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="room-no" class="form-label">Number of Rooms</label>
                        <input type="number" class="form-control" id="room-no" name="room_no" required value="<?php echo $flat['ROOM_NO']; ?>">
                    </div>
                    <button type="submit" name="update_flat" class="btn btn-primary">Update Flat</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
include('footer.php');
?>

<?php
session_start();

require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: logout.php');
    exit();
}

if ($_SESSION['user_role'] !== 'admin') {
    header('Location: house.php');
    exit();
}

if (!isset($_GET['house_id'])) {
    header('Location: house.php');
    exit();
}

$house_id = $_GET['house_id'];

// Check if the user is the owner of the house
$stmt = oci_parse($conn, "SELECT * FROM house WHERE house_id = :house_id AND owner_id = :owner_id");
oci_bind_by_name($stmt, ":house_id", $house_id);
oci_bind_by_name($stmt, ":owner_id", $_SESSION['user_id']);
oci_execute($stmt);
$house = oci_fetch_assoc($stmt);

if (!$house) {
    header('Location: house.php');
    exit();
}
if (isset($_POST['add_flat'])) {
    // Validate the form data
    $floor_no = $_POST['floor_no'];
    $room_no = $_POST['room_no'];
    $flat_size = $_POST['flat_size'];
    $furnish_type = $_POST['furnish_type'];
    $flat_rent = $_POST['flat_rent'];
    $owner_id = $_SESSION['user_id']; // Get the user_id from the session

    // Get the next value from the sequence for the flat_no
    $query = "SELECT flat_seq.NEXTVAL FROM DUAL";
    $stmt = oci_parse($conn, $query);
    oci_execute($stmt);
    $row = oci_fetch_assoc($stmt);
    $nextFlatNo = $row['NEXTVAL'];

    // If the form data is valid, insert the data into the table
    $sql = "INSERT INTO flat (flat_no, floor_no, room_no, flat_size, furnish_type, flat_rent, house_id, owner_id) 
            VALUES (:flat_no, :floor_no, :room_no, :flat_size, :furnish_type, :flat_rent, :house_id, :owner_id)";
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ':flat_no', $nextFlatNo);
    oci_bind_by_name($stmt, ":floor_no", $floor_no);
    oci_bind_by_name($stmt, ":room_no", $room_no);
    oci_bind_by_name($stmt, ":flat_size", $flat_size);
    oci_bind_by_name($stmt, ":furnish_type", $furnish_type);
    oci_bind_by_name($stmt, ":flat_rent", $flat_rent);
    oci_bind_by_name($stmt, ":house_id", $house_id);
    oci_bind_by_name($stmt, ":owner_id", $owner_id); // Bind the owner_id

    if (oci_execute($stmt)) {
        // Insert successful
        $_SESSION['success'] = 'New flat added successfully.';
    } else {
        // Insert failed
        $error = oci_error($stmt);
        $_SESSION['error'] = 'Failed to add new flat. Error: ' . $error['message'];
    }

    header('location: house.php');
    exit();
}


include('header.php');
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Add Flat</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="house.php">House Management</a></li>
        <li class="breadcrumb-item"><a href="flats.php?house_id=<?php echo $house_id; ?>">Flats for <?php echo $house['house_name']; ?></a></li>
        <li class="breadcrumb-item active">Add Flat</li>
    </ol>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Add Flat</h5>
            </div>
            <div class="card-body">
                <form id="add-flat-form" method="POST">
                    <div class="mb-3">
                        <label for="floor-no" class="form-label">Floor Number</label>
                        <select class="form-control" id="floor-no" name="floor_no" required>
                            <?php
                            // Get the selected house ID
                            
                            // Query to fetch the number of floors for the selected house ID
                            $query = "SELECT no_of_floors FROM house WHERE house_id = :house_id";
                            $stmt = oci_parse($conn, $query);
                            oci_bind_by_name($stmt, ':house_id', $house_id);
                            oci_execute($stmt);

                            $row = oci_fetch_assoc($stmt);
                            $noOfFloors = $row['NO_OF_FLOORS'];

                            // Generate selectable options for the floor numbers based on the number of floors
                            for ($i = 1; $i <= $noOfFloors; $i++) {
                                echo '<option value="' . $i . '">' . $i . '</option>';
                            }

                            ?>
                        </select>
                    </div>


                    <div class="mb-3">
                        <label for="room-no" class="form-label">Number of Rooms</label>
                        <input type="number" class="form-control" id="room-no" name="room_no" required>
                    </div>
                    <div class="mb-3">
                        <label for="flat-size" class="form-label">Flat Size</label>
                        <input type="number" class="form-control" id="flat-size" name="flat_size" required>
                    </div>
                    <div class="mb-3">
                        <label for="furnish-type" class="form-label">Furnish Type</label>
                        <select class="form-select" id="furnish-type" name="furnish_type" required>
                            <option value="furnished">Furnished</option>
                            <option value="not-furnished">Not Furnished</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="flat-rent" class="form-label">Flat Rent</label>
                        <input type="number" class="form-control" id="flat-rent" name="flat_rent" required>
                    </div>
                    <button type="submit" name="add_flat" class="btn btn-primary">Add Flat</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
include('footer.php');
?>
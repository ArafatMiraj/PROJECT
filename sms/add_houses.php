<?php
session_start();

require_once 'config.php';

if (isset($_POST['add_house'])) {
    // Validate the form data
    $house_name = $_POST['house_name'];
    $parking = $_POST['parking'];
    $service_charge = $_POST['service_charge'];
    $no_of_floors = $_POST['no_of_floors'];

    if (empty($house_name)) {
        $errors[] = 'House Name is required';
    }
    if (empty($parking)) {
        $errors[] = 'Parking is required';
    }
    if (empty($service_charge)) {
        $errors[] = 'Service Charge is required';
    }


    // If the form data is valid, insert the data into the table
    if (empty($errors)) {
        $owner_id = $_SESSION['user_id']; // Get the user_id from the session
        $sql = "INSERT INTO house (house_id, house_name, parking, service_charge, no_of_floors, owner_id) VALUES (house_seq.nextval, :house_name, :parking, :service_charge, :no_of_floors, :owner_id)";
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ":house_name", $house_name);
        oci_bind_by_name($stmt, ":parking", $parking);
        oci_bind_by_name($stmt, ":service_charge", $service_charge);
        oci_bind_by_name($stmt, ":no_of_floors", $no_of_floors);
        oci_bind_by_name($stmt, ":owner_id", $owner_id); // Bind the owner_id
        oci_execute($stmt);

        $_SESSION['success'] = 'New House Data Added';

        header('location:house.php');
        exit();
    }
}

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: logout.php');
    exit();
}

include('header.php');
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Add House</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="house.php">house Management</a></li>
        <li class="breadcrumb-item active">Add House</li>
    </ol>
    <div class="col-md-4">
        <?php
        if (isset($errors)) {
            foreach ($errors as $error) {
                echo "<div class='alert alert-danger'>$error</div>";
            }
        }
        ?>
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Add House</h5>
            </div>
            <div class="card-body">
                <form id="add-house-form" method="POST">
                    <div class="mb-3">
                        <label for="house-name" class="form-label">House Name</label>
                        <input type="text" class="form-control" id="house-name" name="house_name">
                    </div>
                    <div class="mb-3">
                        <label for="parking" class="form-label">Parking</label>
                        <select name="parking" class="form-control">
                            <option value="available">Available</option>
                            <option value="not available">Not Available</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="service-charge" class="form-label">Service Charge</label>
                        <input type="number" class="form-control" id="service-charge" name="service_charge">
                    </div>
                    <div class="mb-3">
                        <label for="no_of_floors" class="form-label">Number of Floors</label>
                        <input type="number" class="form-control" id="no_of_floors" name="no_of_floors" min="6" max="14"
                            required>
                    </div>

                    <button type="submit" name="add_house" class="btn btn-primary">Add House</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
require_once 'config.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: logout.php');
    exit();
}

// Rest of the code remains unchanged

if (isset($_GET['action'], $_GET['id']) && $_GET['action'] == 'delete') {
    $stmt = oci_parse($conn, "DELETE FROM house WHERE house_id = :id");
    oci_bind_by_name($stmt, ":id", $_GET['id']);
    oci_execute($stmt);
    $error = oci_error($stmt);
    if ($error) {
        $_SESSION['error'] = $error['message'];
    } else {
        $_SESSION['success'] = 'House deleted successfully.';
    }
    header('Location: house.php');
    exit();
}

include('header.php');
?>
<div class="container-fluid px-4">
    <h1 class="mt-4">Houses Management</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Houses Management</li>
    </ol>
    <?php
    if (isset($_SESSION['success'])) {
        echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
        unset($_SESSION['success']);
    }
    if (isset($_SESSION['error'])) {
        echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
        unset($_SESSION['error']);
    }
    ?>
    <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col col-6">
                    <h5 class="card-title">Houses Management</h5>
                </div>
                <?php if ($_SESSION['user_role'] == 'admin') { ?>
                <div class="col col-6">
                    <div class="float-end"><a href="add_houses.php" class="btn btn-success btn-sm">Add</a></div>
                </div>
                <?php }?>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-bordered" id="houses-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Houses Name</th>
                            <th>Parking</th>
                            <th>Service Charge</th>
                            <th>No of Floor</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $user_role = $_SESSION['user_role'];

                        if ($user_role == 'superadmin') {
                            $stmt = oci_parse($conn, "SELECT * FROM HOUSE");
                            oci_execute($stmt);
                        }
                        else {
                            $user_id = $_SESSION['user_id'];

                            $stmt = oci_parse($conn, "SELECT * FROM HOUSE WHERE owner_id = :user_id");
                            oci_bind_by_name($stmt, ":user_id", $user_id);
                            oci_execute($stmt);
                        } 

                        

                        while ($row = oci_fetch_assoc($stmt)) {
                            echo '<tr>';
                            echo '<td>' . $row['HOUSE_ID'] . '</td>';
                            echo '<td>' . $row['HOUSE_NAME'] . '</td>';
                            echo '<td>' . $row['PARKING'] . '</td>';
                            echo '<td>' . $row['SERVICE_CHARGE'] . '</td>';
                            echo '<td>' . $row['NO_OF_FLOORS'] . '</td>';
                            echo '<td>';
                            if ($_SESSION['user_role'] == 'admin') {
                                echo '<button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#editHouseModal-' . $row['HOUSE_ID'] . '">Edit</button>';

                                // Edit House Modal
                                echo '
                                <div class="modal fade" id="editHouseModal-' . $row['HOUSE_ID'] . '" tabindex="-1" aria-labelledby="editHouseModalLabel-' . $row['HOUSE_ID'] . '" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="editHouseModalLabel-' . $row['HOUSE_ID'] . '">Edit House</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <!-- Add your form fields here for editing the house details -->
                                                <form action="update_house.php" method="POST">
                                                    <input type="hidden" name="house_id" value="' . $row['HOUSE_ID'] . '">
                                                    <!-- Add your form fields for editing house details -->
                                                    <div class="form-group">
                                                        <label for="house_name">House Name</label>
                                                        <input type="text" class="form-control" id="house_name" name="house_name" value="' . $row['HOUSE_NAME'] . '">
                                                    </div>
                                                    <!-- Add more form fields as needed -->
                                                </form>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                <button type="submit" class="btn btn-primary" form="editHouseForm-' . $row['HOUSE_ID'] . '">Save Changes</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>';

                                // Add the following script at the bottom of your HTML file, after including the necessary JavaScript libraries (Bootstrap and jQuery)
                                echo '
<script>
    $(document).ready(function() {
        $("#editHouseModal-' . $row['HOUSE_ID'] . '").on("shown.bs.modal", function() {
            $("#house_name").focus(); // Set focus on the house_name field when the modal is shown
        });
    });
</script>';
                                echo '<button type="button" class="btn btn-sm btn-danger delete_btn" data-id="' . $row['HOUSE_ID'] . '">Delete</button>&nbsp;';
                                echo '<a href="flat.php?house_id=' . $row['HOUSE_ID'] . '" class="btn btn-sm btn-warning">View flat</a>';

                            } else {
                                echo '<a href="flat.php?house_id=' . $row['HOUSE_ID'] . '" class="btn btn-sm btn-primary">View flat</a>&nbsp;';
                            }
                            echo '</td>';
                            echo '</tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>



<?php
include('footer.php');
?>

<script>
    $(document).ready(function () {
        $('#houses-table').DataTable();

        $(document).on('click', '.delete_btn', function () {
            if (confirm("Are you sure you want to remove this houses data?")) {
                window.location.href = 'house.php?action=delete&id=' + $(this).data('id') + '';
            }
        });
    });
</script>
<?php
session_start();
require_once 'config.php';

// Check if the user is logged in and has the role of renter
if (!isset($_SESSION['user_id'])) {
    header('Location: logout.php');
    exit();
}

include('header.php');

// Check if the form is submitted to add a complaint
if (isset($_POST['add_complaint'])) {
    $subject = $_POST['subject'];
    $description = $_POST['description'];
    $renter_id = $_SESSION['user_id'];

    // Insert the complaint into the Forum table with status "Pending"
    $stmt = oci_parse($conn, "INSERT INTO Forum (id, Subject, details, status, renter_id, complain_time) 
                              VALUES (forum_seq.nextval, :subject, :description, 'Pending', :renter_id, SYSTIMESTAMP)");
    oci_bind_by_name($stmt, ":subject", $subject);
    oci_bind_by_name($stmt, ":description", $description);
    oci_bind_by_name($stmt, ":renter_id", $renter_id);
    oci_execute($stmt);

    // Redirect back to the same page to refresh the content
    header('Location: complain.php');
    exit();
}

// Check if the form is submitted to update the complaint status and response
if (isset($_POST['update_complaint'])) {
    $complain_id = $_POST['complain_id'];
    $status = $_POST['status'];
    $response = $_POST['response'];

    // Update the complaint status and response in the Forum table
    $stmt = oci_parse($conn, "UPDATE Forum SET status = :status, Response = :response WHERE id = :complain_id");
    oci_bind_by_name($stmt, ":status", $status);
    oci_bind_by_name($stmt, ":response", $response);
    oci_bind_by_name($stmt, ":complain_id", $complain_id);
    oci_execute($stmt);

    // Output JavaScript code to redirect the page
    echo '<script>window.location.href = "complain.php";</script>';
    exit();
}

if (isset($_POST['delete_complaint'])) {
    $complain_id = $_POST['complain_id'];

    // Delete the complaint from the Forum table
    $stmt = oci_parse($conn, "DELETE FROM Forum WHERE id = :complain_id");
    oci_bind_by_name($stmt, ":complain_id", $complain_id);
    oci_execute($stmt);

    // Redirect back to the same page to refresh the content
    header('Location: complain.php');
    exit();
}


// Retrieve the complaints for the renter
$stmt = oci_parse($conn, "SELECT * FROM Forum WHERE renter_id = :renter_id OR owner_id = :owner_id ORDER BY STATUS");
oci_bind_by_name($stmt, ":renter_id", $_SESSION['user_id']);
oci_bind_by_name($stmt, ":owner_id", $_SESSION['user_id']);
oci_execute($stmt);
?>



<div class="container-fluid px-4">
    <h1 class="mt-4">Complaints</h1>

    <?php
    if ($_SESSION['user_role'] == 'user'):
        ?>
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Add Complaint</h5>
                <form method="POST">
                    <div class="mb-3">
                        <label for="subject" class="form-label">Subject</label>
                        <input type="text" class="form-control" id="subject" name="subject" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" required></textarea>
                    </div>
                    <button type="submit" name="add_complaint" class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div>

        <?php
    endif;
    ?>

    <?php //if (oci_fetch($stmt)): ?>
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">My Complaints</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Complaint ID</th>
                            <th>Subject</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Response</th>
                            <th>Complain Time</th>
                            <th>Action</th>

                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        oci_execute($stmt);
                        $hasComplaints = false; // Flag to track if there are any complaints
                        
                        while ($row = oci_fetch_assoc($stmt)):
                            $hasComplaints = true; // Set the flag to true if there are complaints
                            ?>
                            <tr>
                                <td>
                                    <?php echo $row['ID']; ?>
                                </td>
                                <td>
                                    <?php echo $row['SUBJECT']; ?>
                                </td>
                                <td>
                                    <?php echo $row['DETAILS']; ?>
                                </td>
                                <td>
                                    <?php if ($row['STATUS'] === 'Pending'): ?>
                                        <div type="button" class="btn btn-warning">
                                            <span>Pending</span>
                                        </div>
                                    <?php else: ?>
                                        <div type="button" class="btn btn-success">
                                            <span>Solved</span>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo $row['RESPONSE']; ?>
                                </td>
                                <td>
                                    <?php echo $row['COMPLAIN_TIME']; ?>
                                </td>

                                <td>
                                    <?php if ($_SESSION['user_role'] == 'admin'): ?>

                                        <?php if ($row['STATUS'] === 'Pending'): ?>
                                            <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                                data-bs-target="#updateModal-<?php echo $row['ID']; ?>">Update
                                            </button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <?php if ($_SESSION['user_role'] == 'user'): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="complain_id" value="<?php echo $row['ID']; ?>">
                                            <button type="submit" name="delete_complaint" class="btn btn-danger">Delete</button>
                                        </form>
                                    <?php endif; ?>



                                </td>
                            </tr>





                            <!-- Update Modal -->
                            <div class="modal fade" id="updateModal-<?php echo $row['ID']; ?>" tabindex="-1"
                                aria-labelledby="updateModalLabel-<?php echo $row['ID']; ?>" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="updateModalLabel-<?php echo $row['ID']; ?>">Update
                                                Complaint</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form method="POST">
                                                <input type="hidden" name="complain_id" value="<?php echo $row['ID']; ?>">
                                                <div class="mb-3">
                                                    <label for="status" class="form-label">Status</label>
                                                    <select class="form-select" id="status" name="status" required>
                                                        <option value="Pending" <?php echo ($row['STATUS'] === 'Pending') ? 'selected' : ''; ?>>
                                                            Pending
                                                        </option>
                                                        <option value="Solved" <?php echo ($row['STATUS'] === 'Solved') ? 'selected' : ''; ?>>
                                                            Solved
                                                        </option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="response" class="form-label">Response</label>
                                                    <textarea class="form-control" id="response" name="response"
                                                        required></textarea>
                                                </div>
                                                <button type="submit" name="update_complaint"
                                                    class="btn btn-primary">Update</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                        endwhile;

                        ?>

                        <?php if (!$hasComplaints): ?>
                            <tr>
                                <td colspan="7">No complaints found.</td>
                            </tr>
                        <?php endif; ?>


                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php // endif; ?>
</div>

<?php include('footer.php'); ?>
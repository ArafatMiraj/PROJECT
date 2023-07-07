<?php
session_start();

require_once 'config.php';

// Check if the user is logged in and has the "superadmin" role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'superadmin') {
    header('Location: logout.php');
    exit();
}

// Handle paper deletion
if (isset($_GET['delete_paper'])) {
    $paperId = $_GET['delete_paper'];

    // Delete the paper from the housepaper table
    $sql = "DELETE FROM housepaper WHERE paperno = :paperId";
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ":paperId", $paperId);
    oci_execute($stmt);

    $_SESSION['success'] = 'Paper deleted successfully.';
    header('Location: add_paper.php');
    exit();
}

// Handle form submission
if (isset($_POST['add_paper'])) {
    // Validate the form data
    $paperNo = $_POST['paper_no'];

    if (empty($paperNo)) {
        $errors[] = 'Paper Number is required.';
    }

    if (empty($errors)) {
        // Insert the paper into the housepaper table
        $sql = "INSERT INTO housepaper (paperno) VALUES (:paperNo)";
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ":paperNo", $paperNo);
        oci_execute($stmt);

        $_SESSION['success'] = 'Paper added successfully.';
        header('Location: add_paper.php');
        exit();
    }
}

include('header.php');
?>
<div class="container-fluid px-4">
    <h1 class="mt-4">Add Paper</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Add Paper</li>
    </ol>
    <div class="row">
        <div class="col-md-6">
            <?php
            if (isset($errors)) {
                foreach ($errors as $error) {
                    echo "<div class='alert alert-danger'>$error</div>";
                }
            }
            ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Add Paper</h5>
                </div>
                <div class="card-body">
                    <form id="add-paper-form" method="POST">
                        <div class="mb-3">
                            <label for="paper-no" class="form-label">Paper Number</label>
                            <input type="text" class="form-control" id="paper-no" name="paper_no">
                        </div>
                        <button type="submit" name="add_paper" class="btn btn-primary">Add Paper</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Paper ID</th>
                        <th>Paper Number</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Fetch and display the house papers
                    $sql = "SELECT * FROM housepaper";
                    $stmt = oci_parse($conn, $sql);
                    oci_execute($stmt);

                    while ($row = oci_fetch_assoc($stmt)) {
                        echo "<tr>";
                        echo "<td>{$row['ID']}</td>";
                        echo "<td>{$row['PAPERNO']}</td>";
                        echo "<td><a href='add_paper.php?delete_paper={$row['PAPERNO']}' class='btn btn-danger btn-sm'>Delete</a></td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<?php include('footer.php'); ?>
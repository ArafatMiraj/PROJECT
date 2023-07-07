<?php
include 'config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$errorMessage = '';

if (isset($_POST['btn_register'])) {
    // Get the form inputs
    $role = $_POST['role'];
    $fullName = $_POST['full_name'];
    $email = $_POST['email'];
    $contactNumber = $_POST['contact_number'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $paperNo = $_POST['paper_no'];

    // Validate form inputs
    $errors = [];

    // Check if email already exists
    $stmt = oci_parse($conn, "SELECT COUNT(*) FROM users WHERE email = :email");
    oci_bind_by_name($stmt, ":email", $email);
    oci_execute($stmt);
    $emailCount = oci_fetch_assoc($stmt)['COUNT(*)'];

    if ($emailCount > 0) {
        $errors[] = 'Email already exists. Please choose a different email.';
    }

    

    // Validate password match
    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }

    // Perform paper number validation against the housepaper table
    if ($role === 'admin') {
        // Check if the paper number exists in the housepaper table
        $stmt = oci_parse($conn, "SELECT COUNT(*) FROM housepaper WHERE paperno = :paperNo");
        oci_bind_by_name($stmt, ":paperNo", $paperNo);
        oci_execute($stmt);
        $paperCount = oci_fetch_assoc($stmt)['COUNT(*)'];

        if ($paperCount < 1 ) {
            $errors[] = 'Invalid paper number.';
        }
    }

    // If there are no validation errors, proceed with registration
    if (empty($errors)) {
        // Prepare the query to insert user details into the database
        $query = "INSERT INTO USERS (ID, NAME, EMAIL, PASSWORD, ROLE, CONTACT_NUMBER) VALUES (USERS_SEQ.NEXTVAL, :fullName, :email, :password, :role, :contactNumber)";
        $stmt = oci_parse($conn, $query);
        oci_bind_by_name($stmt, ":fullName", $fullName);
        oci_bind_by_name($stmt, ":email", $email);
        oci_bind_by_name($stmt, ":password", $password);
        oci_bind_by_name($stmt, ":role", $role);
        oci_bind_by_name($stmt, ":contactNumber", $contactNumber);

        // Execute the statement
        $result = oci_execute($stmt);

        if ($result) {
            // Redirect to index.php page
            $_SESSION['registration_success'] = true;
            header('Location: index.php');
            exit;
        } else {
            $errors[] = "Error occurred during registration. Please try again.";
        }
    }
}


?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Society Management System</title>
    <!-- Load Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="path/to/custom.css"> <!-- Add your custom CSS file path here -->
</head>

<body>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">

                    <div class="card-header">
                        <h3 class="card-title text-center">Registration</h3>
                    </div>
                    <div class="card-body">
                        <?php
                        if (!empty($errors)) {
                            foreach ($errors as $error) {
                                echo "<div class='alert alert-danger'>$error</div>";
                            }
                        }
                        ?>
                        <!-- Registration form -->
                        <form id="registration-form" method="post">
                            <div class="mb-3">
                                <label for="role" class="form-label">Role</label>
                                <select class="form-control" id="role" name="role" required>
                                    <option value="user">Renter</option>
                                    <option value="admin">Owner</option>

                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="full_name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="contact_number" class="form-label">Contact Number</label>
                                <input type="text" class="form-control" id="contact_number" name="contact_number"
                                    required>
                            </div>
                            <!-- Added input field for paper number -->
                            <div class="mb-3 owner-paper" style="display: none;">
                                <label for="paper_no" class="form-label">Paper Number</label>
                                <input type="text" class="form-control" id="paper_no" name="paper_no">
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="confirm_password"
                                    name="confirm_password" required>
                            </div>

                            <div class="text-center">
                                <button type="submit" name="btn_register" class="btn btn-primary">Register</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#role').change(function () {
                if ($(this).val() === 'admin') {
                    $('.owner-paper').show();
                    $('#paper_no').prop('required', true); // Set the paper number field as required
                } else {
                    $('.owner-paper').hide();
                    $('#paper_no').prop('required', false); // Remove the required attribute from the paper number field
                }
            });
        });

    </script>

    <!-- Load Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="path/to/custom.js"></script> <!-- Add your custom JS file path here -->
</body>

</html>


<!-- Your index.php content here -->

<?php
session_start();

if (isset($_SESSION['registration_success']) && $_SESSION['registration_success']) {
    echo "<div class='alert alert-success'>Registration successful! Please log in to continue.</div>";
    unset($_SESSION['registration_success']);
}
include 'config.php';



// Rest of the code...

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$errorMessage = '';

if (isset($_POST['btn_login'])) {
    // Get the email and password entered by the user
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Validate email address format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    // Validate password field is not empty
    if (empty($password)) {
        $errors[] = 'Please enter a password.';
    }

    // If there are no validation errors, attempt to log in
    if (empty($errors)) {
        // Prepare the query to retrieve the user details from the database
// Prepare the query to retrieve the user details from the database
        $query = "SELECT * FROM users WHERE email = :email";
        $stmt = oci_parse($conn , $query);
        oci_bind_by_name($stmt, ":email", $email);
        oci_execute($stmt);

        // Fetch the user details from the query result
        $user = oci_fetch_assoc($stmt);




            // Use the password_verify function to check if the entered password matches the password hash
            if ($user && $user['PASSWORD'] === $password) {
                // Password is correct, log the user in
                $_SESSION['user_id'] = $user['ID'];
                $_SESSION['user_role'] = $user['ROLE'];
                $_SESSION['user_name'] = $user['NAME'];
                if ($user['ROLE'] == 'admin') {
                    header('Location: dashboard.php');
                } else {
                    header('Location: dashboard.php');
                }
                exit;
            } else {
                // Password is incorrect, show an error message
                $errors[] = "Invalid password";
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
    <style>
        .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 100vh;
        }

        .left-section {
            flex: 1;
            padding-left: 40px;
        }

        .right-section {
            flex: 1;
            padding-right: 40px;
        }

        .card {
            width: 400px;
            margin: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="left-section">
            <h1>Society Management System</h1>
            <h3>Welcome to our system!</h3>
            <p>If you're a new user, please register below:</p>
            <a href="registration.php" class="btn btn-primary">Register</a>
        </div>
        <div class="right-section">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title text-center">Login</h3>
                </div>
                <div class="card-body">
                    <?php
                    if (isset($errors)) {
                        foreach ($errors as $error) {
                            echo "<div class='alert alert-danger'>$error</div>";
                        }
                    }
                    ?>
                    <!-- Login form -->
                    <form id="login-form" method="post">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email">
                            <div class="invalid-feedback">Please enter a valid email address.</div>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password">
                            <div class="invalid-feedback">Please enter a password.</div>
                        </div>
                        <button type="submit" name="btn_login" class="btn btn-primary">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Load Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>



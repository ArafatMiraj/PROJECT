<?php
session_start();

require_once 'config.php';

if (isset($_POST['btn_change_password'])) {
    // Validate the form data
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($current_password)) {
        $errors[] = 'Current password is required';
    }
    if (empty($new_password)) {
        $errors[] = 'New password is required';
    } elseif (strlen($new_password) < 6) {
        $errors[] = 'New password must be at least 6 characters long';
    }
    if (empty($confirm_password)) {
        $errors[] = 'Confirm password is required';
    } elseif ($new_password !== $confirm_password) {
        $errors[] = 'New password and confirm password do not match';
    }

    // If the form data is valid, update the user's password
    if (empty($errors)) {
        $user_id = $_SESSION['user_id'];
        $sql = "SELECT password FROM users WHERE id = :user_id";
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ":user_id", $user_id);
        oci_execute($stmt);
        $row = oci_fetch_assoc($stmt);

        if ($row && $current_password === $row['PASSWORD']) {
            $sql = "UPDATE users SET password = :new_password WHERE id = :user_id";
            $stmt = oci_parse($conn, $sql);
            oci_bind_by_name($stmt, ":new_password", $new_password);
            oci_bind_by_name($stmt, ":user_id", $user_id);
            oci_execute($stmt);
            $_SESSION['success'] = 'Password changed successfully';
        } else {
            $errors[] = 'Current password is incorrect';
        }
    }
}

if (!isset($_SESSION['user_id'])) {
    header('Location: logout.php');
    exit();
}

include('header.php');
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Profile</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Change Password</li>
    </ol>
    <div class="col-md-4">
        <?php
        if (isset($_SESSION['success'])) {
            echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';

            unset($_SESSION['success']);
        }

        if (isset($errors)) {
            foreach ($errors as $error) {
                echo "<div class='alert alert-danger'>$error</div>";
            }
        }
        ?>
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Change Password</h5>
            </div>
            <div class="card-body">
                <form method="post">
                    <div class="mb-3">
                        <label for="current-password">Current Password</label>
                        <input type="password" class="form-control" id="current-password" name="current_password">
                    </div>
                    <div class="mb-3">
                        <label for="new-password">New Password</label>
                        <input type="password" class="form-control" id="new-password" name="new_password">
                    </div>
                    <div class="mb-3">
                        <label for="confirm-password">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm-password" name="confirm_password">
                    </div>
                    <button type="submit" name="btn_change_password" class="btn btn-primary">Change Password</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php

include('footer.php');

?>

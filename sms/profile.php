<?php
session_start();

require_once 'config.php';

if(isset($_POST['save_button']))
{
    // Validate name
    if (empty(trim($_POST['name']))) 
    {
        $errors[] = 'Please enter your name.';
    } 
    else 
    {
        $name = trim($_POST['name']);
    }

    // Validate email
    if (empty(trim($_POST['email'])))
    {
        $errors[] = 'Please enter your email address.';
    } 
    else 
    {
        $email = trim($_POST['email']);
    }   

    if(empty($errors))
    {
        $sql = "UPDATE users SET name = :name, email = :email WHERE id = :user_id";
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ":name", $name);
        oci_bind_by_name($stmt, ":email", $email);
        oci_bind_by_name($stmt, ":user_id", $_SESSION['user_id']);

        // Execute the statement
        $result = oci_execute($stmt);

        if ($result) {
            // Registration successful, redirect to profile.php page
            $_SESSION['success'] = 'Your profile has been updated.';
            header('Location: profile.php');
            exit;
        } else {
            $errors[] = "Error occurred during profile update. Please try again.";
        }
    }
}

if (!isset($_SESSION['user_id'])) 
{
    header('Location: logout.php');
    exit();
}
else
{
    // Check if the user ID is set in the query string
    if (isset($_SESSION['user_id'])) 
    {
        // Retrieve the user ID from the query string
        $user_id = $_SESSION['user_id'];

        // Prepare a SELECT statement to retrieve the user's details
        $stmt = oci_parse($conn, "SELECT * FROM users WHERE id = :user_id");
        oci_bind_by_name($stmt, ":user_id", $user_id);
        oci_execute($stmt);

        // Fetch the user's details from the database
        $user = oci_fetch_assoc($stmt);
    }
}

include('header.php');

?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Profile</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Edit Profile</li>
    </ol>
    <div class="col-md-4">
        <?php

        if(isset($_SESSION['success']))
        {
            echo '<div class="alert alert-success">'.$_SESSION['success'].'</div>';

            unset($_SESSION['success']);
        }

        if(isset($errors))
        {
            foreach ($errors as $error) 
            {
                echo "<div class='alert alert-danger'>$error</div>";
            }
        }

        ?>
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Edit Profile</h5>
            </div>
            <div class="card-body">
                <form method="post">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo $user['NAME']; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo $user['EMAIL']; ?>">
                    </div>
                    <button type="submit" name="save_button" class="btn btn-primary">Save</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php

include('footer.php');

?>

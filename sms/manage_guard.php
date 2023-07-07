<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: logout.php');
    exit();
}

// Fetch houses information for select options
$owner_id = $_SESSION['user_id'];
$stmt = oci_parse($conn, "SELECT House_ID, House_Name FROM HOUSE H WHERE H.owner_id = :owner_id");
oci_bind_by_name($stmt, ":owner_id", $owner_id); // Bind the owner_id");
oci_execute($stmt);
$houses = oci_fetch_all($stmt, $housesArr, null, null, OCI_FETCHSTATEMENT_BY_ROW);

// Function to validate the guard form data
function validateGuardForm($guard)
{
    $errors = array();

    if (empty($guard['district'])) {
        $errors[] = 'District is required';
    }

    if (empty($guard['name'])) {
        $errors[] = 'Name is required';
    }

    return $errors;
}

// Add or Edit guard
if (isset($_POST['add_guard']) || isset($_POST['edit_guard'])) {
    $guard = array(
        'guard_id' => $_POST['guard_id'],
        'district' => $_POST['district'],
        'name' => $_POST['name'],
        'contact_info' => $_POST['contact_info'],
        'gender' => $_POST['gender'],
        'house_id' => $_POST['house_id']
    );

    // Validate the guard form data
    $errors = validateGuardForm($guard);

    if (empty($errors)) {
        if (isset($_POST['add_guard'])) {
            $stmt = oci_parse($conn, "INSERT INTO GUARD (District, Name, Contact_info, Gender, House_ID) 
                                      VALUES (:district, :name, :contact_info, :gender, :house_id)");
        } else {
            $stmt = oci_parse($conn, "UPDATE GUARD 
                                      SET District = :district, Name = :name, Contact_info = :contact_info, 
                                          Gender = :gender, House_ID = :house_id 
                                      WHERE Guard_ID = :guard_id");
            oci_bind_by_name($stmt, ":guard_id", $guard['guard_id']);
        }

        oci_bind_by_name($stmt, ":district", $guard['district']);
        oci_bind_by_name($stmt, ":name", $guard['name']);
        oci_bind_by_name($stmt, ":contact_info", $guard['contact_info']);
        oci_bind_by_name($stmt, ":gender", $guard['gender']);
        oci_bind_by_name($stmt, ":house_id", $guard['house_id']);
        oci_execute($stmt);

        $_SESSION['success'] = isset($_POST['add_guard']) ? 'New Guard Added' : 'Guard Updated';

        header('Location: guards.php');
        exit();
    }
}

// Delete guard
if (isset($_GET['delete_guard'])) {
    $guard_id = $_GET['delete_guard'];

    $stmt = oci_parse($conn, "DELETE FROM GUARD WHERE Guard_ID = :guard_id");
    oci_bind_by_name($stmt, ":guard_id", $guard_id);
    oci_execute($stmt);

    $_SESSION['success'] = 'Guard Deleted';

    header('Location: guards.php');
    exit();
}

// Check if editing a guard
$edit_guard_id = isset($_GET['edit_guard']) ? $_GET['edit_guard'] : null;
$edit_guard = array();

if ($edit_guard_id) {
    $stmt = oci_parse($conn, "SELECT G.Guard_ID, G.District, G.Name, G.Contact_info, G.Gender, H.House_Name 
                            FROM GUARD G
                            INNER JOIN HOUSE H ON G.House_ID = H.House_ID
                            WHERE G.Guard_ID = :guard_id");
    oci_bind_by_name($stmt, ":guard_id", $edit_guard_id);
    oci_execute($stmt);
    $edit_guard = oci_fetch_assoc($stmt);
}

include('header.php');
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Manage Guards</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="guards.php">Guard Management</a></li>
        <li class="breadcrumb-item active">Manage Guards</li>
    </ol>
    <?php
    if (isset($_SESSION['success'])) {
        echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
        unset($_SESSION['success']);
    }
    ?>
    <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col col-6">
                    <h5 class="card-title">Manage Guards</h5>
                </div>
                <div class="col col-6">
                    <div class="float-end"><a href="guards.php" class="btn btn-primary btn-sm">View Guards</a></div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><?php echo $edit_guard ? 'Edit Guard' : 'Add Guard'; ?></h5>
                    </div>
                    <div class="card-body">
                        <form id="manage-guard-form" method="POST">
                            <input type="hidden" name="guard_id" value="<?php echo $edit_guard ? $edit_guard['GUARD_ID'] : ''; ?>">
                            <div class="mb-3">
                                <label for="district" class="form-label">District</label>
                                <input type="text" class="form-control" id="district" name="district" value="<?php echo $edit_guard ? $edit_guard['DISTRICT'] : ''; ?>">
                            </div>
                            <div class="mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo $edit_guard ? $edit_guard['NAME'] : ''; ?>">
                            </div>
                            <div class="mb-3">
                                <label for="contact-info" class="form-label">Contact Info</label>
                                <input type="text" class="form-control" id="contact-info" name="contact_info" value="<?php echo $edit_guard ? $edit_guard['CONTACT_INFO'] : ''; ?>">
                            </div>
                            <div class="mb-3">
                                <label for="gender" class="form-label">Gender</label>
                                <select name="gender" class="form-control">
                                    <option value="male" <?php echo $edit_guard && $edit_guard['GENDER'] == 'male' ? 'selected' : ''; ?>>Male</option>
                                    <option value="female" <?php echo $edit_guard && $edit_guard['GENDER'] == 'female' ? 'selected' : ''; ?>>Female</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="house_id" class="form-label">House Name</label>
                                <select name="house_id" class="form-control">
                                    <?php foreach ($housesArr as $house) { ?>
                                        <option value="<?php echo $house['HOUSE_ID']; ?>" <?php echo $edit_guard && $edit_guard['HOUSE_ID'] == $house['HOUSE_ID'] ? 'selected' : ''; ?>><?php echo $house['HOUSE_NAME']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <button type="submit" name="<?php echo $edit_guard ? 'edit_guard' : 'add_guard'; ?>" class="btn btn-primary"><?php echo $edit_guard ? 'Update Guard' : 'Add Guard'; ?></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include('footer.php');
?>

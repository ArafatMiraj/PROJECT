<?php
session_start();
include 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check user role
$userRole = $_SESSION['user_role'];

// Generate Bills Button - Only visible to admin
$generateBillsButton = '';
if ($userRole === 'admin') {
    $generateBillsButton = '<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#generateBillsModal">Generate Bills</button>';
}

// Check if the "Generate Bills" button is clicked
if (isset($_POST['generate_bills'])) {
    $currentMonth = date('F Y');
    $query = "INSERT INTO bills (id, flat_id, amount, month, paid_date, payment_method, reference, paid_amount)
    SELECT bills_seq.NEXTVAL, f.flat_no, (f.flat_rent + h.service_charge) AS amount, TO_CHAR(SYSDATE, 'Month') AS month, NULL, NULL, NULL, NULL
    FROM flat f
    JOIN house h ON f.house_id = h.house_id
    WHERE f.owner_id = :owner_id AND f.renter_id IS NOT NULL";

    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':owner_id', $_SESSION['user_id']);
    oci_execute($stmt);

    if (oci_num_rows($stmt) > 0) {
        $_SESSION['success'] = "Bills for $currentMonth are generated successfully!";
    } else {
        $_SESSION['error'] = "Failed to generate bills for $currentMonth. Bills already generated.";
    }
    header("Location: bills.php");
    exit();
}

if (isset($_POST['pay_bill'])) {
    $billId = $_POST['bill_id'];
    $paymentMethod = $_POST['payment_method'];
    $reference = $_POST['reference'];
    $paidAmount = $_POST['paid_amount'];

    // Perform the update query to set bill_status to 'Sent' and update other values
    $updateQuery = "UPDATE bills SET bill_status = 'Sent', payment_method = :payment_method, reference = :reference, paid_amount = :paid_amount, paid_date = SYSDATE WHERE id = :bill_id";
    $updateStmt = oci_parse($conn, $updateQuery);
    oci_bind_by_name($updateStmt, ":payment_method", $paymentMethod);
    oci_bind_by_name($updateStmt, ":reference", $reference);
    oci_bind_by_name($updateStmt, ":paid_amount", $paidAmount);
    oci_bind_by_name($updateStmt, ":bill_id", $billId);

    $updateResult = oci_execute($updateStmt);

    if ($updateResult) {
        // Update successful
        $_SESSION['success'] = "Bill status updated successfully.";
        header("Location: bills.php"); // Redirect to the bills.php page
        exit();
    } else {
        // Update failed
        $error = "Error updating bill status.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_bill_status'])) {
    $billId = $_POST['bill_id'];

    // Update the bill status to "Paid"
    $updateQuery = "UPDATE bills SET bill_status = 'Paid' WHERE id = :bill_id";
    $stmt = oci_parse($conn, $updateQuery);
    oci_bind_by_name($stmt, ":bill_id", $billId);
    oci_execute($stmt);

    // Check if the update was successful
    if (oci_num_rows($stmt) > 0) {
        $_SESSION['success'] = "Bill status updated successfully.";
    } else {
        $_SESSION['error'] = "Failed to update bill status.";
    }

    // Redirect back to the bills page
    header("Location: bills.php");
    exit();
}

include 'header.php';

if ($_SESSION['user_role'] == 'admin') {
    // Admin role
    $adminId = $_SESSION['user_id'];
    $query = "SELECT * FROM bills WHERE flat_id IN (SELECT flat_no FROM flat WHERE owner_id = :owner_id)";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ":owner_id", $adminId);
    oci_execute($stmt);
} elseif ($_SESSION['user_role'] == 'user') {
    // User role
    $renterId = $_SESSION['user_id'];
    $query = "SELECT * FROM bills WHERE flat_id IN (SELECT flat_no FROM flat WHERE renter_id = :renter_id)";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ":renter_id", $renterId);
    oci_execute($stmt);
} else {
    // Other user roles
    // Handle the appropriate logic for other roles or display an error message
}

?>


<div class="container">
    <h1>Bills Management</h1>
    <!-- Display the success or error message -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?php echo $_SESSION['success']; ?>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php elseif (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?php echo $_SESSION['error']; ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    <!-- Generate Bills Button -->
    <?php echo $generateBillsButton; ?>

    <br><br>

    <!-- Bill Table -->
    <table class="table">
        <thead>
            <tr>
                <th>Bill ID</th>
                <th>Flat ID</th>
                <th>Amount</th>
                <th>Month</th>
                <th>Paid Amount</th>
                <th>Paid Date</th>
                <th>Payment Method</th>
                <th>Reference</th>
                <th>Bill Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = oci_fetch_assoc($stmt)): ?>
                <tr>
                    <td>
                        <?php echo $row['ID']; ?>
                    </td>
                    <td>
                        <?php echo $row['FLAT_ID']; ?>
                    </td>
                    <td>
                        <?php echo $row['AMOUNT']; ?>
                    </td>
                    <td>
                        <?php echo $row['MONTH']; ?>
                    </td>
                    <td>
                        <?php echo $row['PAID_AMOUNT']; ?>
                    </td>
                    <td>
                        <?php echo $row['PAID_DATE']; ?>
                    </td>
                    <td>
                        <?php echo $row['PAYMENT_METHOD']; ?>
                    </td>
                    <td>
                        <?php echo $row['REFERENCE']; ?>
                    </td>
                    <td>
                        <?php echo $row['BILL_STATUS']; ?>
                    </td>
                    <td>
                        <?php if ($userRole === 'admin'): ?>
                            <?php if ($row['BILL_STATUS'] === 'Sent'): ?>
                                <button type="button" class="btn btn-success" data-toggle="modal"
                                    data-target="#updateBillModal-<?php echo $row['ID']; ?>">Received</button>

                            <?php endif; ?>
                        <?php endif; ?>
                        <?php if ($userRole === 'user'): ?>
                            <?php if ($row['BILL_STATUS'] === 'unpaid'): ?>
                                <button type="button" class="btn btn-success" data-toggle="modal"
                                    data-target="#payBillModal-<?php echo $row['ID']; ?>">Pay Bill</button>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                </tr>

                <div class="modal fade" id="payBillModal-<?php echo $row['ID']; ?>" tabindex="-1"
                    aria-labelledby="payBillModalLabel-<?php echo $row['ID']; ?>" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="payBillModalLabel-<?php echo $row['ID']; ?>">Pay Bill</h5>
                                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form method="POST">
                                    <input type="hidden" name="bill_id" value="<?php echo $row['ID']; ?>">
                                    <div class="mb-3">
                                        <label for="payment_method" class="form-label">Payment Method</label>
                                        <select class="form-select" id="payment_method" name="payment_method" required>
                                            <option value="Bkash">Bkash</option>
                                            <option value="Bank">Bank</option>
                                            <option value="Cash">Cash</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="reference" class="form-label">Reference</label>
                                        <input type="text" class="form-control" id="reference" name="reference" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="paid_amount" class="form-label">Paid Amount</label>
                                        <input type="number" class="form-control" id="paid_amount" name="paid_amount"
                                            required>
                                    </div>
                                    <button type="submit" name="pay_bill" class="btn btn-primary">Pay</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="updateBillModal-<?php echo $row['ID']; ?>" tabindex="-1"
                    aria-labelledby="updateBillModalLabel-<?php echo $row['ID']; ?>" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="updateBillModalLabel-<?php echo $row['ID']; ?>">Confirm Bill
                                    Payment</h5>
                                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p>Are you sure you want to mark this bill as paid?</p>
                                <form method="POST">
                                    <input type="hidden" name="bill_id" value="<?php echo $row['ID']; ?>">
                                    <button type="submit" name="update_bill_status" class="btn btn-primary">Confirm
                                        Payment</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            <?php endwhile; ?>
        </tbody>
    </table>
</div>


<!-- Generate Bills Modal - Only visible to admin -->
<?php if ($userRole === 'admin'): ?>
    <div class="modal fade" id="generateBillsModal" tabindex="-1" aria-labelledby="generateBillsModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="generateBillsModalLabel">Generate Bills for
                        <?php echo date('F Y'); ?>
                    </h5>
                    <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to generate bills for
                        <?php echo date('F Y'); ?>?
                    </p>
                    <form method="POST">
                        <button type="submit" name="generate_bills" class="btn btn-primary">Generate</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Include jQuery and Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<?php
include 'footer.php';
?>
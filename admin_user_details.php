<?php
include "admin_header.php";
include("db.php");

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$userID = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch user details
$sql = "SELECT * FROM CUSTOMER WHERE CustomerID = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $userID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$user) {
    die("User not found.");
}

// Fetch user's orders
$ordersSql = "SELECT * FROM orders WHERE O_CustomerID = ? ORDER BY OrderDate DESC";
$stmt = mysqli_prepare($conn, $ordersSql);
mysqli_stmt_bind_param($stmt, "i", $userID);
mysqli_stmt_execute($stmt);
$ordersResult = mysqli_stmt_get_result($stmt);
?>

<div class="container mt-5">
    <h2>User Details</h2>
    <a href="admin_users.php" class="btn btn-secondary">← Back to User List</a>
    
    <div class="info-section">
        <h3>Personal Information</h3>
        <div class="info-row">
            <span class="info-label">Customer ID:</span>
            <span><?php echo $user['CustomerID']; ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Full Name:</span>
            <span><?php echo htmlspecialchars($user['CFName'] . ' ' . $user['CMName'] . ' ' . $user['CLName']); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Email:</span>
            <span><?php echo htmlspecialchars($user['Email']); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Phone Number:</span>
            <span><?php echo htmlspecialchars($user['PhoneNum']); ?></span>
        </div>
    </div>

    <div class="info-section">
        <h3>Address Information</h3>
        <div class="info-row">
            <span class="info-label">Street:</span>
            <span><?php echo htmlspecialchars($user['StreetName']); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Town/City:</span>
            <span><?php echo htmlspecialchars($user['Town']); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Zip Code:</span>
            <span><?php echo htmlspecialchars($user['ZipCode']); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Country:</span>
            <span><?php echo htmlspecialchars($user['Country']); ?></span>
        </div>
    </div>

    <div class="info-section">
        <h3>Order History</h3>
        <?php if (mysqli_num_rows($ordersResult) > 0): ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Date</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = mysqli_fetch_assoc($ordersResult)): ?>
                        <tr>
                            <td>#<?php echo $order['OrderID']; ?></td>
                            <td><?php echo $order['OrderDate']; ?></td>
                            <td>$<?php echo $order['TotalAmount']; ?></td>
                            <td><?php echo $order['ShipStat']; ?></td>
                            <td><a href="admin_order_details.php?id=<?php echo $order['OrderID']; ?>" class="btn btn-sm btn-info">View Order</a></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>This user has no orders yet.</p>
        <?php endif; ?>
    </div>
</div>

<?php 
mysqli_stmt_close($stmt); 
include "admin_footer.php"; 
?>
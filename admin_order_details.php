<?php
include "admin_header.php";
include("db.php");

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$orderID = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch order details with customer info
$sql = "SELECT o.*, c.CFName, c.CLName, c.Email, c.PhoneNum, c.StreetName, c.Town, c.Country, c.ZipCode
        FROM orders o
        LEFT JOIN CUSTOMER c ON o.O_CustomerID = c.CustomerID
        WHERE o.OrderID = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $orderID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$order) {
    die("Order not found.");
}

// Fetch books in this order
$booksSql = "SELECT b.*, oc.ISBN_Order
             FROM order_contents oc
             JOIN BOOK b ON oc.ISBN_Order = b.ISBN
             WHERE oc.O_ID = ?";
$stmt = mysqli_prepare($conn, $booksSql);
mysqli_stmt_bind_param($stmt, "i", $orderID);
mysqli_stmt_execute($stmt);
$booksResult = mysqli_stmt_get_result($stmt);

// Handle status update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $newStatus = mysqli_real_escape_string($conn, $_POST['status']);
    $updateSql = "UPDATE orders SET ShipStat = ? WHERE OrderID = ?";
    $updateStmt = mysqli_prepare($conn, $updateSql);
    mysqli_stmt_bind_param($updateStmt, "si", $newStatus, $orderID);
    mysqli_stmt_execute($updateStmt);
    mysqli_stmt_close($updateStmt);
    
    $message = "Order status updated to: $newStatus";
    // Refresh order data
    header("Location: admin_order_details.php?id=$orderID&updated=1");
    exit;
}

$showMessage = isset($_GET['updated']) ? "Order status updated successfully!" : "";
?>

<div class="container mt-5">
    <h2>Order Details - #<?php echo $orderID; ?></h2>
    <a href="admin_orders.php" class="btn btn-secondary">← Back to Orders List</a>
    
    <?php if ($showMessage): ?>
        <div class="alert alert-success" style="margin-top: 15px;"><?php echo $showMessage; ?></div>
    <?php endif; ?>

    <div class="info-section">
        <h3>Order Information</h3>
        <div class="info-row">
            <span class="info-label">Order ID:</span>
            <span>#<?php echo $order['OrderID']; ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Order Date:</span>
            <span><?php echo $order['OrderDate']; ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Total Amount:</span>
            <span style="font-size: 20px; color: green;"><strong>$<?php echo $order['TotalAmount']; ?></strong></span>
        </div>
        <div class="info-row">
            <span class="info-label">Current Status:</span>
            <span class="badge" style="background: #667eea; color: white; padding: 8px 15px;">
                <?php echo $order['ShipStat']; ?>
            </span>
        </div>
        
        <div style="margin-top: 20px;">
            <form method="POST" style="display: inline-block;">
                <label><strong>Update Status:</strong></label>
                <select name="status" class="form-control" style="display: inline-block; width: 200px; margin: 0 10px;">
                    <option value="Processing" <?php echo $order['ShipStat'] == 'Processing' ? 'selected' : ''; ?>>Processing</option>
                    <option value="Shipped" <?php echo $order['ShipStat'] == 'Shipped' ? 'selected' : ''; ?>>Shipped</option>
                    <option value="Delivered" <?php echo $order['ShipStat'] == 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                </select>
                <button type="submit" name="update_status" class="btn btn-warning">Update Status</button>
            </form>
        </div>
    </div>

    <div class="info-section">
        <h3>Customer Information</h3>
        <div class="info-row">
            <span class="info-label">Customer Name:</span>
            <span><?php echo htmlspecialchars($order['CFName'] . ' ' . $order['CLName']); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Email:</span>
            <span><?php echo htmlspecialchars($order['Email']); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Phone:</span>
            <span><?php echo htmlspecialchars($order['PhoneNum']); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Shipping Address:</span>
            <span><?php echo htmlspecialchars($order['StreetName'] . ', ' . $order['Town'] . ', ' . $order['Country'] . ' - ' . $order['ZipCode']); ?></span>
        </div>
        <div style="margin-top: 15px;">
            <a href="admin_user_details.php?id=<?php echo $order['O_CustomerID']; ?>" class="btn btn-info">View Customer Profile</a>
        </div>
    </div>

    <div class="info-section">
        <h3>Ordered Books</h3>
        <?php if (mysqli_num_rows($booksResult) > 0): ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ISBN</th>
                        <th>Title</th>
                        <th>Edition</th>
                        <th>Price</th>
                        <th>Year</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $subtotal = 0;
                    while ($book = mysqli_fetch_assoc($booksResult)): 
                        $subtotal += $book['Price'];
                    ?>
                        <tr>
                            <td><?php echo $book['ISBN']; ?></td>
                            <td><?php echo htmlspecialchars($book['Title']); ?></td>
                            <td><?php echo htmlspecialchars($book['B_Edition']); ?></td>
                            <td>$<?php echo $book['Price']; ?></td>
                            <td><?php echo $book['Publication_Year']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" style="text-align: right;"><strong>Total:</strong></td>
                        <td colspan="2"><strong>$<?php echo $order['TotalAmount']; ?></strong></td>
                    </tr>
                </tfoot>
            </table>
        <?php else: ?>
            <p>No books found for this order.</p>
        <?php endif; ?>
    </div>
</div>

<?php include "admin_footer.php"; ?>
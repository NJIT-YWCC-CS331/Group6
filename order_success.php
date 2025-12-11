<?php
session_start();
include "header.php";
include "db.php";

// Must be logged in
if (!isset($_SESSION['userid'])) {
    header("Location: login.php");
    exit;
}

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($order_id == 0) {
    header("Location: books.php");
    exit;
}

// Get order details
$stmt = mysqli_prepare($conn, "SELECT * FROM ORDERS WHERE OrderID = ? AND O_CustomerID = ?");
mysqli_stmt_bind_param($stmt, "ii", $order_id, $_SESSION['userid']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$order) {
    echo "<div class='container mt-5'><p>Order not found.</p></div>";
    include "footer.php";
    exit;
}

// Get order items
$stmt = mysqli_prepare($conn, "
    SELECT b.* FROM ORDER_CONTENTS oc
    JOIN BOOK b ON oc.ISBN_Order = b.ISBN
    WHERE oc.O_ID = ?
");
mysqli_stmt_bind_param($stmt, "i", $order_id);
mysqli_stmt_execute($stmt);
$items_result = mysqli_stmt_get_result($stmt);
$order_items = array();
while ($item = mysqli_fetch_assoc($items_result)) {
    $order_items[] = $item;
}
mysqli_stmt_close($stmt);
?>

<style>
.success-box {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 40px;
    border-radius: 12px;
    text-align: center;
    margin: 30px auto;
    max-width: 600px;
}
.checkmark {
    font-size: 64px;
    margin-bottom: 20px;
}
.order-details-box {
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    margin: 20px 0;
}
</style>

<div class="container mt-5">
    <div class="success-box">
        <div class="checkmark">✓</div>
        <h1>Order Placed Successfully!</h1>
        <p style="font-size: 18px; margin-top: 15px;">Thank you for your purchase</p>
        <p style="font-size: 24px; font-weight: bold; margin-top: 20px;">
            Order #<?php echo $order_id; ?>
        </p>
    </div>
    
    <div class="order-details-box">
        <h3>Order Details</h3>
        <hr>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <p><strong>Order Date:</strong> <?php echo date('F j, Y', strtotime($order['OrderDate'])); ?></p>
                <p><strong>Status:</strong> <span class="badge" style="background: #ffc107; padding: 5px 15px; border-radius: 5px;"><?php echo $order['ShipStat']; ?></span></p>
            </div>
            <div>
                <p><strong>Total Amount:</strong> <span style="font-size: 24px; color: #667eea; font-weight: bold;">$<?php echo number_format($order['TotalAmount'], 2); ?></span></p>
            </div>
        </div>
        
        <h4>Items Ordered</h4>
        <table class="table table-striped" style="width: 100%; margin-top: 15px;">
            <thead>
                <tr style="background: #f4f4f4;">
                    <th style="padding: 10px;">Book Title</th>
                    <th style="padding: 10px;">ISBN</th>
                    <th style="padding: 10px;">Edition</th>
                    <th style="padding: 10px;">Price</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($order_items as $item): ?>
                    <tr>
                        <td style="padding: 10px;"><?php echo htmlspecialchars($item['Title']); ?></td>
                        <td style="padding: 10px;"><?php echo $item['ISBN']; ?></td>
                        <td style="padding: 10px;"><?php echo htmlspecialchars($item['B_Edition']); ?></td>
                        <td style="padding: 10px;">$<?php echo number_format($item['Price'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <div style="text-align: center; margin: 30px 0;">
        <a href="orders.php" class="btn btn-primary" style="padding: 12px 30px; font-size: 16px; margin-right: 10px;">View All Orders</a>
        <a href="books.php" class="btn btn-success" style="padding: 12px 30px; font-size: 16px;">Continue Shopping</a>
    </div>
    
    <div style="background: #f9f9f9; padding: 20px; border-radius: 8px; margin: 20px 0;">
        <h4>What's Next?</h4>
        <ul style="line-height: 2;">
            <li>You will receive an email confirmation shortly</li>
            <li>Your order is being processed</li>
            <li>You can track your order status in <a href="orders.php">My Orders</a></li>
            <li>Estimated delivery: 3-5 business days</li>
        </ul>
    </div>
</div>

<?php include "footer.php"; ?>
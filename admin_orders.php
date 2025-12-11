<?php
include "admin_header.php";
include("db.php");

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

// Handle status update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $orderID = intval($_POST['order_id']);
    $newStatus = mysqli_real_escape_string($conn, $_POST['new_status']);
    
    $sql = "UPDATE orders SET ShipStat = ? WHERE OrderID = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $newStatus, $orderID);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    $successMessage = "Order #$orderID status updated to: $newStatus";
}

// Handle search - Email is directly in CUSTOMER table
$search = "";
$sql = "SELECT o.*, c.CFName, c.CLName, c.Email 
        FROM orders o 
        LEFT JOIN CUSTOMER c ON o.O_CustomerID = c.CustomerID";

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $sql .= " WHERE o.OrderID LIKE '%$search%' OR o.O_CustomerID LIKE '%$search%' OR c.CFName LIKE '%$search%' OR c.CLName LIKE '%$search%'";
}

$sql .= " ORDER BY o.OrderDate DESC";
$result = mysqli_query($conn, $sql);
?>

<div class="container mt-5">
    <h2>Orders / Reservations</h2>
    
    <?php if (isset($successMessage)): ?>
        <div class="alert alert-success"><?php echo $successMessage; ?></div>
    <?php endif; ?>
    
    <div class="search-box">
        <form method="GET" style="display: flex; gap: 10px;">
            <input type="text" name="search" class="form-control" placeholder="Search by Order ID, Customer ID, or Name" value="<?php echo htmlspecialchars($search); ?>" style="flex: 1;">
            <button type="submit" class="btn btn-primary">Search</button>
            <?php if (!empty($search)): ?>
                <a href="admin_orders.php" class="btn btn-secondary">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <p><strong>Total Orders: <?php echo mysqli_num_rows($result); ?></strong></p>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Date</th>
                <th>Amount</th>
                <th>Books</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (mysqli_num_rows($result) > 0) {
                while ($order = mysqli_fetch_assoc($result)) {
                    $customerName = htmlspecialchars($order['CFName'] . ' ' . $order['CLName']);
                    $customerEmail = htmlspecialchars($order['Email']);
                    
                    echo "<tr>";
                    echo "<td>#{$order['OrderID']}</td>";
                    echo "<td>{$customerName}<br><small>{$customerEmail}</small></td>";
                    echo "<td>{$order['OrderDate']}</td>";
                    echo "<td>\${$order['TotalAmount']}</td>";
                    
                    // Get books for this order
                    $orderID = $order['OrderID'];
                    $sqlBooks = "SELECT b.Title FROM order_contents oc 
                                 JOIN BOOK b ON oc.ISBN_Order = b.ISBN 
                                 WHERE oc.O_ID = ?";
                    $stmtBooks = mysqli_prepare($conn, $sqlBooks);
                    mysqli_stmt_bind_param($stmtBooks, "i", $orderID);
                    mysqli_stmt_execute($stmtBooks);
                    $booksResult = mysqli_stmt_get_result($stmtBooks);
                    
                    $books = [];
                    while ($bookRow = mysqli_fetch_assoc($booksResult)) {
                        $books[] = htmlspecialchars($bookRow['Title']);
                    }
                    mysqli_stmt_close($stmtBooks);
                    
                    if (count($books) > 0) {
                        echo "<td>" . implode("<br>", $books) . "</td>";
                    } else {
                        echo "<td><em>No books</em></td>";
                    }
                    
                    echo "<td><span class='badge'>{$order['ShipStat']}</span></td>";
                    
                    echo "<td>
                        <a href='admin_order_details.php?id={$order['OrderID']}' class='btn btn-sm btn-info'>View</a><br>
                        <form method='POST' style='display:inline-block; margin-top: 5px;'>
                            <input type='hidden' name='order_id' value='{$order['OrderID']}'>
                            <select name='new_status' class='status-select'>
                                <option value='Processing' " . ($order['ShipStat'] == 'Processing' ? 'selected' : '') . ">Processing</option>
                                <option value='Shipped' " . ($order['ShipStat'] == 'Shipped' ? 'selected' : '') . ">Shipped</option>
                                <option value='Delivered' " . ($order['ShipStat'] == 'Delivered' ? 'selected' : '') . ">Delivered</option>
                                <option value='Cancelled' " . ($order['ShipStat'] == 'Cancelled' ? 'selected' : '') . ">Cancelled</option>
                            </select>
                            <button type='submit' name='update_status' class='btn btn-sm btn-warning'>Update</button>
                        </form>
                    </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='7' style='text-align: center;'>No orders found</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php include "admin_footer.php"; ?>
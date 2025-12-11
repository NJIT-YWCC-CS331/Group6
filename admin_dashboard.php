<?php
include "admin_header.php";
include "db.php";

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$admin_name = $_SESSION['admin_name'];
?>

<div class="container mt-5">
    <h1>Admin Dashboard</h1>
    <p>Welcome, <strong><?php echo htmlspecialchars($admin_name); ?></strong></p>

    <hr>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h3>Total Users</h3>
                    <?php
                    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM CUSTOMER");
                    $row = mysqli_fetch_assoc($result);
                    echo "<h2>" . $row['count'] . "</h2>";
                    ?>
                    <a href="admin_users.php" class="btn btn-primary">View All Users</a>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h3>Total Orders</h3>
                    <?php
                    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM orders");
                    $row = mysqli_fetch_assoc($result);
                    echo "<h2>" . $row['count'] . "</h2>";
                    ?>
                    <a href="admin_orders.php" class="btn btn-primary">View All Orders</a>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h3>Total Books</h3>
                    <?php
                    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM BOOK");
                    $row = mysqli_fetch_assoc($result);
                    echo "<h2>" . $row['count'] . "</h2>";
                    ?>
                    <a href="books.php" class="btn btn-primary">View Books</a>
                </div>
            </div>
        </div>
    </div>

    <hr>

    <h3>Recent Orders</h3>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Date</th>
                <th>Amount</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT o.OrderID, o.OrderDate, o.TotalAmount, o.ShipStat, 
                    c.CFName, c.CLName, c.Email
                    FROM orders o
                    LEFT JOIN CUSTOMER c ON o.O_CustomerID = c.CustomerID
                    ORDER BY o.OrderDate DESC
                    LIMIT 10";
            $result = mysqli_query($conn, $sql);

            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>";
                echo "<td>{$row['OrderID']}</td>";
                echo "<td>" . htmlspecialchars($row['CFName'] . ' ' . $row['CLName']) . "<br><small>" . htmlspecialchars($row['Email']) . "</small></td>";
                echo "<td>{$row['OrderDate']}</td>";
                echo "<td>\${$row['TotalAmount']}</td>";
                echo "<td><span class='badge'>{$row['ShipStat']}</span></td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php include "admin_footer.php"; ?>
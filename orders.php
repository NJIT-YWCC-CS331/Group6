<?php
session_start();
include "header.php";
include "db.php";

// User must be logged in - FIXED SESSION CHECK
if (!isset($_SESSION['userid'])) {
    echo "<div class='container mt-5'><p>You must be logged in to view your orders.</p>";
    echo "<a href='login.php' class='btn btn-primary'>Login Here</a></div>";
    include "footer.php";
    exit;
}

$customerID = $_SESSION['userid'];

// Fetch all orders for this user
$sql = "SELECT * FROM orders WHERE O_CustomerID = ? ORDER BY OrderDate DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $customerID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<div class='container mt-5'>
    <h2>My Orders</h2>
    <hr>
<?php
// Show a quick success alert if redirected after adding an order
if (isset($_GET['added'])) {
    $addedId = htmlspecialchars($_GET['added']);
    echo "<div class='alert alert-success'>Order added successfully. Order ID: $addedId</div>";
}

if (mysqli_num_rows($result) == 0) {
    echo "<p>You have no orders yet.</p>";
    echo "<a href='books.php' class='btn btn-success'>Browse Books</a>";
} else {

    while ($order = mysqli_fetch_assoc($result)) {

        $orderID = $order['OrderID'];

        echo "<div class='order-box' style='background: #f9f9f9; padding: 20px; margin-bottom: 20px; border-radius: 5px;'>";
        echo "<h4>Order ID: #{$orderID}</h4>";
        echo "<p><strong>Date:</strong> {$order['OrderDate']}</p>";
        echo "<p><strong>Status:</strong> {$order['ShipStat']}</p>";
        echo "<p><strong>Total Amount:</strong> \${$order['TotalAmount']}</p>";

        // Fetch books in this order
        $sqlBooks = "
            SELECT book.Title, book.Price, book.ISBN 
            FROM order_contents 
            JOIN book ON order_contents.ISBN_Order = book.ISBN
            WHERE order_contents.O_ID = ?
        ";

        $stmtBooks = mysqli_prepare($conn, $sqlBooks);
        mysqli_stmt_bind_param($stmtBooks, "i", $orderID);
        mysqli_stmt_execute($stmtBooks);
        $booksResult = mysqli_stmt_get_result($stmtBooks);

        echo "<strong>Books:</strong><ul>";

        while ($b = mysqli_fetch_assoc($booksResult)) {
            echo "<li>" . htmlspecialchars($b['Title']) . " (ISBN: {$b['ISBN']}) - \${$b['Price']} ";
            echo " <a class='btn btn-sm btn-primary' href='purchase.php?isbn=" . urlencode($b['ISBN']) . "'>Buy Again</a>";
            echo "</li>";
        }

        echo "</ul>";
        echo "</div>";

        mysqli_stmt_close($stmtBooks);
    }
}

mysqli_stmt_close($stmt);
?>

</div>

<?php include "footer.php"; ?>
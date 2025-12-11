<?php
include "header.php";
include "db.php";

$isbn = isset($_GET['isbn']) ? trim($_GET['isbn']) : '';
$message = "";

// Validate ISBN input
if ($isbn === '') {
    die('Missing ISBN parameter.');
}

// Fetch book details
$stmt = mysqli_prepare($conn, "SELECT * FROM book WHERE ISBN = ?");
mysqli_stmt_bind_param($stmt, 's', $isbn);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$book = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$book) {
    die('Book not found for ISBN: ' . htmlspecialchars($isbn));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Require login
    if (!isset($_SESSION['userid'])) {
        header('Location: login.php');
        exit;
    }

    $customerID = intval($_SESSION['userid']);
    $quantity = isset($_POST['quantity']) ? max(1, intval($_POST['quantity'])) : 1;
    $shipping_address = isset($_POST['shipping_address']) ? trim($_POST['shipping_address']) : '';
    $payment_method = isset($_POST['payment_method']) ? trim($_POST['payment_method']) : 'unknown';

    $unitPrice = floatval($book["Price"]);
    $totalPrice = $unitPrice * $quantity;
    $orderDate = date('Y-m-d');
    $ship = 'Processing';

    // Insert into orders (AUTO_INCREMENT OrderID)
    $sqlOrder = "INSERT INTO orders 
        (OrderDate, ShipStat, TotalAmount, O_PayID, OAdminID, O_CustomerID) 
        VALUES (?, ?, ?, NULL, NULL, ?)";

    $stmtOrder = mysqli_prepare($conn, $sqlOrder);

    mysqli_stmt_bind_param($stmtOrder, 'ssdi', 
        $orderDate, 
        $ship, 
        $totalPrice, 
        $customerID
    );

    if (mysqli_stmt_execute($stmtOrder)) {

        // Get auto-generated OrderID
        $orderID = mysqli_insert_id($conn);
        mysqli_stmt_close($stmtOrder);

        // Insert into order_contents
        $sqlContent = "INSERT INTO order_contents (O_ID, ISBN_Order) VALUES (?, ?)";
        $stmtContent = mysqli_prepare($conn, $sqlContent);
        mysqli_stmt_bind_param($stmtContent, 'is', $orderID, $isbn);

        if (mysqli_stmt_execute($stmtContent)) {
            $message = "Added to order successfully! Your Order ID is: $orderID";
            // Redirect to orders page so user can see their orders
            header('Location: orders.php?added=' . urlencode($orderID));
            exit;
        } else {
            $message = "Error inserting order contents: " . mysqli_stmt_error($stmtContent);
        }

        mysqli_stmt_close($stmtContent);

    } else {
        $message = "Error inserting order: " . mysqli_stmt_error($stmtOrder);
    }
}
?>

<div class="container mt-5">
    <h2>Add Book to Order</h2>

    <h4><?php echo $book["Title"]; ?></h4>
    <p><strong>Price:</strong> $<?php echo $book["Price"]; ?></p>
    <p><strong>Edition:</strong> <?php echo $book["B_Edition"]; ?></p>

    <?php if ($message != "") { echo "<div class='alert alert-info'>$message</div>"; } ?>

    <form method="POST">
        <button type="submit" class="btn btn-success">Confirm Add to Order</button>
    </form>
</div>

<?php include "footer.php"; ?>

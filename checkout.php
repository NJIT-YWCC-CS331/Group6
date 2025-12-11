<?php
session_start();
include "db.php";

// Must be logged in
if (!isset($_SESSION['userid'])) {
    header("Location: login.php");
    exit;
}

// Must have items in cart
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}

$customerID = $_SESSION['userid'];

// Get customer info
$stmt = mysqli_prepare($conn, "SELECT * FROM CUSTOMER WHERE CustomerID = ?");
mysqli_stmt_bind_param($stmt, "i", $customerID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$customer = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// Calculate cart totals
$cart_items = array();
$cart_total = 0;

foreach ($_SESSION['cart'] as $isbn => $item) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM BOOK WHERE ISBN = ?");
    mysqli_stmt_bind_param($stmt, "i", $isbn);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $book = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    if ($book) {
        $book['quantity'] = $item['quantity'];
        $book['subtotal'] = $book['Price'] * $item['quantity'];
        $cart_total += $book['subtotal'];
        $cart_items[] = $book;
    }
}

// Process payment
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['place_order'])) {
    $card_name = mysqli_real_escape_string($conn, $_POST['card_name']);
    $card_type = mysqli_real_escape_string($conn, $_POST['card_type']);
    // Basic sanitization: keep only digits and store last 4 digits to avoid storing full card numbers
    $raw_card_number = isset($_POST['card_number']) ? preg_replace('/\D/', '', $_POST['card_number']) : '';
    $card_number_last4 = $raw_card_number !== '' ? substr($raw_card_number, -4) : '';
    
    $orderDate = date('Y-m-d');
    $ship_status = 'Processing';
    
    // Generate unique IDs
    $payID = rand(1000, 9999);
    $orderID = rand(10000, 99999);
    $adminID = 200; // Default admin for payment processing
    
    // Check if PayID already exists
    $checkPay = mysqli_query($conn, "SELECT PayID FROM PAYMENT_RECORD WHERE PayID = $payID");
    while (mysqli_num_rows($checkPay) > 0) {
        $payID = rand(1000, 9999);
        $checkPay = mysqli_query($conn, "SELECT PayID FROM PAYMENT_RECORD WHERE PayID = $payID");
    }
    
    // Check if OrderID already exists
    $checkOrder = mysqli_query($conn, "SELECT OrderID FROM ORDERS WHERE OrderID = $orderID");
    while (mysqli_num_rows($checkOrder) > 0) {
        $orderID = rand(10000, 99999);
        $checkOrder = mysqli_query($conn, "SELECT OrderID FROM ORDERS WHERE OrderID = $orderID");
    }
    
    // Insert payment record. Store last 4 digits in `CardNum` (avoid storing full card numbers).
    $sqlPayment = "INSERT INTO PAYMENT_RECORD (PayID, Amount, CardNum, Paypal, Wallets, P_Date, PAdminID) 
                   VALUES (?, ?, ?, NULL, ?, ?, ?)";
    $stmtPayment = mysqli_prepare($conn, $sqlPayment);

    // Types: i (PayID), d (Amount), s (CardNum), s (Wallets/card type), s (P_Date), i (PAdminID)
    mysqli_stmt_bind_param($stmtPayment, 'idsssi', 
        $payID,
        $cart_total,
        $card_number_last4,
        $card_type,
        $orderDate,
        $adminID
    );
    
    if (mysqli_stmt_execute($stmtPayment)) {
        mysqli_stmt_close($stmtPayment);
        
        // Insert order with payment reference
        $sqlOrder = "INSERT INTO ORDERS (OrderID, OrderDate, ShipStat, TotalAmount, O_PayID, OAdminID, O_CustomerID) 
                     VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmtOrder = mysqli_prepare($conn, $sqlOrder);
        mysqli_stmt_bind_param($stmtOrder, 'issdiii', 
            $orderID, 
            $orderDate, 
            $ship_status, 
            $cart_total, 
            $payID, 
            $adminID, 
            $customerID
        );
        
        if (mysqli_stmt_execute($stmtOrder)) {
            mysqli_stmt_close($stmtOrder);
            
            // Insert order contents
            $sqlContent = "INSERT INTO ORDER_CONTENTS (O_ID, ISBN_Order) VALUES (?, ?)";
            $stmtContent = mysqli_prepare($conn, $sqlContent);
            
            foreach ($cart_items as $book) {
                // Insert multiple times for quantity
                for ($i = 0; $i < $book['quantity']; $i++) {
                    mysqli_stmt_bind_param($stmtContent, 'ii', $orderID, $book['ISBN']);
                    mysqli_stmt_execute($stmtContent);
                }
            }
            mysqli_stmt_close($stmtContent);
            
            // Clear cart
            unset($_SESSION['cart']);
            
            // Redirect to success page
            header("Location: order_success.php?order_id=$orderID");
            exit;
        } else {
            $error_message = "Error creating order: " . mysqli_error($conn);
        }
    } else {
        $error_message = "Error processing payment: " . mysqli_error($conn);
    }
}
?>

<?php include "header.php"; ?>

<style>
.checkout-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    margin-top: 30px;
}
.checkout-box {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}
.form-group {
    margin-bottom: 20px;
}
.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
}
.form-group input, .form-group select {
    width: 100%;
    padding: 12px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 14px;
}
.form-group input:focus, .form-group select:focus {
    outline: none;
    border-color: #667eea;
}
.summary-item {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid #f0f0f0;
}
.summary-total {
    display: flex;
    justify-content: space-between;
    padding: 20px 0 0 0;
    font-size: 20px;
    color: #333;
}
.alert {
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-weight: 500;
}
.alert-danger {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}
@media (max-width: 768px) {
    .checkout-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="container mt-5">
    <h2>Checkout</h2>
    
    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger">
            <strong>Error:</strong> <?php echo $error_message; ?>
        </div>
    <?php endif; ?>
    
    <div class="checkout-grid">
        
        <!-- Order Summary -->
        <div class="checkout-box">
            <h3>Order Summary</h3>
            <hr>
            <?php foreach ($cart_items as $book): ?>
                <div class="summary-item">
                    <div>
                        <strong><?php echo htmlspecialchars($book['Title']); ?></strong>
                        <br>
                        <small>Quantity: <?php echo $book['quantity']; ?> × $<?php echo number_format($book['Price'], 2); ?></small>
                    </div>
                    <span style="font-weight: bold;">$<?php echo number_format($book['subtotal'], 2); ?></span>
                </div>
            <?php endforeach; ?>
            
            <div class="summary-total">
                <strong>Total:</strong>
                <strong style="color: #667eea;">$<?php echo number_format($cart_total, 2); ?></strong>
            </div>
            
            <hr>
            <h4>Shipping Address</h4>
            <p style="color: #666; line-height: 1.6;">
                <?php echo htmlspecialchars($customer['CFName'] . ' ' . $customer['CLName']); ?><br>
                <?php echo htmlspecialchars($customer['StreetName']); ?><br>
                <?php echo htmlspecialchars($customer['Town'] . ', ' . $customer['Country']); ?><br>
                Zip: <?php echo htmlspecialchars($customer['ZipCode']); ?><br>
                Phone: <?php echo htmlspecialchars($customer['PhoneNum']); ?>
            </p>
        </div>
        
        <!-- Payment Form -->
        <div class="checkout-box">
            <h3>Payment Details</h3>
            <hr>
            
            <form method="POST" action="checkout.php">
                <div class="form-group">
                    <label>Cardholder Name <span style="color: red;">*</span></label>
                    <input type="text" name="card_name" required placeholder="John Doe">
                </div>
                
                <div class="form-group">
                    <label>Card Type <span style="color: red;">*</span></label>
                    <select name="card_type" required>
                        <option value="">Select Card Type</option>
                        <option value="Credit Card">Credit Card</option>
                        <option value="Debit Card">Debit Card</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Card Number <span style="color: red;">*</span></label>
                    <input type="text" name="card_number" required 
                           placeholder="1234 5678 9012 3456" 
                           maxlength="19">
                    <small style="color: #888;">For demo purposes only - not stored</small>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label>Expiry Date <span style="color: red;">*</span></label>
                        <input type="month" name="expiry_date" required>
                    </div>
                    
                    <div class="form-group">
                        <label>CVV <span style="color: red;">*</span></label>
                        <input type="text" name="cvv" required 
                               placeholder="123" 
                               maxlength="4"
                               pattern="[0-9]+"
                               title="Please enter only numbers">
                    </div>
                </div>
                
                <button type="submit" name="place_order" 
                        style="width: 100%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
                               color: white; border: none; padding: 15px; border-radius: 8px; 
                               font-size: 18px; font-weight: 600; cursor: pointer; margin-top: 10px;">
                    Place Order - $<?php echo number_format($cart_total, 2); ?>
                </button>
                
                <p style="text-align: center; margin-top: 15px; color: #666; font-size: 14px;">
                    
                </p>
            </form>
        </div>
    </div>
</div>

<?php include "footer.php"; ?>
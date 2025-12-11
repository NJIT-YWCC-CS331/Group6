<?php
session_start();
include "header.php";
include "db.php";

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Handle remove from cart
if (isset($_GET['remove'])) {
    $isbn_to_remove = $_GET['remove'];
    if (isset($_SESSION['cart'][$isbn_to_remove])) {
        unset($_SESSION['cart'][$isbn_to_remove]);
        header("Location: cart.php?removed=1");
        exit;
    }
}

// Handle update quantity
if (isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $isbn => $qty) {
        $qty = max(1, intval($qty));
        if (isset($_SESSION['cart'][$isbn])) {
            $_SESSION['cart'][$isbn]['quantity'] = $qty;
        }
    }
    header("Location: cart.php?updated=1");
    exit;
}

// Calculate totals
$cart_total = 0;
$cart_items = array();

if (!empty($_SESSION['cart'])) {
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
}
?>

<style>
.cart-item {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 20px;
    border-bottom: 2px solid #f0f0f0;
}
.cart-item:last-child {
    border-bottom: none;
}
.quantity-input {
    width: 70px;
    padding: 8px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 14px;
}
.cart-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    margin-top: 20px;
}
@media (max-width: 768px) {
    .cart-item {
        flex-direction: column;
        align-items: flex-start;
    }
    .cart-actions {
        flex-direction: column;
        gap: 20px;
    }
}
</style>

<div class="container mt-5">
    <h2>Shopping Cart</h2>
    
    <?php if (isset($_GET['removed'])): ?>
        <div class="alert alert-success">Item removed from cart successfully!</div>
    <?php endif; ?>
    
    <?php if (isset($_GET['updated'])): ?>
        <div class="alert alert-success">Cart updated successfully!</div>
    <?php endif; ?>
    
    <?php if (empty($cart_items)): ?>
        <div class="empty-cart" style="text-align: center; padding: 60px 20px;">
            <h3>Your cart is empty</h3>
            <p>Add some books to get started!</p>
            <a href="books.php" class="btn btn-primary">Browse Books</a>
        </div>
    <?php else: ?>
        <form method="POST" action="cart.php">
            <div class="cart-items" style="background: white; border-radius: 12px; padding: 20px; margin-bottom: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                <?php foreach ($cart_items as $book): ?>
                    <div class="cart-item">
                        <div style="flex: 1;">
                            <h4 style="margin: 0 0 10px 0;"><?php echo htmlspecialchars($book['Title']); ?></h4>
                            <p style="margin: 5px 0; color: #666;">ISBN: <?php echo htmlspecialchars($book['ISBN']); ?></p>
                            <p style="margin: 5px 0; color: #666;">Edition: <?php echo htmlspecialchars($book['B_Edition']); ?></p>
                            <p style="font-size: 18px; font-weight: bold; color: #667eea; margin: 10px 0;">
                                $<?php echo number_format($book['Price'], 2); ?> each
                            </p>
                        </div>
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <div>
                                <label style="display: block; margin-bottom: 5px; font-weight: 600;">Quantity:</label>
                                <input type="number" name="quantity[<?php echo $book['ISBN']; ?>]" 
                                       value="<?php echo $book['quantity']; ?>" 
                                       min="1" max="99"
                                       class="quantity-input">
                            </div>
                            <div style="text-align: right;">
                                <label style="display: block; margin-bottom: 5px; font-weight: 600;">Subtotal:</label>
                                <p style="font-size: 20px; font-weight: bold; color: #333; margin: 0;">
                                    $<?php echo number_format($book['subtotal'], 2); ?>
                                </p>
                            </div>
                        </div>
                        <a href="cart.php?remove=<?php echo $book['ISBN']; ?>" 
                           class="btn btn-danger"
                           onclick="return confirm('Remove this item from cart?');"
                           style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); 
                                  color: white; border: none; padding: 10px 20px; 
                                  border-radius: 8px; text-decoration: none; font-weight: 600;">
                            Remove
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="cart-actions">
                <div>
                    <button type="submit" name="update_cart" class="btn btn-secondary">Update Cart</button>
                    <a href="books.php" class="btn btn-primary">Continue Shopping</a>
                </div>
                <div style="text-align: right;">
                    <h3 style="margin: 0 0 15px 0;">Total: $<?php echo number_format($cart_total, 2); ?></h3>
                    <?php if (isset($_SESSION['userid'])): ?>
                        <a href="checkout.php" class="btn btn-success" 
                           style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
                                  padding: 15px 40px; font-size: 18px;">
                            Proceed to Checkout
                        </a>
                    <?php else: ?>
                        <p style="color: #666; margin-bottom: 10px;">Please login to checkout</p>
                        <a href="login.php" class="btn btn-success">Login to Checkout</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php include "footer.php"; ?>
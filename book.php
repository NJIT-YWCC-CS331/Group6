<?php
session_start();
include "header.php";
include "db.php";

$isbn = isset($_GET["isbn"]) ? intval($_GET["isbn"]) : 0;

// Handle add to cart
if (isset($_POST['add_to_cart'])) {
    $quantity = isset($_POST['quantity']) ? max(1, intval($_POST['quantity'])) : 1;
    
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }
    
    if (isset($_SESSION['cart'][$isbn])) {
        $_SESSION['cart'][$isbn]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$isbn] = array('quantity' => $quantity);
    }
    
    header("Location: cart.php");
    exit;
}

$sql = "SELECT * FROM BOOK WHERE ISBN = $isbn";
$result = mysqli_query($conn, $sql);
$book = mysqli_fetch_assoc($result);

if (!$book) {
    echo "<div class='container mt-5'><p>Book not found.</p></div>";
    include "footer.php";
    exit;
}
?>

<style>
.book-detail-container {
    background: white;
    padding: 40px;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    margin-top: 30px;
}
.book-info {
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 40px;
}
.book-image-placeholder {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    height: 400px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 24px;
    font-weight: bold;
}
.book-details h2 {
    color: #333;
    margin-bottom: 20px;
}
.book-details p {
    margin: 15px 0;
    font-size: 16px;
    color: #666;
}
.price-tag {
    font-size: 32px;
    font-weight: bold;
    color: #667eea;
    margin: 20px 0;
}
.quantity-selector {
    display: flex;
    align-items: center;
    gap: 15px;
    margin: 20px 0;
}
.quantity-input {
    width: 80px;
    padding: 10px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 16px;
    text-align: center;
}
@media (max-width: 768px) {
    .book-info {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="container mt-5">
    <a href="books.php" class="btn btn-secondary">← Back to Books</a>
    
    <div class="book-detail-container">
        <div class="book-info">
            <div class="book-image-placeholder">
                📚 <?php echo htmlspecialchars($book["Title"]); ?>
            </div>
            
            <div class="book-details">
                <h2><?php echo htmlspecialchars($book["Title"]); ?></h2>
                
                <p><strong>ISBN:</strong> <?php echo htmlspecialchars($book["ISBN"]); ?></p>
                <p><strong>Edition:</strong> <?php echo htmlspecialchars($book["B_Edition"]); ?></p>
                <p><strong>Publication Year:</strong> <?php echo htmlspecialchars($book["Publication_Year"]); ?></p>
                
                <?php if ($book["Stock_Quantity"] > 0): ?>
                    <p><strong>Stock:</strong> <span style="color: green;"><?php echo $book["Stock_Quantity"]; ?> available</span></p>
                <?php else: ?>
                    <p><strong>Stock:</strong> <span style="color: red;">Out of Stock</span></p>
                <?php endif; ?>
                
                <div class="price-tag">
                    $<?php echo number_format($book["Price"], 2); ?>
                </div>
                
                <?php if ($book["Stock_Quantity"] > 0): ?>
                    <form method="POST" action="">
                        <div class="quantity-selector">
                            <label style="font-weight: 600; font-size: 16px;">Quantity:</label>
                            <input type="number" name="quantity" value="1" min="1" max="<?php echo $book["Stock_Quantity"]; ?>" class="quantity-input">
                        </div>
                        
                        <div style="display: flex; gap: 15px; margin-top: 30px;">
                            <button type="submit" name="add_to_cart" 
                                    style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
                                           color: white; border: none; padding: 15px 40px; 
                                           border-radius: 8px; font-size: 18px; font-weight: 600; 
                                           cursor: pointer; flex: 1;">
                                Add to Cart
                            </button>
                            
                            <a href="cart.php" class="btn btn-secondary" style="padding: 15px 40px; font-size: 18px; text-decoration: none; display: inline-block;">
                                View Cart
                            </a>
                        </div>
                    </form>
                <?php else: ?>
                    <p style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-top: 20px;">
                        This book is currently out of stock. Please check back later.
                    </p>
                <?php endif; ?>
                
                <div style="margin-top: 30px; padding-top: 30px; border-top: 2px solid #f0f0f0;">
                    <h4>Book Information</h4>
                    <p style="line-height: 1.8; color: #666;">
                        This book is a great addition to your collection. 
                        Secure checkout and fast shipping available.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "footer.php"; ?>
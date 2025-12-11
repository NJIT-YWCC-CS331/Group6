<?php
session_start();
include "header.php";
include "db.php";

// Handle quick add to cart
if (isset($_POST['quick_add'])) {
    $isbn = intval($_POST['isbn']);
    
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }
    
    if (isset($_SESSION['cart'][$isbn])) {
        $_SESSION['cart'][$isbn]['quantity'] += 1;
    } else {
        $_SESSION['cart'][$isbn] = array('quantity' => 1);
    }
    
    $added_message = "Book added to cart!";
}
?>

<style>
.books-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 25px;
    margin-top: 30px;
}
.book-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    transition: transform 0.3s, box-shadow 0.3s;
}
.book-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 12px rgba(0,0,0,0.15);
}
.book-image {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    height: 200px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 16px;
    font-weight: bold;
    margin-bottom: 15px;
    text-align: center;
    padding: 10px;
}
.book-card h5 {
    color: #333;
    margin: 10px 0;
    font-size: 18px;
    min-height: 50px;
}
.book-card .price {
    font-size: 24px;
    font-weight: bold;
    color: #667eea;
    margin: 10px 0;
}
.book-actions {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}
.book-actions a,
.book-actions button {
    flex: 1;
    padding: 10px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    text-align: center;
    text-decoration: none;
    transition: transform 0.2s, opacity 0.2s;
}
.book-actions a:hover,
.book-actions button:hover {
    transform: scale(1.05);
    opacity: 0.9;
}
</style>

<div class="container mt-5">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>All Books</h2>
        <a href="cart.php" class="btn btn-success">
            🛒 Cart 
            <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                <span style="background: #f5576c; color: white; border-radius: 50%; padding: 2px 8px; font-size: 12px; margin-left: 5px;">
                    <?php echo array_sum(array_column($_SESSION['cart'], 'quantity')); ?>
                </span>
            <?php endif; ?>
        </a>
    </div>

    <?php if (isset($added_message)): ?>
        <div class="alert alert-success"><?php echo $added_message; ?> <a href="cart.php">View Cart</a></div>
    <?php endif; ?>

    <form method="GET" class="mb-4">
        <div style="display: flex; gap: 10px;">
            <input type="text" name="search" class="form-control" placeholder="Search by title" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" style="flex: 1;">
            <button type="submit" class="btn btn-primary">Search</button>
            <?php if (isset($_GET['search'])): ?>
                <a href="books.php" class="btn btn-secondary">Clear</a>
            <?php endif; ?>
        </div>
    </form>

    <div class="books-grid">
        <?php
        $search = "";
        if (isset($_GET["search"]) && !empty($_GET["search"])) {
            $search = mysqli_real_escape_string($conn, $_GET["search"]);
            $sql = "SELECT * FROM BOOK WHERE Title LIKE '%$search%' ORDER BY Title";
        } else {
            $sql = "SELECT * FROM BOOK ORDER BY Publication_Year DESC";
        }

        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $out_of_stock = ($row['Stock_Quantity'] <= 0);
                echo "
                <div class='book-card'>
                    <div class='book-image'>📚 " . htmlspecialchars($row['Title']) . "</div>
                    <h5>" . htmlspecialchars($row['Title']) . "</h5>
                    <p style='color: #666; margin: 5px 0;'>Edition: " . htmlspecialchars($row['B_Edition']) . "</p>
                    <p style='color: #666; margin: 5px 0;'>Year: " . htmlspecialchars($row['Publication_Year']) . "</p>
                    <p class='price'>$" . number_format($row['Price'], 2) . "</p>";
                    
                if ($out_of_stock) {
                    echo "<p style='color: red; font-weight: 600;'>Out of Stock</p>";
                } else {
                    echo "<p style='color: green; font-size: 14px;'>" . $row['Stock_Quantity'] . " in stock</p>";
                }
                
                echo "<div class='book-actions'>";
                echo "<a href='book.php?isbn={$row['ISBN']}' class='btn btn-primary' style='background: #667eea;'>View Details</a>";
                
                if (!$out_of_stock) {
                    echo "<form method='POST' style='flex: 1; margin: 0;'>
                            <input type='hidden' name='isbn' value='{$row['ISBN']}'>
                            <button type='submit' name='quick_add' class='btn btn-success' style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); width: 100%;'>Add to Cart</button>
                          </form>";
                }
                
                echo "</div>";
                echo "</div>";
            }
        } else {
            echo "<p style='grid-column: 1 / -1; text-align: center; padding: 40px; color: #666;'>No books found.</p>";
        }
        ?>
    </div>
</div>

<?php include "footer.php"; ?>
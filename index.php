<?php 
session_start();
include "header.php"; 
include "db.php";
?>

<div class="container mt-5">
    <h1>Welcome to Online Fancy Book Store</h1>
    
    <?php if(isset($_SESSION['userid'])): ?>
        <p>Hello! Welcome back to our bookstore.</p>
    <?php else: ?>
        <p>Please <a href="login.php">login</a> or <a href="register.php">register</a> to start shopping.</p>
    <?php endif; ?>

    <hr>

    <h2>Featured Books</h2>
    <div class="row">
        <?php
        // Get latest books
        $sql = "SELECT * FROM BOOK ORDER BY Publication_Year DESC LIMIT 6";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                echo "
                <div class='col-md-4 mb-4'>
                    <div class='card'>
                        <div class='card-body'>
                            <h5 class='card-title'>" . htmlspecialchars($row['Title']) . "</h5>
                            <p class='card-text'>Price: \$" . htmlspecialchars($row['Price']) . "</p>
                            <p class='card-text'>Year: " . htmlspecialchars($row['Publication_Year']) . "</p>
                            <a href='book.php?isbn={$row['ISBN']}' class='btn btn-primary'>View Details</a>
                        </div>
                    </div>
                </div>";
            }
        } else {
            echo "<p>No books available at the moment.</p>";
        }
        ?>
    </div>

    <div style="text-align: center; margin-top: 30px;">
        <a href="books.php" class="btn btn-success">Browse All Books</a>
       
    </div>

    <hr>

    <div style="margin-top: 40px;">
        <h3>About Our Bookstore</h3>
        <p>Online Fancy Book Store offers a wide selection of books across various genres. Browse our collection, read reviews, and purchase your favorite books online with ease.</p>
        
        <h4>Features:</h4>
        <ul>
            <li>Wide selection of books</li>
            <li>Easy search and filtering</li>
            <li>Secure online ordering</li>
            <li>Order tracking</li>
            <li>Customer reviews</li>
        </ul>
    </div>
</div>

<?php include "footer.php"; ?>
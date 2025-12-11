<?php if (session_status() === PHP_SESSION_NONE) { session_start(); } ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Online Book Store</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .cart-badge {
            background: #f5576c;
            color: white;
            border-radius: 50%;
            padding: 2px 8px;
            font-size: 12px;
            margin-left: 5px;
        }
    </style>
</head>
<body class="theme-light">

<nav class="bg-primary">
    <div class="nav-left">
        <a href="index.php">Home</a>
        <a href="books.php">Books</a>
        <a href="cart.php">
            🛒 Cart
            <?php 
            if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
                $cart_count = array_sum(array_column($_SESSION['cart'], 'quantity'));
                if ($cart_count > 0) {
                    echo "<span class='cart-badge'>$cart_count</span>";
                }
            }
            ?>
        </a>
    </div>
    <div class="nav-right">
        <?php if(isset($_SESSION['userid'])): ?>
            <a href="profile.php">Profile</a>
            <a href="orders.php">My Orders</a>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
        <a href="admin_login.php">Admin Login</a>
        <button onclick="toggleTheme()" class="btn btn-sm btn-secondary" aria-label="Toggle theme">Toggle</button>
    </div>
</nav>

<hr>
<?php if (session_status() === PHP_SESSION_NONE) { session_start(); } ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Admin Panel - Online Book Store</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="theme-light">

<nav class="bg-primary">
    <div class="nav-left">
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="admin_users.php">Users</a>
        <a href="admin_orders.php">Orders</a>
    </div>
    <div class="nav-right">
        <span style="padding: 10px; color: #fff;">
            Admin: <?php echo isset($_SESSION['admin_name']) ? htmlspecialchars($_SESSION['admin_name']) : 'Admin'; ?>
        </span>
        <a href="admin_logout.php">Logout</a>
        <button onclick="toggleTheme()" class="btn btn-sm btn-secondary" aria-label="Toggle theme">Toggle</button>
    </div>
</nav>

<hr>
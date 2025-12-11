<?php
session_start();
include("db.php");

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Query admin table with correct column names
    $sql = "SELECT * FROM ADMINISTRATOR WHERE Username = ? AND Adm_Password = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $username, $password);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($admin = mysqli_fetch_assoc($result)) {
        $_SESSION['admin_id'] = $admin['AdminID'];
        $_SESSION['admin_username'] = $admin['Username'];
        $_SESSION['admin_name'] = $admin['AdmFName'] . ' ' . $admin['AdmLName'];
        header("Location: admin_dashboard.php");
        exit;
    } else {
        $message = "Invalid admin credentials.";
    }
    mysqli_stmt_close($stmt);
}
?>

<?php if (session_status() === PHP_SESSION_NONE) { session_start(); } ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Admin Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="theme-light">

<nav class="bg-primary">
    <div class="nav-left">
        <a href="index.php">Home</a>
    </div>
    <div class="nav-right">
        <a href="login.php">User Login</a>
    </div>
</nav>

<hr>

<div class="container mt-5" style="max-width:400px; margin: 50px auto;">
    <h2>Admin Login</h2>

    <?php if ($message != "") echo "<div class='alert alert-danger'>$message</div>"; ?>

    <form method="POST">
        <label>Username:</label>
        <input type="text" name="username" class="form-control" required><br>

        <label>Password:</label>
        <input type="password" name="password" class="form-control" required><br>

        <button type="submit" class="btn btn-primary w-100">Login as Admin</button>
    </form>
    
    <p style="margin-top: 20px;">
        <a href="index.php">Back to Home</a>
    </p>
</div>

<hr>
<footer class="bg-primary">
    <p>Online Fancy Book Store - Admin Panel</p>
</footer>

<!-- Theme toggle script -->
<script>
  (function(){
    const body = document.body;
    const key = 'site-theme';
    if (localStorage.getItem(key) === 'dark') body.classList.add('theme-dark');
    window.toggleTheme = function(){
      const isDark = body.classList.toggle('theme-dark');
      localStorage.setItem(key, isDark ? 'dark' : 'light');
    };
  })();
</script>

</body>
</html>
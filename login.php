<?php
session_start();
include("header.php");
include("db.php");

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Prepared statement
    $sql = "SELECT CustomerID, Password FROM CUSTOMER WHERE Email = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {

        if ($password === $row['Password']) {
            $_SESSION['userid'] = $row['CustomerID'];
            header("Location: index.php");
            exit;
        } else {
            $message = "Incorrect password.";
        }

    } else {
        $message = "Email not found.";
    }
}
?>

<div class="container mt-4">
    <h2>Login</h2>

    <?php if ($message != "") echo "<div class='alert alert-danger'>$message</div>"; ?>

    <form method="POST">
        <label>Email:</label>
        <input type="email" name="email" required class="form-control">

        <label>Password:</label>
        <input type="password" name="password" required class="form-control">

        <button type="submit" class="btn btn-primary mt-3">Login</button>
    </form>
</div>

<?php include("footer.php"); ?>

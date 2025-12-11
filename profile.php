<?php
session_start();
include "header.php";
include "db.php";

// FIXED SESSION CHECK
if (!isset($_SESSION['userid'])) {
    echo "<div class='container mt-5'><p>You must be logged in to view your profile.</p>";
    echo "<a href='login.php' class='btn btn-primary'>Login Here</a></div>";
    include "footer.php";
    exit();
}

$customerID = $_SESSION['userid'];

// Fetch user data using CustomerID
$sql = "SELECT * FROM CUSTOMER WHERE CustomerID = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $customerID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$user) {
    echo "<div class='container mt-5'><p>User not found.</p></div>";
    include "footer.php";
    exit();
}
?>

<div class="container mt-5">
    <h2>Your Profile</h2>
    <hr>

    <div style="background: #f9f9f9; padding: 20px; border-radius: 5px;">
        <p><b>Customer ID:</b> <?php echo $user["CustomerID"]; ?></p>
        <p><b>Email:</b> <?php echo htmlspecialchars($user["Email"]); ?></p>
        <p><b>Name:</b> <?php echo htmlspecialchars($user["CFName"] . " " . $user["CMName"] . " " . $user["CLName"]); ?></p>
        <p><b>Phone:</b> <?php echo htmlspecialchars($user["PhoneNum"]); ?></p>
        <p><b>Address:</b> <?php echo htmlspecialchars($user["StreetName"] . ", " . $user["Town"] . ", " . $user["Country"]); ?></p>
        <p><b>Zip Code:</b> <?php echo htmlspecialchars($user["ZipCode"]); ?></p>
    </div>

    <div style="margin-top: 20px;">
        <a href="orders.php" class="btn btn-primary">View My Orders</a>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>
</div>

<?php include "footer.php"; ?>
<?php
include "admin_header.php";
include("db.php");

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

// Handle search - NO CUST_EMAIL table, email is directly in CUSTOMER
$search = "";
$sql = "SELECT * FROM CUSTOMER";

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $sql .= " WHERE Email LIKE '%$search%' OR CFName LIKE '%$search%' OR CLName LIKE '%$search%' OR PhoneNum LIKE '%$search%'";
}

$sql .= " ORDER BY CustomerID DESC";
$result = mysqli_query($conn, $sql);
?>

<div class="container mt-5">
    <h2>User List</h2>
    
    <div class="search-box">
        <form method="GET" style="display: flex; gap: 10px;">
            <input type="text" name="search" class="form-control" placeholder="Search by name, email, or phone" value="<?php echo htmlspecialchars($search); ?>" style="flex: 1;">
            <button type="submit" class="btn btn-primary">Search</button>
            <?php if (!empty($search)): ?>
                <a href="admin_users.php" class="btn btn-secondary">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <p><strong>Total Users: <?php echo mysqli_num_rows($result); ?></strong></p>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Address</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (mysqli_num_rows($result) > 0) {
                while ($user = mysqli_fetch_assoc($result)) {
                    $fullName = htmlspecialchars($user['CFName'] . ' ' . ($user['CMName'] ? $user['CMName'] . ' ' : '') . $user['CLName']);
                    $address = htmlspecialchars($user['StreetName'] . ', ' . $user['Town'] . ', ' . $user['Country']);
                    
                    echo "<tr>";
                    echo "<td>{$user['CustomerID']}</td>";
                    echo "<td>{$fullName}</td>";
                    echo "<td>" . htmlspecialchars($user['Email']) . "</td>";
                    echo "<td>" . htmlspecialchars($user['PhoneNum']) . "</td>";
                    echo "<td>{$address}</td>";
                    echo "<td><a href='admin_user_details.php?id={$user['CustomerID']}' class='btn btn-sm btn-info'>View Details</a></td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='6' style='text-align: center;'>No users found</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php include "admin_footer.php"; ?>
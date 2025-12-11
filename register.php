<?php
session_start();
include "header.php";
include "db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, trim($_POST["email"]));
    $password = trim($_POST["password"]);
    $confirm_password = trim($_POST["confirm_password"]);
    $fname = mysqli_real_escape_string($conn, trim($_POST["fname"]));
    $mname = mysqli_real_escape_string($conn, trim($_POST["mname"]));
    $lname = mysqli_real_escape_string($conn, trim($_POST["lname"]));
    $phone = mysqli_real_escape_string($conn, trim($_POST["phone"]));
    $zip = mysqli_real_escape_string($conn, trim($_POST["zip"]));
    $street = mysqli_real_escape_string($conn, trim($_POST["street"]));
    $town = mysqli_real_escape_string($conn, trim($_POST["town"]));
    $country = mysqli_real_escape_string($conn, trim($_POST["country"]));

    // Validation
    if (empty($email) || empty($password) || empty($fname) || empty($lname)) {
        $message = "Please fill in all required fields.";
    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match!";
    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters long.";
    } else {
        // Check if email already exists
        $checkEmail = "SELECT * FROM CUSTOMER WHERE Email = ?";
        $stmt = mysqli_prepare($conn, $checkEmail);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) > 0) {
            $message = "Email already registered. Please login or use a different email.";
            mysqli_stmt_close($stmt);
        } else {
            mysqli_stmt_close($stmt);
            
            // Insert new user with password
            $sql = "INSERT INTO CUSTOMER (Email, Password, CFName, CMName, CLName, PhoneNum, ZipCode, StreetName, Town, Country)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssssssssss", 
                $email, $password, $fname, $mname, $lname, $phone, $zip, $street, $town, $country);
            
            if (mysqli_stmt_execute($stmt)) {
                $message = "Registration Successful! You can now login.";
                // Optionally auto-login the user
                // $_SESSION['userid'] = mysqli_insert_id($conn);
                // header("Location: index.php");
                // exit;
            } else {
                $message = "Error: " . mysqli_stmt_error($stmt);
            }
            mysqli_stmt_close($stmt);
        }
    }
}
?>

<div class="container mt-5">
    <h2>User Registration</h2>

    <?php if ($message != "") { 
        $alertClass = (strpos($message, 'Successful') !== false) ? 'alert-success' : 'alert-danger';
        echo "<div class='alert $alertClass'>$message</div>"; 
    } ?>

    <form method="POST">
        <label>Email: <span style="color: red;">*</span></label>
        <input type="email" name="email" class="form-control" placeholder="Email" required><br>

        <label>Password: <span style="color: red;">*</span></label>
        <input type="password" name="password" class="form-control" placeholder="Password (min 6 characters)" required><br>

        <label>Confirm Password: <span style="color: red;">*</span></label>
        <input type="password" name="confirm_password" class="form-control" placeholder="Confirm Password" required><br>

        <label>First Name: <span style="color: red;">*</span></label>
        <input type="text" name="fname" class="form-control" placeholder="First Name" required><br>

        <label>Middle Name:</label>
        <input type="text" name="mname" class="form-control" placeholder="Middle Name (optional)"><br>

        <label>Last Name: <span style="color: red;">*</span></label>
        <input type="text" name="lname" class="form-control" placeholder="Last Name" required><br>

        <label>Phone:</label>
        <input type="text" name="phone" class="form-control" placeholder="Phone Number"><br>

        <label>Zip Code:</label>
        <input type="text" name="zip" class="form-control" placeholder="Zip Code"><br>

        <label>Street Address:</label>
        <input type="text" name="street" class="form-control" placeholder="Street"><br>

        <label>Town/City:</label>
        <input type="text" name="town" class="form-control" placeholder="Town"><br>

        <label>Country:</label>
        <input type="text" name="country" class="form-control" placeholder="Country"><br>
        
        <button type="submit" class="btn btn-primary">Register</button>
        <a href="login.php" class="btn btn-secondary">Already have an account? Login</a>
    </form>
</div>

<?php include "footer.php"; ?>
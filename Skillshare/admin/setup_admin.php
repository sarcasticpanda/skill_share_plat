<?php
// This script creates a default admin user
// Run this once to set up the admin account

require_once('../includes/db.php');

// Admin credentials
$admin_name = "Admin";
$admin_email = "admin@skillshare.com";
$admin_password = "admin123"; // Change this after first login

// Check if admin already exists
$check_stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? AND role = 'admin'");
mysqli_stmt_bind_param($check_stmt, "s", $admin_email);
mysqli_stmt_execute($check_stmt);
$result = mysqli_stmt_get_result($check_stmt);

if (mysqli_num_rows($result) > 0) {
    echo "Admin user already exists!<br>";
    echo "Email: $admin_email<br>";
    echo "You can login with the existing credentials.";
} else {
    // Create admin user
    $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
    $stmt = mysqli_prepare($conn, "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'admin')");
    mysqli_stmt_bind_param($stmt, "sss", $admin_name, $admin_email, $hashed_password);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<h2>Admin user created successfully!</h2>";
        echo "<p><strong>Login Details:</strong></p>";
        echo "<p>Email: <strong>$admin_email</strong></p>";
        echo "<p>Password: <strong>$admin_password</strong></p>";
        echo "<p><a href='admin_login.php'>Login to Admin Panel</a></p>";
        echo "<p style='color: red;'><strong>Important:</strong> Change the default password after first login!</p>";
    } else {
        echo "Error creating admin user: " . mysqli_error($conn);
    }
}

// Also update existing user with email 'admin@email.com' to admin role if exists
$update_stmt = mysqli_prepare($conn, "UPDATE users SET role = 'admin' WHERE email = 'admin@email.com'");
mysqli_stmt_execute($update_stmt);

if (mysqli_affected_rows($conn) > 0) {
    echo "<br><p>Also updated existing user with email 'admin@email.com' to admin role.</p>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Setup Admin</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 50px; }
        a { color: #667eea; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <h3>Admin Setup Complete</h3>
    <p><a href="admin_login.php">Go to Admin Login</a></p>
    <p><a href="../pages/index.php">Go to Main Site</a></p>
</body>
</html>

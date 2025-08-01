<?php
// Test database connection
require_once('../includes/db.php');

echo "<h2>Database Connection Test</h2>";

if ($conn) {
    echo "<p style='color: green;'>✅ Database connection successful!</p>";
    
    // Test query
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM users");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        echo "<p>✅ Database query successful! Users count: " . $row['count'] . "</p>";
    } else {
        echo "<p style='color: red;'>❌ Database query failed: " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Database connection failed: " . mysqli_connect_error() . "</p>";
}

// Show database details
echo "<h3>Database Details:</h3>";
echo "<p>Host: " . $host . "</p>";
echo "<p>Database: " . $database . "</p>";
echo "<p>Username: " . $user . "</p>";
?> 
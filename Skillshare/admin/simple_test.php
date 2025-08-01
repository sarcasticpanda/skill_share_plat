<?php
echo "Step 1: Basic PHP is working<br>";

// Test database connection
echo "Step 2: Testing database connection<br>";

try {
    $host = "sql207.infinityfree.com";
    $user = "if0_39611321";
    $password = "WKiIQAMOxsFTv";
    $database = "if0_39611321_skillshare";
    
    $conn = mysqli_connect($host, $user, $password, $database);
    
    if ($conn) {
        echo "✅ Database connection successful!<br>";
        
        // Test query
        $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM users");
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            echo "✅ Database query successful! Users count: " . $row['count'] . "<br>";
        } else {
            echo "❌ Database query failed: " . mysqli_error($conn) . "<br>";
        }
    } else {
        echo "❌ Database connection failed: " . mysqli_connect_error() . "<br>";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "Step 3: Test completed<br>";
?> 
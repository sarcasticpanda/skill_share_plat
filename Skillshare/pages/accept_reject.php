<?php
session_start();
require_once('../includes/db.php');

// Verify user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Ensure we have the message ID and action
if (!isset($_POST['message_id']) || !isset($_POST['action'])) {
    $_SESSION['error'] = "Missing required information.";
    header("Location: inbox.php");
    exit();
}

$message_id = (int)$_POST['message_id'];
$action = $_POST['action'];
$user_id = $_SESSION['user_id'];

// Verify that this message belongs to the current user
$verify_sql = "SELECT from_id, to_id FROM messages WHERE id = ? AND to_id = ?";
$verify_stmt = mysqli_prepare($conn, $verify_sql);
mysqli_stmt_bind_param($verify_stmt, "ii", $message_id, $user_id);
mysqli_stmt_execute($verify_stmt);
mysqli_stmt_store_result($verify_stmt);

if (mysqli_stmt_num_rows($verify_stmt) === 0) {
    $_SESSION['error'] = "You don't have permission to perform this action.";
    header("Location: inbox.php");
    exit();
}

// Close the verification statement
mysqli_stmt_close($verify_stmt);

// Update the message status based on the action
if ($action === 'accept' || $action === 'reject') {
    $status = ($action === 'accept') ? 'accepted' : 'rejected';
    
    $update_sql = "UPDATE messages SET status = ? WHERE id = ?";
    $update_stmt = mysqli_prepare($conn, $update_sql);
    mysqli_stmt_bind_param($update_stmt, "si", $status, $message_id);
    
    if (mysqli_stmt_execute($update_stmt)) {
        // If accepting, create a connection in the connections table (if it exists)
        if ($action === 'accept') {
            // Get the from_id from the message
            $get_sender_sql = "SELECT from_id FROM messages WHERE id = ?";
            $sender_stmt = mysqli_prepare($conn, $get_sender_sql);
            mysqli_stmt_bind_param($sender_stmt, "i", $message_id);
            mysqli_stmt_execute($sender_stmt);
            $sender_result = mysqli_stmt_get_result($sender_stmt);
            $sender_row = mysqli_fetch_assoc($sender_result);
            $from_id = $sender_row['from_id'];
            
            // Try to insert into connections table if it exists
            $check_table_sql = "SHOW TABLES LIKE 'connections'";
            $check_table_result = mysqli_query($conn, $check_table_sql);
            
            if (mysqli_num_rows($check_table_result) > 0) {
                // Table exists, so insert connection records for both users
                $connection_sql = "INSERT INTO connections (user_id, connection_id, connected_at) 
                                  VALUES (?, ?, NOW()), (?, ?, NOW())";
                $connection_stmt = mysqli_prepare($conn, $connection_sql);
                mysqli_stmt_bind_param($connection_stmt, "iiii", $user_id, $from_id, $from_id, $user_id);
                mysqli_stmt_execute($connection_stmt);
            }
        }
        
        $_SESSION['success'] = "Request " . ucfirst($action) . "ed successfully.";
        header("Location: inbox.php");
        exit();
    } else {
        $_SESSION['error'] = "Failed to update request status.";
        header("Location: inbox.php");
        exit();
    }
} else {
    $_SESSION['error'] = "Invalid action.";
    header("Location: inbox.php");
    exit();
}
?> 
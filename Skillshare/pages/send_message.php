<?php
// No HTML output before JSON response
session_start();
require_once('../includes/db.php');
// Don't include header.php or any files that output HTML

// Initialize response array
$response = ['success' => false, 'message' => ''];

// Verify user is logged in
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'You must be logged in to send messages.';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Process the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['receiver_id'], $_POST['message'])) {
    $sender_id = $_SESSION['user_id'];
    $receiver_id = intval($_POST['receiver_id']);
    $message = trim($_POST['message']);
    
    // Validate message is not empty
    if (empty($message)) {
        $response['message'] = 'Message cannot be empty.';
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }
    
    // Verify these users are connected
    $check_sql = "
        SELECT COUNT(*) AS connected
        FROM messages 
        WHERE ((from_id = ? AND to_id = ?) OR (from_id = ? AND to_id = ?))
          AND status = 'accepted'
    ";
    $check_stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "iiii", $sender_id, $receiver_id, $receiver_id, $sender_id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    $check_row = mysqli_fetch_assoc($check_result);
    
    if ($check_row['connected'] == 0) {
        $response['message'] = 'You can only message users you are connected with.';
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }
    
    // Insert the message
    $sql = "INSERT INTO chat_messages (sender_id, receiver_id, message) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iis", $sender_id, $receiver_id, $message);
    
    if (mysqli_stmt_execute($stmt)) {
        $response['success'] = true;
        $response['message'] = 'Message sent successfully.';
        $response['message_id'] = mysqli_insert_id($conn);
        
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    } else {
        $response['message'] = 'Failed to send message: ' . mysqli_error($conn);
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }
}

// If we get here, something went wrong
$response['message'] = 'Invalid request.';
header('Content-Type: application/json');
echo json_encode($response);
exit();
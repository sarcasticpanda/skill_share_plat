<?php
// No HTML output before JSON response
session_start();
require_once('../includes/db.php');
// Don't include header.php or any files that output HTML

// Initialize response
$response = ['success' => false, 'messages' => []];

// Verify user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Get parameters
$user_id = $_SESSION['user_id'];
$partner_id = isset($_GET['user']) ? intval($_GET['user']) : 0;
$last_id = isset($_GET['last']) ? intval($_GET['last']) : 0;

// Debug info - comment out in production
// $response['debug'] = ['user_id' => $user_id, 'partner_id' => $partner_id, 'last_id' => $last_id];

// Verify these users are connected
$check_sql = "
    SELECT COUNT(*) AS connected
    FROM messages 
    WHERE ((from_id = ? AND to_id = ?) OR (from_id = ? AND to_id = ?))
      AND status = 'accepted'
";
$check_stmt = mysqli_prepare($conn, $check_sql);
mysqli_stmt_bind_param($check_stmt, "iiii", $user_id, $partner_id, $partner_id, $user_id);
mysqli_stmt_execute($check_stmt);
$check_result = mysqli_stmt_get_result($check_stmt);
$check_row = mysqli_fetch_assoc($check_result);

if ($check_row['connected'] == 0) {
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Get new messages
$messages_sql = "
    SELECT * FROM chat_messages 
    WHERE sender_id = ? AND receiver_id = ? AND id > ?
    ORDER BY timestamp ASC
";
$messages_stmt = mysqli_prepare($conn, $messages_sql);
mysqli_stmt_bind_param($messages_stmt, "iii", $partner_id, $user_id, $last_id);
mysqli_stmt_execute($messages_stmt);
$messages_result = mysqli_stmt_get_result($messages_stmt);

// Format messages
while ($row = mysqli_fetch_assoc($messages_result)) {
    $response['messages'][] = [
        'id' => $row['id'],
        'message' => htmlspecialchars($row['message']),
        'time' => date('M d, g:i a', strtotime($row['timestamp']))
    ];
}

// Mark messages as read
if (count($response['messages']) > 0) {
    $update_sql = "UPDATE chat_messages SET is_read = TRUE 
                  WHERE sender_id = ? AND receiver_id = ? AND is_read = FALSE";
    $update_stmt = mysqli_prepare($conn, $update_sql);
    mysqli_stmt_bind_param($update_stmt, "ii", $partner_id, $user_id);
    mysqli_stmt_execute($update_stmt);
    
    $response['success'] = true;
}

// Return response
header('Content-Type: application/json');
echo json_encode($response);
exit();
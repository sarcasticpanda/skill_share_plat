<?php
session_start();
require_once('../includes/db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../pages/login.php");
    exit();
}

$from_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['to_id'])) {
    $to_id = intval($_POST['to_id']);

    if ($from_id == $to_id) {
        $_SESSION['error'] = "You cannot connect with yourself!";
        header("Location: search.php");
        exit();
    }

    // Check if request already exists
    $check = "SELECT id FROM messages WHERE from_id = ? AND to_id = ?";
    $stmt = mysqli_prepare($conn, $check);
    mysqli_stmt_bind_param($stmt, "ii", $from_id, $to_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        $_SESSION['error'] = "Request already sent!";
        header("Location: search.php");
        exit();
    }

    // Insert new request
    $sql = "INSERT INTO messages (from_id, to_id, status) VALUES (?, ?, 'pending')";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $from_id, $to_id);
    mysqli_stmt_execute($stmt);

    $_SESSION['success'] = "Connection request sent!";
    header("Location: inbox.php");
    exit();
}
?>

<?php

function getUserName($conn, $user_id) {
    $query = "SELECT name FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $name);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
    return $name;
}

function limitText($text, $limit = 100) {
    return (strlen($text) > $limit) ? substr($text, 0, $limit) . '...' : $text;
}
?>

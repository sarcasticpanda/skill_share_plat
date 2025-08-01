<?php
require_once('../includes/auth.php');
require_once('../includes/db.php');
require_once('../includes/header.php');

// Debug: Start session (if not already started)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verify user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'Unknown';

// ===========================================
// 📥 FETCH RECEIVED CONNECTION REQUESTS
// ===========================================
$received_sql = "
    SELECT 
        m.id,
        m.from_id,
        m.message,
        m.status,
        m.timestamp,
        u.name AS sender_name
    FROM 
        messages m
    JOIN 
        users u ON m.from_id = u.id
    WHERE 
        m.to_id = ?
        AND m.status = 'pending'
    ORDER BY 
        m.timestamp DESC
";

$received_stmt = mysqli_prepare($conn, $received_sql);
if (!$received_stmt) {
    die("Error preparing received connections query: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($received_stmt, "i", $user_id);
if (!mysqli_stmt_execute($received_stmt)) {
    die("Error executing received connections query: " . mysqli_stmt_error($received_stmt));
}

$received_result = mysqli_stmt_get_result($received_stmt);
if (!$received_result) {
    die("Error fetching received connections: " . mysqli_error($conn));
}

// ===========================================
// 📤 FETCH SENT CONNECTION REQUESTS
// ===========================================
$sent_sql = "
    SELECT 
        m.id,
        m.to_id,
        m.message,
        m.status,
        m.timestamp,
        u.name AS receiver_name
    FROM 
        messages m
    JOIN 
        users u ON m.to_id = u.id
    WHERE 
        m.from_id = ?
    ORDER BY 
        m.timestamp DESC
";

$sent_stmt = mysqli_prepare($conn, $sent_sql);
if (!$sent_stmt) {
    die("Error preparing sent connections query: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($sent_stmt, "i", $user_id);
if (!mysqli_stmt_execute($sent_stmt)) {
    die("Error executing sent connections query: " . mysqli_stmt_error($sent_stmt));
}

$sent_result = mysqli_stmt_get_result($sent_stmt);
if (!$sent_result) {
    die("Error fetching sent connections: " . mysqli_error($conn));
}
?>

<link rel="stylesheet" href="../assets/css/skills.css">

<div class="page-container">
    <h2 class="page-title">📥 Connection Requests</h2>

    <!-- Display success and error messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="success">
            <?php 
            echo htmlspecialchars($_SESSION['success']); 
            unset($_SESSION['success']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="error">
            <?php 
            echo htmlspecialchars($_SESSION['error']); 
            unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>

    <!-- RECEIVED CONNECTION REQUESTS -->
    <h3>🔔 Received Requests</h3>
    <?php if (mysqli_num_rows($received_result) > 0): ?>
        <ul class="message-list">
            <?php while ($row = mysqli_fetch_assoc($received_result)): ?>
                <li class="message-item">
                    <strong><?php echo htmlspecialchars($row['sender_name']); ?></strong> 
                    wants to connect with you.
                    <?php if (!empty($row['message'])): ?>
                        <div class="message-text"><?php echo htmlspecialchars($row['message']); ?></div>
                    <?php endif; ?>
                    <small class="message-timestamp">Received on <?php echo date("M d, Y h:i A", strtotime($row['timestamp'])); ?></small>

                    <form method="POST" action="accept_reject.php" class="message-actions">
                        <input type="hidden" name="message_id" value="<?php echo $row['id']; ?>">
                        <button type="submit" name="action" value="accept" class="action-button accept-button">✅ Accept</button>
                        <button type="submit" name="action" value="reject" class="action-button reject-button">❌ Reject</button>
                    </form>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <div class="no-messages">
            <p>You don't have any pending connection requests.</p>
        </div>
    <?php endif; ?>

    <hr class="divider">

    <!-- SENT CONNECTION REQUESTS -->
    <h3>📤 Sent Requests</h3>
    <?php if (mysqli_num_rows($sent_result) > 0): ?>
        <ul class="message-list">
            <?php while ($row = mysqli_fetch_assoc($sent_result)): ?>
                <li class="message-item">
                    You sent a request to <strong><?php echo htmlspecialchars($row['receiver_name']); ?></strong>
                    <br>
                    Status: 
                    <span class="status status-<?php echo strtolower($row['status']); ?>">
                        <?php echo ucfirst($row['status']); ?>
                    </span>
                    <?php if (!empty($row['message'])): ?>
                        <div class="message-text"><?php echo htmlspecialchars($row['message']); ?></div>
                    <?php endif; ?>
                    <small class="message-timestamp">Sent on <?php echo date("M d, Y h:i A", strtotime($row['timestamp'])); ?></small>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <div class="no-messages">
            <p>You haven't sent any connection requests yet.</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once('../includes/footer.php'); ?>


<?php
require_once('admin_auth.php');
require_once('../includes/db.php');
requireAdmin();

// Handle message actions
if (isset($_GET['action']) && isset($_GET['message_id'])) {
    $message_id = intval($_GET['message_id']);
    $action = $_GET['action'];
    
    if ($action == 'delete') {
        $stmt = mysqli_prepare($conn, "DELETE FROM chat_messages WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $message_id);
        if (mysqli_stmt_execute($stmt)) {
            $success_msg = "Message deleted successfully!";
        } else {
            $error_msg = "Error deleting message!";
        }
    }
}

// Handle search and filters
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$user_filter = isset($_GET['user']) ? intval($_GET['user']) : 0;

// Build query with filters
$where_conditions = [];
$params = [];
$types = "";

if (!empty($search)) {
    $where_conditions[] = "(cm.message LIKE ? OR u1.name LIKE ? OR u2.name LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "sss";
}

if ($user_filter > 0) {
    $where_conditions[] = "(cm.sender_id = ? OR cm.receiver_id = ?)";
    $params[] = $user_filter;
    $params[] = $user_filter;
    $types .= "ii";
}

$where_clause = "";
if (!empty($where_conditions)) {
    $where_clause = "WHERE " . implode(" AND ", $where_conditions);
}

$sql = "SELECT cm.*, 
               u1.name as sender_name, u1.email as sender_email,
               u2.name as receiver_name, u2.email as receiver_email
        FROM chat_messages cm
        JOIN users u1 ON cm.sender_id = u1.id
        JOIN users u2 ON cm.receiver_id = u2.id
        $where_clause
        ORDER BY cm.timestamp DESC
        LIMIT 100";

$stmt = mysqli_prepare($conn, $sql);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Get users for filter dropdown
$users_result = mysqli_query($conn, "SELECT id, name FROM users WHERE role = 'user' ORDER BY name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Messages - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <div class="admin-sidebar">
            <div class="admin-logo">
                <h2><i class="fas fa-graduation-cap"></i> Skillshare</h2>
                <p>Admin Panel</p>
            </div>
            
            <nav class="admin-nav">
                <div class="nav-item">
                    <a href="admin_dashboard.php" class="nav-link">
                        <i class="fas fa-tachometer-alt"></i>
                        Dashboard
                    </a>
                </div>
                <div class="nav-item">
                    <a href="manage_users.php" class="nav-link">
                        <i class="fas fa-users"></i>
                        Manage Users
                    </a>
                </div>
                <div class="nav-item">
                    <a href="manage_skills.php" class="nav-link">
                        <i class="fas fa-cogs"></i>
                        Manage Skills
                    </a>
                </div>
                <div class="nav-item">
                    <a href="manage_connections.php" class="nav-link">
                        <i class="fas fa-handshake"></i>
                        Connections
                    </a>
                </div>
                <div class="nav-item">
                    <a href="manage_messages.php" class="nav-link active">
                        <i class="fas fa-comments"></i>
                        Messages
                    </a>
                </div>
                <div class="nav-item">
                    <a href="reports.php" class="nav-link">
                        <i class="fas fa-chart-bar"></i>
                        Reports
                    </a>
                </div>
                <div class="nav-item">
                    <a href="settings.php" class="nav-link">
                        <i class="fas fa-cog"></i>
                        Settings
                    </a>
                </div>
            </nav>
        </div>
        
        <!-- Main Content -->
        <div class="admin-main">
            <!-- Header -->
            <div class="admin-header">
                <h1 class="admin-title">Manage Messages</h1>
                <div class="admin-user">
                    <div class="admin-avatar">
                        <?php echo strtoupper(substr($_SESSION['admin_name'], 0, 1)); ?>
                    </div>
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
                    <a href="../includes/logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </div>
            </div>
            
            <!-- Success/Error Messages -->
            <?php if (isset($success_msg)): ?>
                <div class="content-card" style="background: #c6f6d5; border-left: 4px solid #48bb78; margin-bottom: 1rem;">
                    <p style="color: #2f855a; margin: 0;"><i class="fas fa-check-circle"></i> <?php echo $success_msg; ?></p>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error_msg)): ?>
                <div class="content-card" style="background: #fed7d7; border-left: 4px solid #e53e3e; margin-bottom: 1rem;">
                    <p style="color: #c53030; margin: 0;"><i class="fas fa-exclamation-circle"></i> <?php echo $error_msg; ?></p>
                </div>
            <?php endif; ?>
            
            <!-- Search and Filters -->
            <div class="content-card">
                <form method="GET" class="search-filters">
                    <input type="text" 
                           name="search" 
                           placeholder="Search messages or user names..." 
                           class="search-input"
                           value="<?php echo htmlspecialchars($search); ?>">
                    
                    <select name="user" class="filter-select">
                        <option value="">All Users</option>
                        <?php while ($user = mysqli_fetch_assoc($users_result)): ?>
                            <option value="<?php echo $user['id']; ?>" 
                                    <?php echo ($user_filter == $user['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($user['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i>
                        Search
                    </button>
                    
                    <a href="manage_messages.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i>
                        Clear
                    </a>
                </form>
            </div>
            
            <!-- Messages Table -->
            <div class="content-card">
                <div class="card-header">
                    <h3 class="card-title">Chat Messages (Latest 100 messages)</h3>
                    <small style="color: #718096;">Showing <?php echo mysqli_num_rows($result); ?> messages</small>
                </div>
                
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>From</th>
                                <th>To</th>
                                <th>Message</th>
                                <th>Sent</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td>#<?php echo $row['id']; ?></td>
                                <td>
                                    <div>
                                        <strong><?php echo htmlspecialchars($row['sender_name']); ?></strong>
                                        <br>
                                        <small style="color: #718096;"><?php echo htmlspecialchars($row['sender_email']); ?></small>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <strong><?php echo htmlspecialchars($row['receiver_name']); ?></strong>
                                        <br>
                                        <small style="color: #718096;"><?php echo htmlspecialchars($row['receiver_email']); ?></small>
                                    </div>
                                </td>
                                <td>
                                    <div style="max-width: 300px; overflow: hidden;">
                                        <?php 
                                        $message = htmlspecialchars($row['message']);
                                        echo strlen($message) > 100 ? substr($message, 0, 100) . '...' : $message;
                                        ?>
                                    </div>
                                </td>
                                <td><?php echo date('M d, Y g:i A', strtotime($row['timestamp'])); ?></td>
                                <td>
                                    <span class="status-badge <?php echo $row['is_read'] ? 'status-accepted' : 'status-pending'; ?>">
                                        <?php echo $row['is_read'] ? 'Read' : 'Unread'; ?>
                                    </span>
                                </td>
                                <td>
                                    <button onclick="viewMessage('<?php echo htmlspecialchars(addslashes($row['message'])); ?>')" 
                                            class="btn btn-primary btn-sm"
                                            title="View Full Message">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    
                                    <button onclick="deleteMessage(<?php echo $row['id']; ?>)" 
                                            class="btn btn-danger btn-sm"
                                            title="Delete Message">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div style="text-align: center; padding: 3rem; color: #718096;">
                        <i class="fas fa-comments" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                        <h3>No messages found</h3>
                        <p>Try adjusting your search criteria or filters.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Message Modal -->
    <div id="messageModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 2rem; border-radius: 15px; max-width: 500px; max-height: 80vh; overflow-y: auto;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h3>Full Message</h3>
                <button onclick="closeModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
            </div>
            <div id="modalContent" style="white-space: pre-wrap; line-height: 1.6;"></div>
        </div>
    </div>
    
    <script>
        function viewMessage(message) {
            document.getElementById('modalContent').textContent = message;
            document.getElementById('messageModal').style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('messageModal').style.display = 'none';
        }
        
        function deleteMessage(id) {
            if (confirm('Are you sure you want to delete this message?\n\nThis action cannot be undone.')) {
                window.location.href = `manage_messages.php?action=delete&message_id=${id}`;
            }
        }
        
        // Close modal on outside click
        document.getElementById('messageModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
        
        // Auto-submit form on filter change
        document.querySelectorAll('.filter-select').forEach(select => {
            select.addEventListener('change', function() {
                this.form.submit();
            });
        });
    </script>
</body>
</html>

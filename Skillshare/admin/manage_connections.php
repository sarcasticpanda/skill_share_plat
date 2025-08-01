<?php
require_once('admin_auth.php');
require_once('../includes/db.php');
requireAdmin();

// Handle connection actions
if (isset($_GET['action']) && isset($_GET['connection_id'])) {
    $connection_id = intval($_GET['connection_id']);
    $action = $_GET['action'];
    
    if ($action == 'delete') {
        $stmt = mysqli_prepare($conn, "DELETE FROM messages WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $connection_id);
        if (mysqli_stmt_execute($stmt)) {
            $success_msg = "Connection deleted successfully!";
        } else {
            $error_msg = "Error deleting connection!";
        }
    } elseif ($action == 'update_status') {
        $status = $_GET['status'];
        $stmt = mysqli_prepare($conn, "UPDATE messages SET status = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "si", $status, $connection_id);
        if (mysqli_stmt_execute($stmt)) {
            $success_msg = "Connection status updated successfully!";
        } else {
            $error_msg = "Error updating connection status!";
        }
    }
}

// Handle search and filters
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$status_filter = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';

// Build query with filters
$where_conditions = [];
$params = [];
$types = "";

if (!empty($search)) {
    $where_conditions[] = "(u1.name LIKE ? OR u2.name LIKE ? OR u1.email LIKE ? OR u2.email LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ssss";
}

if (!empty($status_filter)) {
    $where_conditions[] = "m.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

$where_clause = "";
if (!empty($where_conditions)) {
    $where_clause = "WHERE " . implode(" AND ", $where_conditions);
}

$sql = "SELECT m.*, 
               u1.name as from_name, u1.email as from_email,
               u2.name as to_name, u2.email as to_email
        FROM messages m
        JOIN users u1 ON m.from_id = u1.id
        JOIN users u2 ON m.to_id = u2.id
        $where_clause
        ORDER BY m.timestamp DESC";

$stmt = mysqli_prepare($conn, $sql);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Connections - Admin Panel</title>
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
                    <a href="manage_connections.php" class="nav-link active">
                        <i class="fas fa-handshake"></i>
                        Connections
                    </a>
                </div>
                <div class="nav-item">
                    <a href="manage_messages.php" class="nav-link">
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
                <h1 class="admin-title">Manage Connections</h1>
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
                           placeholder="Search by user names or emails..." 
                           class="search-input"
                           value="<?php echo htmlspecialchars($search); ?>">
                    
                    <select name="status" class="filter-select">
                        <option value="">All Statuses</option>
                        <option value="pending" <?php echo ($status_filter == 'pending') ? 'selected' : ''; ?>>Pending</option>
                        <option value="accepted" <?php echo ($status_filter == 'accepted') ? 'selected' : ''; ?>>Accepted</option>
                        <option value="rejected" <?php echo ($status_filter == 'rejected') ? 'selected' : ''; ?>>Rejected</option>
                    </select>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i>
                        Search
                    </button>
                    
                    <a href="manage_connections.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i>
                        Clear
                    </a>
                </form>
            </div>
            
            <!-- Connections Table -->
            <div class="content-card">
                <div class="card-header">
                    <h3 class="card-title">All Connection Requests (<?php echo mysqli_num_rows($result); ?> found)</h3>
                </div>
                
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>From User</th>
                                <th>To User</th>
                                <th>Status</th>
                                <th>Requested Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td>#<?php echo $row['id']; ?></td>
                                <td>
                                    <div>
                                        <strong><?php echo htmlspecialchars($row['from_name']); ?></strong>
                                        <br>
                                        <small style="color: #718096;"><?php echo htmlspecialchars($row['from_email']); ?></small>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <strong><?php echo htmlspecialchars($row['to_name']); ?></strong>
                                        <br>
                                        <small style="color: #718096;"><?php echo htmlspecialchars($row['to_email']); ?></small>
                                    </div>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $row['status']; ?>">
                                        <?php echo ucfirst($row['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y g:i A', strtotime($row['timestamp'])); ?></td>
                                <td>
                                    <?php if ($row['status'] == 'pending'): ?>
                                        <button onclick="updateStatus(<?php echo $row['id']; ?>, 'accepted')" 
                                                class="btn btn-success btn-sm"
                                                title="Accept Connection">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        
                                        <button onclick="updateStatus(<?php echo $row['id']; ?>, 'rejected')" 
                                                class="btn btn-danger btn-sm"
                                                title="Reject Connection">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    <?php else: ?>
                                        <button onclick="updateStatus(<?php echo $row['id']; ?>, 'pending')" 
                                                class="btn btn-warning btn-sm"
                                                title="Reset to Pending">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                    <?php endif; ?>
                                    
                                    <button onclick="deleteConnection(<?php echo $row['id']; ?>)" 
                                            class="btn btn-danger btn-sm"
                                            title="Delete Connection">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div style="text-align: center; padding: 3rem; color: #718096;">
                        <i class="fas fa-handshake" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                        <h3>No connections found</h3>
                        <p>Try adjusting your search criteria or filters.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        function updateStatus(id, status) {
            const statusText = status.charAt(0).toUpperCase() + status.slice(1);
            if (confirm(`Are you sure you want to ${status} this connection?`)) {
                window.location.href = `manage_connections.php?action=update_status&connection_id=${id}&status=${status}`;
            }
        }
        
        function deleteConnection(id) {
            if (confirm(`Are you sure you want to delete this connection?\n\nThis action cannot be undone.`)) {
                window.location.href = `manage_connections.php?action=delete&connection_id=${id}`;
            }
        }
        
        // Auto-submit form on filter change
        document.querySelectorAll('.filter-select').forEach(select => {
            select.addEventListener('change', function() {
                this.form.submit();
            });
        });
    </script>
</body>
</html>

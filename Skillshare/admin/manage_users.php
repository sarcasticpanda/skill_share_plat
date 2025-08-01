<?php
require_once('admin_auth.php');
require_once('../includes/db.php');
requireAdmin();

// Handle user actions
if (isset($_GET['action']) && isset($_GET['user_id'])) {
    $user_id = intval($_GET['user_id']);
    $action = $_GET['action'];
    
    if ($action == 'toggle_role') {
        $stmt = mysqli_prepare($conn, "UPDATE users SET role = CASE WHEN role = 'admin' THEN 'user' ELSE 'admin' END WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        if (mysqli_stmt_execute($stmt)) {
            $success_msg = "User role updated successfully!";
        } else {
            $error_msg = "Error updating user role!";
        }
    } elseif ($action == 'delete') {
        // Check if user has any dependencies
        $check_stmt = mysqli_prepare($conn, "SELECT COUNT(*) as count FROM skills WHERE user_id = ?");
        mysqli_stmt_bind_param($check_stmt, "i", $user_id);
        mysqli_stmt_execute($check_stmt);
        $result = mysqli_stmt_get_result($check_stmt);
        $skills_count = mysqli_fetch_assoc($result)['count'];
        
        if ($skills_count > 0) {
            $error_msg = "Cannot delete user with existing skills. Please delete their skills first.";
        } else {
            $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id = ? AND id != ?");
            mysqli_stmt_bind_param($stmt, "ii", $user_id, $_SESSION['admin_id']);
            if (mysqli_stmt_execute($stmt)) {
                $success_msg = "User deleted successfully!";
            } else {
                $error_msg = "Error deleting user!";
            }
        }
    }
}

// Handle search
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$role_filter = isset($_GET['role']) ? mysqli_real_escape_string($conn, $_GET['role']) : '';

// Build query with filters
$where_conditions = [];
$params = [];
$types = "";

if (!empty($search)) {
    $where_conditions[] = "(name LIKE ? OR email LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}

if (!empty($role_filter)) {
    $where_conditions[] = "role = ?";
    $params[] = $role_filter;
    $types .= "s";
}

$where_clause = "";
if (!empty($where_conditions)) {
    $where_clause = "WHERE " . implode(" AND ", $where_conditions);
}

$sql = "SELECT users.*, 
               (SELECT COUNT(*) FROM skills WHERE user_id = users.id) as skills_count,
               (SELECT COUNT(*) FROM messages WHERE from_id = users.id OR to_id = users.id) as connections_count
        FROM users 
        $where_clause
        ORDER BY users.created_at DESC";

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
    <title>Manage Users - Admin Panel</title>
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
                    <a href="manage_users.php" class="nav-link active">
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
                <h1 class="admin-title">Manage Users</h1>
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
                           placeholder="Search users by name or email..." 
                           class="search-input"
                           value="<?php echo htmlspecialchars($search); ?>">
                    
                    <select name="role" class="filter-select">
                        <option value="">All Roles</option>
                        <option value="user" <?php echo ($role_filter == 'user') ? 'selected' : ''; ?>>Users</option>
                        <option value="admin" <?php echo ($role_filter == 'admin') ? 'selected' : ''; ?>>Admins</option>
                    </select>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i>
                        Search
                    </button>
                    
                    <a href="manage_users.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i>
                        Clear
                    </a>
                </form>
            </div>
            
            <!-- Users Table -->
            <div class="content-card">
                <div class="card-header">
                    <h3 class="card-title">All Users (<?php echo mysqli_num_rows($result); ?> found)</h3>
                </div>
                
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User Info</th>
                                <th>Role</th>
                                <th>Skills</th>
                                <th>Connections</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td>#<?php echo $row['id']; ?></td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        <div class="admin-avatar" style="width: 35px; height: 35px; font-size: 0.9rem;">
                                            <?php echo strtoupper(substr($row['name'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <strong><?php echo htmlspecialchars($row['name']); ?></strong>
                                            <br>
                                            <small style="color: #718096;"><?php echo htmlspecialchars($row['email']); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo $row['role'] == 'admin' ? 'status-rejected' : 'status-accepted'; ?>">
                                        <?php echo ucfirst($row['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge status-offer">
                                        <?php echo $row['skills_count']; ?> skills
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge status-pending">
                                        <?php echo $row['connections_count']; ?> connections
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                <td>
                                    <?php if ($row['id'] != $_SESSION['admin_id']): ?>
                                        <button onclick="toggleRole(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['name']); ?>', '<?php echo $row['role']; ?>')" 
                                                class="btn btn-warning btn-sm"
                                                title="Toggle Role">
                                            <i class="fas fa-user-cog"></i>
                                        </button>
                                        
                                        <button onclick="deleteUser(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['name']); ?>')" 
                                                class="btn btn-danger btn-sm"
                                                title="Delete User">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?php else: ?>
                                        <span class="status-badge status-accepted">Current Admin</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div style="text-align: center; padding: 3rem; color: #718096;">
                        <i class="fas fa-users" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                        <h3>No users found</h3>
                        <p>Try adjusting your search criteria or filters.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        function toggleRole(id, name, currentRole) {
            const newRole = currentRole === 'admin' ? 'user' : 'admin';
            if (confirm(`Are you sure you want to change ${name}'s role from ${currentRole} to ${newRole}?`)) {
                window.location.href = `manage_users.php?action=toggle_role&user_id=${id}`;
            }
        }
        
        function deleteUser(id, name) {
            if (confirm(`Are you sure you want to delete user "${name}"?\n\nThis action cannot be undone.`)) {
                window.location.href = `manage_users.php?action=delete&user_id=${id}`;
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

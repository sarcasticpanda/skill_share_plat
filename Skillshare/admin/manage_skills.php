<?php
require_once('admin_auth.php');
require_once('../includes/db.php');
requireAdmin();

// Handle delete action
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = mysqli_prepare($conn, "DELETE FROM skills WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    if (mysqli_stmt_execute($stmt)) {
        $success_msg = "Skill deleted successfully!";
    } else {
        $error_msg = "Error deleting skill!";
    }
}

// Handle search and filters
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$category_filter = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : '';
$type_filter = isset($_GET['type']) ? mysqli_real_escape_string($conn, $_GET['type']) : '';

// Build query with filters
$where_conditions = [];
$params = [];
$types = "";

if (!empty($search)) {
    $where_conditions[] = "(skills.title LIKE ? OR skills.description LIKE ? OR users.name LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "sss";
}

if (!empty($category_filter)) {
    $where_conditions[] = "skills.category = ?";
    $params[] = $category_filter;
    $types .= "s";
}

if (!empty($type_filter)) {
    $where_conditions[] = "skills.type = ?";
    $params[] = $type_filter;
    $types .= "s";
}

$where_clause = "";
if (!empty($where_conditions)) {
    $where_clause = "WHERE " . implode(" AND ", $where_conditions);
}

$sql = "SELECT skills.*, users.name, users.email 
        FROM skills 
        JOIN users ON skills.user_id = users.id 
        $where_clause
        ORDER BY skills.created_at DESC";

$stmt = mysqli_prepare($conn, $sql);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Get categories for filter dropdown
$categories_result = mysqli_query($conn, "SELECT DISTINCT category FROM skills ORDER BY category");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Skills - Admin Panel</title>
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
                    <a href="manage_skills.php" class="nav-link active">
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
                <h1 class="admin-title">Manage Skills</h1>
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
                           placeholder="Search skills, descriptions, or user names..." 
                           class="search-input"
                           value="<?php echo htmlspecialchars($search); ?>">
                    
                    <select name="category" class="filter-select">
                        <option value="">All Categories</option>
                        <?php while ($cat = mysqli_fetch_assoc($categories_result)): ?>
                            <option value="<?php echo htmlspecialchars($cat['category']); ?>" 
                                    <?php echo ($category_filter == $cat['category']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['category']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    
                    <select name="type" class="filter-select">
                        <option value="">All Types</option>
                        <option value="offer" <?php echo ($type_filter == 'offer') ? 'selected' : ''; ?>>Offer</option>
                        <option value="request" <?php echo ($type_filter == 'request') ? 'selected' : ''; ?>>Request</option>
                    </select>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i>
                        Search
                    </button>
                    
                    <a href="manage_skills.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i>
                        Clear
                    </a>
                </form>
            </div>
            
            <!-- Skills Table -->
            <div class="content-card">
                <div class="card-header">
                    <h3 class="card-title">All Skills (<?php echo mysqli_num_rows($result); ?> found)</h3>
                </div>
                
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Skill Title</th>
                                <th>Posted By</th>
                                <th>Category</th>
                                <th>Type</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td>#<?php echo $row['id']; ?></td>
                                <td>
                                    <div style="max-width: 200px;">
                                        <strong><?php echo htmlspecialchars($row['title']); ?></strong>
                                        <br>
                                        <small style="color: #718096;">
                                            <?php echo htmlspecialchars(substr($row['description'], 0, 100)) . (strlen($row['description']) > 100 ? '...' : ''); ?>
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['name']); ?></strong>
                                    <br>
                                    <small style="color: #718096;"><?php echo htmlspecialchars($row['email']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($row['category']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $row['type']; ?>">
                                        <?php echo ucfirst($row['type']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                <td>
                                    <a href="../pages/view_skill.php?id=<?php echo $row['id']; ?>" 
                                       class="btn btn-primary btn-sm" 
                                       target="_blank"
                                       title="View Skill">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    <button onclick="deleteSkill(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['title']); ?>')" 
                                            class="btn btn-danger btn-sm"
                                            title="Delete Skill">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div style="text-align: center; padding: 3rem; color: #718096;">
                        <i class="fas fa-search" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                        <h3>No skills found</h3>
                        <p>Try adjusting your search criteria or filters.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        function deleteSkill(id, title) {
            if (confirm(`Are you sure you want to delete the skill "${title}"?\n\nThis action cannot be undone.`)) {
                window.location.href = `manage_skills.php?delete=${id}`;
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

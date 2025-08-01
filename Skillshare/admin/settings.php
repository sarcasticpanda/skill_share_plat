<?php
require_once('admin_auth.php');
require_once('../includes/db.php');
requireAdmin();

$success_msg = '';
$error_msg = '';

// Handle settings updates
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_profile'])) {
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        
        // Check if email is already taken by another user
        $check_stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? AND id != ?");
        mysqli_stmt_bind_param($check_stmt, "si", $email, $_SESSION['admin_id']);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        
        if (mysqli_num_rows($check_result) > 0) {
            $error_msg = "Email is already taken by another user!";
        } else {
            $stmt = mysqli_prepare($conn, "UPDATE users SET name = ?, email = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "ssi", $name, $email, $_SESSION['admin_id']);
            
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['admin_name'] = $name;
                $_SESSION['admin_email'] = $email;
                $success_msg = "Profile updated successfully!";
            } else {
                $error_msg = "Error updating profile!";
            }
        }
    }
    
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Verify current password
        $stmt = mysqli_prepare($conn, "SELECT password FROM users WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $_SESSION['admin_id']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        
        if (!password_verify($current_password, $user['password'])) {
            $error_msg = "Current password is incorrect!";
        } elseif ($new_password !== $confirm_password) {
            $error_msg = "New passwords do not match!";
        } elseif (strlen($new_password) < 6) {
            $error_msg = "New password must be at least 6 characters long!";
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "si", $hashed_password, $_SESSION['admin_id']);
            
            if (mysqli_stmt_execute($stmt)) {
                $success_msg = "Password changed successfully!";
            } else {
                $error_msg = "Error changing password!";
            }
        }
    }
    
    if (isset($_POST['create_admin'])) {
        $name = mysqli_real_escape_string($conn, $_POST['admin_name']);
        $email = mysqli_real_escape_string($conn, $_POST['admin_email']);
        $password = $_POST['admin_password'];
        
        // Check if email already exists
        $check_stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($check_stmt, "s", $email);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        
        if (mysqli_num_rows($check_result) > 0) {
            $error_msg = "Email is already registered!";
        } elseif (strlen($password) < 6) {
            $error_msg = "Password must be at least 6 characters long!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($conn, "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'admin')");
            mysqli_stmt_bind_param($stmt, "sss", $name, $email, $hashed_password);
            
            if (mysqli_stmt_execute($stmt)) {
                $success_msg = "New admin created successfully!";
            } else {
                $error_msg = "Error creating admin!";
            }
        }
    }
}

// Get current admin info
$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $_SESSION['admin_id']);
mysqli_stmt_execute($stmt);
$admin_info = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

// Get all admins
$admins_result = mysqli_query($conn, "SELECT * FROM users WHERE role = 'admin' ORDER BY created_at ASC");

// Get system info
$system_info = [
    'php_version' => phpversion(),
    'mysql_version' => mysqli_get_server_info($conn),
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size'),
    'memory_limit' => ini_get('memory_limit')
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Admin Panel</title>
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
                    <a href="settings.php" class="nav-link active">
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
                <h1 class="admin-title">Settings</h1>
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
            <?php if ($success_msg): ?>
                <div class="content-card" style="background: #c6f6d5; border-left: 4px solid #48bb78; margin-bottom: 1rem;">
                    <p style="color: #2f855a; margin: 0;"><i class="fas fa-check-circle"></i> <?php echo $success_msg; ?></p>
                </div>
            <?php endif; ?>
            
            <?php if ($error_msg): ?>
                <div class="content-card" style="background: #fed7d7; border-left: 4px solid #e53e3e; margin-bottom: 1rem;">
                    <p style="color: #c53030; margin: 0;"><i class="fas fa-exclamation-circle"></i> <?php echo $error_msg; ?></p>
                </div>
            <?php endif; ?>
            
            <!-- Profile Settings -->
            <div class="content-card">
                <div class="card-header">
                    <h3 class="card-title">Profile Settings</h3>
                </div>
                
                <form method="POST" style="max-width: 500px;">
                    <div class="form-group">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               class="form-input" 
                               value="<?php echo htmlspecialchars($admin_info['name']); ?>" 
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               class="form-input" 
                               value="<?php echo htmlspecialchars($admin_info['email']); ?>" 
                               required>
                    </div>
                    
                    <button type="submit" name="update_profile" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Update Profile
                    </button>
                </form>
            </div>
            
            <!-- Change Password -->
            <div class="content-card">
                <div class="card-header">
                    <h3 class="card-title">Change Password</h3>
                </div>
                
                <form method="POST" style="max-width: 500px;">
                    <div class="form-group">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" 
                               id="current_password" 
                               name="current_password" 
                               class="form-input" 
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" 
                               id="new_password" 
                               name="new_password" 
                               class="form-input" 
                               minlength="6"
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" 
                               id="confirm_password" 
                               name="confirm_password" 
                               class="form-input" 
                               minlength="6"
                               required>
                    </div>
                    
                    <button type="submit" name="change_password" class="btn btn-warning">
                        <i class="fas fa-key"></i>
                        Change Password
                    </button>
                </form>
            </div>
            
            <!-- Create New Admin -->
            <div class="content-card">
                <div class="card-header">
                    <h3 class="card-title">Create New Admin</h3>
                </div>
                
                <form method="POST" style="max-width: 500px;">
                    <div class="form-group">
                        <label for="admin_name" class="form-label">Name</label>
                        <input type="text" 
                               id="admin_name" 
                               name="admin_name" 
                               class="form-input" 
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="admin_email" class="form-label">Email</label>
                        <input type="email" 
                               id="admin_email" 
                               name="admin_email" 
                               class="form-input" 
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="admin_password" class="form-label">Password</label>
                        <input type="password" 
                               id="admin_password" 
                               name="admin_password" 
                               class="form-input" 
                               minlength="6"
                               required>
                    </div>
                    
                    <button type="submit" name="create_admin" class="btn btn-success">
                        <i class="fas fa-user-plus"></i>
                        Create Admin
                    </button>
                </form>
            </div>
            
            <!-- All Admins -->
            <div class="content-card">
                <div class="card-header">
                    <h3 class="card-title">All Administrators</h3>
                </div>
                
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Created</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($admin = mysqli_fetch_assoc($admins_result)): ?>
                        <tr>
                            <td>#<?php echo $admin['id']; ?></td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <div class="admin-avatar" style="width: 35px; height: 35px; font-size: 0.9rem;">
                                        <?php echo strtoupper(substr($admin['name'], 0, 1)); ?>
                                    </div>
                                    <strong><?php echo htmlspecialchars($admin['name']); ?></strong>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($admin['email']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($admin['created_at'])); ?></td>
                            <td>
                                <?php if ($admin['id'] == $_SESSION['admin_id']): ?>
                                    <span class="status-badge status-accepted">Current Session</span>
                                <?php else: ?>
                                    <span class="status-badge status-offer">Active</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- System Information -->
            <div class="content-card">
                <div class="card-header">
                    <h3 class="card-title">System Information</h3>
                </div>
                
                <table class="admin-table">
                    <tbody>
                        <tr>
                            <td><strong>PHP Version</strong></td>
                            <td><?php echo $system_info['php_version']; ?></td>
                        </tr>
                        <tr>
                            <td><strong>MySQL Version</strong></td>
                            <td><?php echo $system_info['mysql_version']; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Server Software</strong></td>
                            <td><?php echo $system_info['server_software']; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Upload Max Filesize</strong></td>
                            <td><?php echo $system_info['upload_max_filesize']; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Post Max Size</strong></td>
                            <td><?php echo $system_info['post_max_size']; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Memory Limit</strong></td>
                            <td><?php echo $system_info['memory_limit']; ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script>
        // Password confirmation validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = this.value;
            
            if (newPassword !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
        
        document.getElementById('new_password').addEventListener('input', function() {
            const confirmPassword = document.getElementById('confirm_password');
            if (confirmPassword.value) {
                confirmPassword.dispatchEvent(new Event('input'));
            }
        });
    </script>
</body>
</html>

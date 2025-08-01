<?php
require_once('admin_auth.php');
require_once('../includes/db.php');
requireAdmin();

$stats = getAdminStats($conn);
$recent_activities = getRecentActivity($conn, 10);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Skillshare</title>
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
                    <a href="admin_dashboard.php" class="nav-link active">
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
                <h1 class="admin-title">Dashboard Overview</h1>
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
            
            <!-- Statistics Cards -->
            <div class="dashboard-stats">
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Total Users</span>
                        <div class="stat-icon users">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="stat-number"><?php echo number_format($stats['users']); ?></div>
                    <div class="stat-description">
                        <?php echo $stats['new_users_today']; ?> new today
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Total Skills</span>
                        <div class="stat-icon skills">
                            <i class="fas fa-cogs"></i>
                        </div>
                    </div>
                    <div class="stat-number"><?php echo number_format($stats['skills']); ?></div>
                    <div class="stat-description">
                        <?php echo $stats['new_skills_today']; ?> posted today
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Connections</span>
                        <div class="stat-icon connections">
                            <i class="fas fa-handshake"></i>
                        </div>
                    </div>
                    <div class="stat-number"><?php echo number_format($stats['connections']); ?></div>
                    <div class="stat-description">Active connections</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Messages</span>
                        <div class="stat-icon messages">
                            <i class="fas fa-comments"></i>
                        </div>
                    </div>
                    <div class="stat-number"><?php echo number_format($stats['chat_messages']); ?></div>
                    <div class="stat-description">Total messages sent</div>
                </div>
            </div>
            
            <!-- Recent Activity -->
            <div class="content-card">
                <div class="card-header">
                    <h3 class="card-title">Recent Activity</h3>
                    <a href="reports.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-chart-line"></i>
                        View Reports
                    </a>
                </div>
                
                <div class="activity-list">
                    <?php if (count($recent_activities) > 0): ?>
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Activity</th>
                                    <th>Type</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_activities as $activity): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($activity['title']); ?></td>
                                    <td>
                                        <?php if ($activity['type'] == 'user_registered'): ?>
                                            <span class="status-badge status-accepted">User Registration</span>
                                        <?php else: ?>
                                            <span class="status-badge status-offer">Skill Posted</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('M d, Y g:i A', strtotime($activity['timestamp'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p style="text-align: center; color: #718096; padding: 2rem;">No recent activity found.</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="content-card">
                <div class="card-header">
                    <h3 class="card-title">Quick Actions</h3>
                </div>
                
                <div class="dashboard-stats">
                    <div class="stat-card" style="cursor: pointer;" onclick="location.href='manage_users.php'">
                        <div class="stat-header">
                            <span class="stat-title">Manage Users</span>
                            <div class="stat-icon users">
                                <i class="fas fa-user-cog"></i>
                            </div>
                        </div>
                        <div class="stat-description">View and manage all registered users</div>
                    </div>
                    
                    <div class="stat-card" style="cursor: pointer;" onclick="location.href='manage_skills.php'">
                        <div class="stat-header">
                            <span class="stat-title">Manage Skills</span>
                            <div class="stat-icon skills">
                                <i class="fas fa-tools"></i>
                            </div>
                        </div>
                        <div class="stat-description">Review and moderate skill posts</div>
                    </div>
                    
                    <div class="stat-card" style="cursor: pointer;" onclick="location.href='manage_connections.php'">
                        <div class="stat-header">
                            <span class="stat-title">Monitor Connections</span>
                            <div class="stat-icon connections">
                                <i class="fas fa-network-wired"></i>
                            </div>
                        </div>
                        <div class="stat-description">View user connections and requests</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Auto-refresh stats every 30 seconds
        setInterval(function() {
            location.reload();
        }, 30000);
    </script>
</body>
</html>

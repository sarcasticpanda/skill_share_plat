<?php
require_once('admin_auth.php');
require_once('../includes/db.php');
requireAdmin();

// Get statistics for different time periods
$stats = [];

// Today's stats
$today = date('Y-m-d');
$stats['today'] = [
    'users' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE DATE(created_at) = '$today'"))['count'],
    'skills' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM skills WHERE DATE(created_at) = '$today'"))['count'],
    'connections' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM messages WHERE DATE(timestamp) = '$today'"))['count'],
    'messages' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM chat_messages WHERE DATE(timestamp) = '$today'"))['count']
];

// This week's stats
$week_start = date('Y-m-d', strtotime('monday this week'));
$stats['week'] = [
    'users' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE created_at >= '$week_start'"))['count'],
    'skills' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM skills WHERE created_at >= '$week_start'"))['count'],
    'connections' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM messages WHERE timestamp >= '$week_start'"))['count'],
    'messages' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM chat_messages WHERE timestamp >= '$week_start'"))['count']
];

// This month's stats
$month_start = date('Y-m-01');
$stats['month'] = [
    'users' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE created_at >= '$month_start'"))['count'],
    'skills' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM skills WHERE created_at >= '$month_start'"))['count'],
    'connections' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM messages WHERE timestamp >= '$month_start'"))['count'],
    'messages' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM chat_messages WHERE timestamp >= '$month_start'"))['count']
];

// Total stats
$stats['total'] = [
    'users' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role = 'user'"))['count'],
    'skills' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM skills"))['count'],
    'connections' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM messages WHERE status = 'accepted'"))['count'],
    'messages' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM chat_messages"))['count']
];

// Get top categories
$top_categories = [];
$cat_result = mysqli_query($conn, "SELECT category, COUNT(*) as count FROM skills GROUP BY category ORDER BY count DESC LIMIT 5");
while ($row = mysqli_fetch_assoc($cat_result)) {
    $top_categories[] = $row;
}

// Get most active users
$active_users = [];
$user_result = mysqli_query($conn, "
    SELECT u.name, u.email,
           (SELECT COUNT(*) FROM skills WHERE user_id = u.id) as skills_count,
           (SELECT COUNT(*) FROM chat_messages WHERE sender_id = u.id) as messages_sent
    FROM users u
    WHERE u.role = 'user'
    ORDER BY (skills_count + messages_sent) DESC
    LIMIT 10
");
while ($row = mysqli_fetch_assoc($user_result)) {
    $active_users[] = $row;
}

// Get daily registrations for the last 30 days
$daily_registrations = [];
for ($i = 29; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE DATE(created_at) = '$date'"))['count'];
    $daily_registrations[] = ['date' => $date, 'count' => $count];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                    <a href="reports.php" class="nav-link active">
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
                <h1 class="admin-title">Reports & Analytics</h1>
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
            
            <!-- Time Period Stats -->
            <div class="content-card">
                <div class="card-header">
                    <h3 class="card-title">Statistics by Time Period</h3>
                </div>
                
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Period</th>
                            <th>New Users</th>
                            <th>New Skills</th>
                            <th>New Connections</th>
                            <th>Messages Sent</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Today</strong></td>
                            <td><?php echo $stats['today']['users']; ?></td>
                            <td><?php echo $stats['today']['skills']; ?></td>
                            <td><?php echo $stats['today']['connections']; ?></td>
                            <td><?php echo $stats['today']['messages']; ?></td>
                        </tr>
                        <tr>
                            <td><strong>This Week</strong></td>
                            <td><?php echo $stats['week']['users']; ?></td>
                            <td><?php echo $stats['week']['skills']; ?></td>
                            <td><?php echo $stats['week']['connections']; ?></td>
                            <td><?php echo $stats['week']['messages']; ?></td>
                        </tr>
                        <tr>
                            <td><strong>This Month</strong></td>
                            <td><?php echo $stats['month']['users']; ?></td>
                            <td><?php echo $stats['month']['skills']; ?></td>
                            <td><?php echo $stats['month']['connections']; ?></td>
                            <td><?php echo $stats['month']['messages']; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Total</strong></td>
                            <td><?php echo $stats['total']['users']; ?></td>
                            <td><?php echo $stats['total']['skills']; ?></td>
                            <td><?php echo $stats['total']['connections']; ?></td>
                            <td><?php echo $stats['total']['messages']; ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Charts Row -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
                <!-- User Registration Chart -->
                <div class="content-card">
                    <div class="card-header">
                        <h3 class="card-title">User Registrations (Last 30 Days)</h3>
                    </div>
                    <canvas id="registrationChart" width="400" height="200"></canvas>
                </div>
                
                <!-- Categories Chart -->
                <div class="content-card">
                    <div class="card-header">
                        <h3 class="card-title">Top Skill Categories</h3>
                    </div>
                    <canvas id="categoriesChart" width="400" height="200"></canvas>
                </div>
            </div>
            
            <!-- Top Categories Table -->
            <div class="content-card">
                <div class="card-header">
                    <h3 class="card-title">Popular Skill Categories</h3>
                </div>
                
                <?php if (count($top_categories) > 0): ?>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Category</th>
                                <th>Number of Skills</th>
                                <th>Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $total_skills = $stats['total']['skills'];
                            foreach ($top_categories as $index => $category): 
                                $percentage = $total_skills > 0 ? round(($category['count'] / $total_skills) * 100, 1) : 0;
                            ?>
                            <tr>
                                <td>#<?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($category['category']); ?></td>
                                <td><?php echo $category['count']; ?></td>
                                <td><?php echo $percentage; ?>%</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="text-align: center; color: #718096; padding: 2rem;">No categories found.</p>
                <?php endif; ?>
            </div>
            
            <!-- Most Active Users -->
            <div class="content-card">
                <div class="card-header">
                    <h3 class="card-title">Most Active Users</h3>
                </div>
                
                <?php if (count($active_users) > 0): ?>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>User</th>
                                <th>Skills Posted</th>
                                <th>Messages Sent</th>
                                <th>Total Activity</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($active_users as $index => $user): ?>
                            <tr>
                                <td>#<?php echo $index + 1; ?></td>
                                <td>
                                    <div>
                                        <strong><?php echo htmlspecialchars($user['name']); ?></strong>
                                        <br>
                                        <small style="color: #718096;"><?php echo htmlspecialchars($user['email']); ?></small>
                                    </div>
                                </td>
                                <td><?php echo $user['skills_count']; ?></td>
                                <td><?php echo $user['messages_sent']; ?></td>
                                <td>
                                    <strong><?php echo $user['skills_count'] + $user['messages_sent']; ?></strong>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="text-align: center; color: #718096; padding: 2rem;">No active users found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        // User Registration Chart
        const registrationCtx = document.getElementById('registrationChart').getContext('2d');
        const registrationData = <?php echo json_encode($daily_registrations); ?>;
        
        new Chart(registrationCtx, {
            type: 'line',
            data: {
                labels: registrationData.map(item => {
                    const date = new Date(item.date);
                    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                }),
                datasets: [{
                    label: 'New Users',
                    data: registrationData.map(item => item.count),
                    borderColor: 'rgb(102, 126, 234)',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.1,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
        
        // Categories Chart
        const categoriesCtx = document.getElementById('categoriesChart').getContext('2d');
        const categoriesData = <?php echo json_encode($top_categories); ?>;
        
        new Chart(categoriesCtx, {
            type: 'doughnut',
            data: {
                labels: categoriesData.map(item => item.category),
                datasets: [{
                    data: categoriesData.map(item => item.count),
                    backgroundColor: [
                        'rgba(102, 126, 234, 0.8)',
                        'rgba(72, 187, 120, 0.8)',
                        'rgba(237, 137, 54, 0.8)',
                        'rgba(159, 122, 234, 0.8)',
                        'rgba(229, 62, 62, 0.8)'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
</body>
</html>

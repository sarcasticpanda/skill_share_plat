<?php
function requireAdmin() {
    session_start();
    if (!isset($_SESSION['admin_id'])) {
        header("Location: admin_login.php");
        exit();
    }
}

function getAdminStats($conn) {
    $stats = [];
    
    // Total users
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role = 'user'");
    $stats['users'] = mysqli_fetch_assoc($result)['count'];
    
    // Total skills
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM skills");
    $stats['skills'] = mysqli_fetch_assoc($result)['count'];
    
    // Total messages/connections
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM messages WHERE status = 'accepted'");
    $stats['connections'] = mysqli_fetch_assoc($result)['count'];
    
    // Total chat messages
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM chat_messages");
    $stats['chat_messages'] = mysqli_fetch_assoc($result)['count'];
    
    // Recent activities
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE DATE(created_at) = CURDATE()");
    $stats['new_users_today'] = mysqli_fetch_assoc($result)['count'];
    
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM skills WHERE DATE(created_at) = CURDATE()");
    $stats['new_skills_today'] = mysqli_fetch_assoc($result)['count'];
    
    return $stats;
}

function getRecentActivity($conn, $limit = 10) {
    $activities = [];
    
    // Recent user registrations
    $sql = "SELECT 'user_registered' as type, name as title, created_at as timestamp 
            FROM users WHERE role = 'user' 
            ORDER BY created_at DESC LIMIT $limit";
    $result = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_assoc($result)) {
        $activities[] = $row;
    }
    
    // Recent skill posts
    $sql = "SELECT 'skill_posted' as type, CONCAT(s.title, ' by ', u.name) as title, s.created_at as timestamp 
            FROM skills s 
            JOIN users u ON s.user_id = u.id 
            ORDER BY s.created_at DESC LIMIT $limit";
    $result = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_assoc($result)) {
        $activities[] = $row;
    }
    
    // Sort by timestamp
    usort($activities, function($a, $b) {
        return strtotime($b['timestamp']) - strtotime($a['timestamp']);
    });
    
    return array_slice($activities, 0, $limit);
}
?>

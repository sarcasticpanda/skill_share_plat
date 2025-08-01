<?php
require_once('../includes/auth.php');
require_once('../includes/db.php');
require_once('../includes/header.php');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verify session and user_id
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user name from database if not in session
if (!isset($_SESSION['user_name'])) {
    $user_sql = "SELECT name FROM users WHERE id = ?";
    $user_stmt = mysqli_prepare($conn, $user_sql);
    mysqli_stmt_bind_param($user_stmt, "i", $user_id);
    mysqli_stmt_execute($user_stmt);
    $user_result = mysqli_stmt_get_result($user_stmt);
    
    if ($user = mysqli_fetch_assoc($user_result)) {
        $_SESSION['user_name'] = $user['name'];
    } else {
        $_SESSION['user_name'] = 'User'; // Default if not found
    }
}

// Fetch skills shared by the user count
$skills_shared_count = 0;
$skills_sql = "SELECT COUNT(*) as count FROM skills WHERE user_id = ?";
$skills_stmt = mysqli_prepare($conn, $skills_sql);
if ($skills_stmt) {
    mysqli_stmt_bind_param($skills_stmt, "i", $user_id);
    mysqli_stmt_execute($skills_stmt);
    $skills_result = mysqli_stmt_get_result($skills_stmt);
    if ($row = mysqli_fetch_assoc($skills_result)) {
        $skills_shared_count = $row['count'];
    }
    mysqli_stmt_close($skills_stmt);
} else {
    error_log("Failed to prepare skills count query: " . mysqli_error($conn));
}

$new_messages_count = 0;
$messages_sql = "SELECT COUNT(*) as count FROM messages WHERE to_id = ? AND status = 'unread'";
$messages_stmt = mysqli_prepare($conn, $messages_sql);
if ($messages_stmt) {
    mysqli_stmt_bind_param($messages_stmt, "i", $user_id);
    mysqli_stmt_execute($messages_stmt);
    $messages_result = mysqli_stmt_get_result($messages_stmt);
    if ($row = mysqli_fetch_assoc($messages_result)) {
        $new_messages_count = $row['count'];
    }
    mysqli_stmt_close($messages_stmt);
} else {
     error_log("Failed to prepare new messages count query: " . mysqli_error($conn));
}

// Fetch accepted connections
$connections_sql = "
    SELECT 
        u.id,
        u.name,
        m.timestamp AS connected_since
    FROM 
        messages m
    JOIN 
        users u ON (m.from_id = u.id OR m.to_id = u.id)
    WHERE 
        ((m.from_id = ? AND m.to_id = u.id) OR (m.to_id = ? AND m.from_id = u.id))
        AND m.status = 'accepted'
    GROUP BY
        u.id
    ORDER BY 
        connected_since DESC
";

$connections_stmt = mysqli_prepare($conn, $connections_sql);
mysqli_stmt_bind_param($connections_stmt, "ii", $user_id, $user_id);
mysqli_stmt_execute($connections_stmt);
$connections_result = mysqli_stmt_get_result($connections_stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - SkillShare</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        :root {
            --primary-color: rgb(219, 43, 128);
            --secondary-color: rgb(195, 39, 45);
            --light-bg: #f8f9fa;
            --text-dark: #2b2d42;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
        }

        .dashboard-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .welcome-banner {
            text-align: center;
            padding: 2rem;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 1rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .welcome-banner h1 {
            font-size: 2.2rem;
            margin-bottom: 0.5rem;
        }

        .welcome-banner .subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin: 2rem 0;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 0.5rem;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card i {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }

        .stat-card h3 {
            font-size: 2rem;
            margin: 0.5rem 0;
            color: var(--text-dark);
        }

        .stat-card p {
            color: #666;
            margin: 0;
        }

        /* Quick Actions - Centered and Improved */
        .quick-actions {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            margin: 2.5rem 0;
            flex-wrap: wrap;
        }

        .action-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 0.5rem;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            min-width: 200px;
            justify-content: center;
        }

        .action-btn:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        .action-btn i {
            font-size: 1.2rem;
        }

        .dashboard-section {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
        }

        .dashboard-section h2 {
            font-size: 1.8em;
            color: #212529;
            margin-bottom: 25px;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 10px;
            display: flex;
            align-items: center;
        }

        .dashboard-section h2 i {
            margin-right: 12px;
            color: var(--primary-color);
        }

        /* Connections Grid */
        .connections-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .connection-card {
            background: white;
            padding: 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            text-align: center;
        }

        .connection-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .avatar {
            width: 60px;
            height: 60px;
            background: var(--primary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: bold;
            margin: 0 auto 1rem;
        }

        .connection-card h4 {
            margin: 0.5rem 0;
            color: var(--text-dark);
        }

        .connection-date {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .chat-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 0.3rem;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .chat-btn:hover {
            background: var(--secondary-color);
        }

        /* Skills Grid */
        .skills-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
        }

        .skill-card {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .skill-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
        }

        .skill-card img {
            display: block;
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-bottom: 1px solid #eee;
        }

        .skill-content {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .skill-content h4 {
            font-size: 1.25em;
            color: #212529;
            margin-top: 0;
            margin-bottom: 10px;
        }

        .skill-content p {
            font-size: 0.95em;
            color: #555;
            line-height: 1.5;
            margin-bottom: 15px;
            flex-grow: 1;
        }

        .skill-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: auto;
            padding-top: 10px;
            border-top: 1px solid #f0f0f0;
        }

        .skill-meta span {
            font-size: 0.9em;
            color: #7f8c8d;
            display: flex;
            align-items: center;
        }

        .skill-meta span i {
            margin-right: 6px;
            color: #3498db;
        }

        .interest-btn {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            font-size: 0.85em;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .interest-btn:hover {
            background-color: #2980b9;
        }

        .interest-btn.active {
            background-color: #27ae60;
        }

        /* Activity Section */
        .activity-list {
            margin: 1rem 0;
        }

        .activity-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: #f8f9fa;
            margin: 0.5rem 0;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }

        .activity-item:hover {
            background: #e9ecef;
        }

        .activity-item i {
            color: var(--primary-color);
            font-size: 1.2rem;
        }

        .activity-item p {
            margin: 0;
            flex-grow: 1;
        }

        .activity-item small {
            color: #666;
            font-size: 0.8rem;
        }

        .activity-form {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .activity-form input {
            flex-grow: 1;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 0.3rem;
        }

        .activity-form button {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 0 1.5rem;
            border-radius: 0.3rem;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .activity-form button:hover {
            background: var(--secondary-color);
        }

        /* Resources Grid */
        .resources-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .resource-card {
            background: white;
            padding: 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }

        .resource-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .resource-card h4 {
            color: var(--text-dark);
            margin-top: 0;
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        .resource-card h4 i {
            color: var(--primary-color);
        }

        .resource-card p {
            color: #666;
            margin-bottom: 1.5rem;
        }

        .resource-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 0.3rem;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .resource-btn:hover {
            background: var(--secondary-color);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 2rem;
            background: #f8f9fa;
            border-radius: 0.5rem;
            grid-column: 1 / -1;
        }

        .empty-state button {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 0.3rem;
            margin-top: 1rem;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .empty-state button:hover {
            background: var(--secondary-color);
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .quick-actions {
                flex-direction: column;
                align-items: center;
            }
            
            .action-btn {
                width: 100%;
                max-width: 250px;
            }
            
            .dashboard-section {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>

<div class="dashboard-container">
    <!-- Welcome Header -->
    <div class="welcome-banner">
        <h1>Welcome Back, <?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'User'; ?>! <span class="wave">👋</span></h1>
        <p class="subtitle">Let's continue your learning journey</p>
    </div>

    <!-- Quick Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <i class="fas fa-handshake"></i>
            <h3><?php echo mysqli_num_rows($connections_result); ?></h3>
            <p>Active Connections</p>
        </div>
        <div class="stat-card">
            <i class="fas fa-share-alt"></i>
            <h3><?php echo $skills_shared_count; ?></h3>
            <p>Skills Shared</p>
        </div>
        <div class="stat-card">
            <i class="fas fa-inbox"></i>
            <h3><?php echo $new_messages_count; ?></h3>
            <p>New Messages</p>
        </div>
    </div>

    <!-- Quick Actions - Now properly centered -->
    <div class="quick-actions">
        <button class="action-btn" onclick="location.href='post_skill.php'">
            <i class="fas fa-plus-circle"></i> Post Skill
        </button>
        <button class="action-btn" onclick="location.href='search.php'">
            <i class="fas fa-search"></i> Find Learners
        </button>
        <button class="action-btn" onclick="location.href='inbox.php'">
            <i class="fas fa-inbox"></i> Check Inbox
        </button>
    </div>

    <!-- Connections Section -->
    <section class="dashboard-section">
        <h2><i class="fas fa-users"></i> Your Connections</h2>
        <div class="connections-grid">
            <?php if (mysqli_num_rows($connections_result) > 0): ?>
                <?php while ($connection = mysqli_fetch_assoc($connections_result)): ?>
                    <div class="connection-card">
                        <div class="avatar"><?php echo strtoupper(substr($connection['name'], 0, 1)); ?></div>
                        <h4><?php echo htmlspecialchars($connection['name']); ?></h4>
                        <p class="connection-date">Connected: <?php echo date('M Y', strtotime($connection['connected_since'])); ?></p>
                        <button class="chat-btn" onclick="location.href='chat.php?user=<?php echo $connection['id']; ?>'">
                            <i class="fas fa-comment-dots"></i> Chat
                        </button>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <p>No connections yet! Start by exploring skills:</p>
                    <button onclick="location.href='search.php'">Browse Skills</button>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Recent Skills Section -->
    <section class="dashboard-section">
        <h2><i class="fas fa-clock"></i> Recently Shared Skills</h2>
        <div class="skills-grid">
            <?php
            // Fetch recent skills with user information
            $recent_skills_sql = "
                SELECT skills.*, users.name as user_name 
                FROM skills 
                JOIN users ON skills.user_id = users.id 
                ORDER BY skills.created_at DESC 
                LIMIT 6
            ";
            $recent_skills_result = mysqli_query($conn, $recent_skills_sql);
            
            if ($recent_skills_result && mysqli_num_rows($recent_skills_result) > 0): ?>
                <?php while ($skill = mysqli_fetch_assoc($recent_skills_result)): ?>
                    <div class="skill-card">
                        <img src="../uploads/<?php echo htmlspecialchars($skill['skill_img']); ?>" alt="<?php echo htmlspecialchars($skill['title']); ?>">
                        <div class="skill-content">
                            <h4><?php echo htmlspecialchars($skill['title']); ?></h4>
                            <p><?php echo substr(htmlspecialchars($skill['description']), 0, 100) . '...'; ?></p>
                            <div class="skill-meta">
                                <span style="color: #7f8c8d; font-size: 0.85em;">
                                    <i class="fas fa-user"></i> Shared by: <strong><?php echo htmlspecialchars($skill['user_name']); ?></strong>
                                </span>
                                <span class="skill-type type-<?php echo $skill['type']; ?>" style="font-size: 0.8em; margin-left: 10px;">
                                    <?php echo ucfirst($skill['type']); ?>
                                </span>
                            </div>
                            <div style="margin-top: 8px;">
                                <span style="color: #95a5a6; font-size: 0.8em;">
                                    <i class="fas fa-tag"></i> <?php echo htmlspecialchars($skill['category']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-lightbulb" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                    <h3>No skills shared yet</h3>
                    <p>Be the first to share a skill with the community!</p>
                    <button onclick="location.href='post_skill.php'">Post Your First Skill</button>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="dashboard-section">
        <h2><i class="fas fa-star"></i> Recommended Skills</h2>
        <div id="featured-skills" class="skills-grid">
            <!-- Skills will be populated by JavaScript -->
        </div>
    </section>
    
    <section class="dashboard-section">
        <h2><i class="fas fa-history"></i> Recent Activity</h2>
        <div id="recent-activity" class="activity-list">
            <!-- Activities will be populated by JavaScript -->
        </div>
        <form id="add-activity" class="activity-form">
            <input type="text" placeholder="Add custom note..." required>
            <button type="submit"><i class="fas fa-plus"></i> Add Note</button>
        </form>
    </section>

    <section class="dashboard-section">
        <h2><i class="fas fa-lightbulb"></i> Learning Resources</h2>
        <div class="resources-grid">
            <div class="resource-card">
                <h4><i class="fas fa-video"></i> Tutorial Videos</h4>
                <p>Watch our beginner-friendly tutorials</p>
                <a href="https://www.geeksforgeeks.org/courses?source=google&medium=cpc&device=c&keyword=gfg&matchtype=p&campaignid=20039445781&adgroup=147845288105&gbraid=0AAAAAC9yBkAEwhpJZHeMh-l1liWrNrKie" target="_blank"><button class="resource-btn">Video Tutorial</button></a>
            </div>
            <div class="resource-card">
                <h4><i class="fas fa-book"></i> Documentation</h4>
                <p>Explore our comprehensive guides</p>
                <a href="https://developer.mozilla.org/en-US/" target="_blank"><button class="resource-btn">Read Docs</button></a>
            </div>
        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Featured Skills Logic with Local Storage
    const skillsContainer = document.getElementById('featured-skills');
    const defaultSkills = [
        {
            id: 'web-dev-1',
            img: "../uploads/web_dev.jpg",
            alt: "Web Development Concept Image",
            title: "Modern Web Development",
            description: "Master HTML5, CSS3, Flexbox, Grid, and modern JavaScript frameworks.",
            learners: 112,
            interested: false
        },
        {
            id: 'guitar-2',
            img: "../uploads/guitar.webp",
            alt: "Acoustic Guitar",
            title: "Acoustic Guitar Basics",
            description: "Learn essential chords, strumming patterns, and your first few songs.",
            learners: 85,
            interested: false
        },
        {
            id: 'data-analysis-3',
            img: "../uploads/da.png",
            alt: "Data Analysis Charts",
            title: "Introduction to Data Analysis",
            description: "Understand data concepts, basic statistics, and tools like Excel or Python Pandas.",
            learners: 98,
            interested: false
        },
        {
            id: 'graphic-design-4',
            img: "../uploads/graphic_des.jpg",
            alt: "Graphic Design Tools",
            title: "Graphic Design Fundamentals",
            description: "Explore principles of design, color theory, typography, and layout techniques.",
            learners: 76,
            interested: false
        },
        {
            id: 'cooking-5',
            img: "../uploads/cooking.jpeg",
            alt: "Cooking Ingredients",
            title: "Basic Culinary Skills",
            description: "Learn essential knife skills, cooking methods, and how to follow recipes.",
            learners: 55,
            interested: false
        }
    ];

    let storedSkills = localStorage.getItem('featuredSkills');
    let skills = storedSkills ? JSON.parse(storedSkills) : defaultSkills;

    function renderSkills() {
        skillsContainer.innerHTML = skills.map(skill => `
            <div class="skill-card ${skill.interested ? 'interested' : ''}">
                <img src="${skill.img}" alt="${skill.alt}">
                <div class="skill-content">
                    <h4>${skill.title}</h4>
                    <p>${skill.description}</p>
                    <div class="skill-meta">
                        <span><i class="fas fa-users"></i> ${skill.learners + (skill.interested ? 1 : 0)} learners</span>
                        <button class="interest-btn ${skill.interested ? 'active' : ''}" data-skill-id="${skill.id}">
                            ${skill.interested ? 'Interested' : 'Express Interest'}
                        </button>
                    </div>
                </div>
            </div>
        `).join('');

        // Add event listeners to the "Express Interest" buttons
        const interestButtons = document.querySelectorAll('.interest-btn');
        interestButtons.forEach(button => {
            button.addEventListener('click', function() {
                const skillId = this.dataset.skillId;
                const skillIndex = skills.findIndex(skill => skill.id === skillId);
                if (skillIndex !== -1) {
                    skills[skillIndex].interested = !skills[skillIndex].interested;
                    localStorage.setItem('featuredSkills', JSON.stringify(skills));
                    renderSkills(); // Re-render to update the UI
                }
            });
        });
    }

    renderSkills();

    // LocalStorage for Recent Activity
    const activityList = document.getElementById('recent-activity');
    let activities = JSON.parse(localStorage.getItem('dashboardActivities') || '[]');

    function renderActivities() {
        activityList.innerHTML = activities.map(activity => `
            <div class="activity-item">
                <i class="fas fa-sticky-note"></i>
                <div>
                    <p>${activity.text}</p>
                    <small>${new Date(activity.date).toLocaleString()}</small>
                </div>
            </div>
        `).join('');
    }

    renderActivities();

    // Add new activity
    document.getElementById('add-activity').addEventListener('submit', (e) => {
        e.preventDefault();
        const noteInput = e.target.querySelector('input');
        const text = noteInput.value.trim();
        if (text) {
            const newActivity = {
                text,
                date: new Date().toISOString()
            };
            activities.unshift(newActivity);
            localStorage.setItem('dashboardActivities', JSON.stringify(activities));
            renderActivities();
            noteInput.value = ''; // Clear the input field
        }
    });
});
</script>

<?php require_once('../includes/footer.php'); ?>
</body>
</html>
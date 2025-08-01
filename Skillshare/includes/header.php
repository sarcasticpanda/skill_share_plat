<?php
// Get the filename of the current script to set the active class
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>SkillShare - Connect & Learn</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Reset and base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            background-color: #f5f7fa;
            color: #333;
            display: flex;
            flex-direction: column;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Elegant and Modern Header Styles */
        .site-header {
            background-color:rgb(246, 248, 250); /* Light background */
            color: #333; /* Dark text */
            padding: 20px 0;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05); /* Subtle shadow */
            position: sticky;
            top: 0;
            z-index: 100;
            border-bottom: 1px solid #e0e0e0; /* Light border */
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 60px; /* Increased height for better spacing */
        }

        .logo {
            font-size: 28px; /* Larger logo */
            font-weight: bold;
            color: #333;
            text-decoration: none;
            letter-spacing: -0.5px; /* Slightly tighter letter spacing */
        }

        .logo span {
            color: #db2b80; /* Modern accent color */
        }

        /* Modern Navigation Styles */
        .main-nav {
            display: flex;
            gap: 25px; /* Increased gap */
            height: 100%;
            align-items: center;
        }

        .main-nav a {
            color: #212529; /* Slightly softer text color */
            text-decoration: none;
            padding: 10px 15px; /* Increased padding */
            border-radius: 6px; /* More rounded corners */
            transition: background-color 0.3s ease, color 0.3s ease;
            font-weight: 500;
            display: flex;
            align-items: center;
            height: 40px; /* Adjusted height */
        }

        .main-nav a:hover {
            background-color:rgb(190, 202, 213); /* Light hover background */
            color: #db2b80; /* Modern hover color */
        }

        .main-nav a.active {
            background-color: #db2b80; /* Modern active background */
            color: white;
        }

        /* Main content container */
        .main-content {
            flex: 1 1 auto;
            padding: 30px 0; /* Increased top and bottom padding */
        }

        /* Footer styles */
        .site-footer {
            background-color:rgb(102, 106, 112); /* Darker footer */
            color: #f8f9fa; /* Light footer text */
            padding: 20px 0;
            text-align: center;
            margin-top: 40px; /* Increased margin */
            box-shadow: 0 -4px 12px rgba(0,0,0,0.05); /* Subtle shadow */
            flex-shrink: 0;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                text-align: center;
                height: auto;
                padding: 15px 0;
            }

            .main-nav {
                margin-top: 20px;
                flex-wrap: wrap;
                justify-content: center;
                gap: 15px;
            }

            .main-nav a {
                margin: 5px;
                padding: 8px 12px;
                height: auto;
            }

            .logo {
                font-size: 26px;
            }
        }
    </style>
</head>
<body>
    <header class="site-header">
        <div class="container header-content">
            <a href="../pages/dashboard.php" class="logo">Skill<span>Share</span></a>
            <nav class="main-nav">
                <a href="../pages/dashboard.php" class="<?php echo ($currentPage == 'dashboard.php') ? 'active' : ''; ?>">Dashboard</a>
                <a href="../pages/post_skill.php" class="<?php echo ($currentPage == 'post_skill.php') ? 'active' : ''; ?>">Post Skill</a>
                <a href="../pages/my_skills.php" class="<?php echo ($currentPage == 'my_skills.php') ? 'active' : ''; ?>">My Skills</a>
                <a href="../pages/inbox.php" class="<?php echo ($currentPage == 'inbox.php') ? 'active' : ''; ?>">Inbox</a>
                <a href="../pages/chat.php" class="<?php echo ($currentPage == 'chat.php') ? 'active' : ''; ?>">Chat</a>
                <a href="../pages/search.php" class="<?php echo ($currentPage == 'search.php') ? 'active' : ''; ?>">Search</a>
                <a href="../includes/logout.php">Logout</a>
            </nav>
        </div>
    </header>

    <div class="main-content">
        <div class="container">
            
    </div>

   

</body>
</html>
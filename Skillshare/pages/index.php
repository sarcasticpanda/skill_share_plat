<?php
session_start();

require_once('../includes/db.php');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Fetch total number of users
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $totalUsers = $stmt->fetchColumn();
} catch (PDOException $e) {
    error_log("Error fetching user count: " . $e->getMessage());
    $totalUsers = 0;
}

// Fetch total number of skills shared (counting both offers and requests)
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM skills");
    $totalSkills = $stmt->fetchColumn();
} catch (PDOException $e) {
    error_log("Error fetching skills count: " . $e->getMessage());
    $totalSkills = 0;
}

// Fetch total number of connections (counting distinct user pairs in messages)
try {
    $stmt = $pdo->query("SELECT COUNT(DISTINCT LEAST(from_id, to_id), GREATEST(from_id, to_id)) FROM messages");
    $totalConnections = $stmt->fetchColumn();
} catch (PDOException $e) {
    error_log("Error fetching connections count: " . $e->getMessage());
    $totalConnections = 0;
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkillShare - Share & Learn Skills</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2d3e50;
            --primary-dark: #230c27;
            --secondary:rgb(201, 55, 116);
            --accent:rgb(239, 72, 108);
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --light-gray: #e9ecef;
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            color: var(--dark);
            line-height: 1.6;
            overflow-x: hidden;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header */
        header {
            background-color: rgba(255, 255, 255, 0.95);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: fixed;
            width: 100%;
            z-index: 1000;
            transition: var(--transition);
        }

        header.scrolled {
            background-color: rgba(255, 255, 255, 0.98);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem 1.5rem;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
            display: flex;
            align-items: center;
            margin-left: 1.5rem;
        }

        .logo i {
            margin-right: 10px;
            color: var(--accent);
        }

        nav a {
            color: var(--dark);
            text-decoration: none;
            margin-left: 1.5rem;
            font-weight: 500;
            transition: var(--transition);
            position: relative;
            margin-left:1.5 rem;
        }

        nav a:hover {
            color: var(--primary);
        }

        nav a:after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            background: var(--primary);
            bottom: -5px;
            left: 0;
            transition: var(--transition);
        }

        nav a:hover:after {
            width: 100%;
        }

        .cta-button {
            background-color: var(--primary);
            color: white;
            padding: 0.6rem 1.2rem;
            border-radius: 50px;
            font-weight: 500;
            transition: var(--transition);
            margin-left: 1.5rem;
        }

        .cta-button:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
            color: white;
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg,rgb(238, 67, 184) 0%,rgb(191, 33, 46) 100%);
            color: white;
            padding: 10rem 0 6rem;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('https://images.pexels.com/photos/301703/pexels-photo-301703.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2') no-repeat center center/cover;
            opacity: 0.15;
            z-index: 0;
        }

        .hero-content {
            position: relative;
            z-index: 1;
            max-width: 600px;
        }

        .hero h2 {
            font-size: 3rem;
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }

        .hero p {
            font-size: 1.1rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .btn-primary {
            display: inline-block;
            background-color: white;
            color: var(--primary);
            padding: 0.8rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            background-color: var(--light);
        }

        /* Features Section */
        .features {
            padding: 6rem 0;
            background-color: var(--light);
        }

        .section-title {
            text-align: center;
            margin-bottom: 3rem;
        }

        .section-title h2 {
            font-size: 2.5rem;
            color: var(--dark);
            position: relative;
            display: inline-block;
        }

        .section-title h2:after {
            content: '';
            position: absolute;
            width: 50%;
            height: 4px;
            background: var(--primary);
            bottom: -10px;
            left: 25%;
            border-radius: 2px;
        }

        .feature-boxes {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .feature {
            background: white;
            padding: 2.5rem;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            transition: var(--transition);
            text-align: center;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .feature:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }

        .feature::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: var(--primary);
            z-index: 2;
        }

        .feature i {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 1.5rem;
            background: rgba(67, 97, 238, 0.1);
            width: 80px;
            height: 80px;
            line-height: 80px;
            border-radius: 50%;
            display: inline-block;
            transition: var(--transition);
        }

        .feature:hover i {
            background: var(--primary);
            color: white;
            transform: rotate(10deg) scale(1.1);
        }

        .feature h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: var(--dark);
        }

        .feature p {
            color: var(--gray);
        }

        /* Stats Section */
        .stats {
            padding: 4rem 0;
            background: linear-gradient(135deg,rgb(213, 51, 99) 0%,rgb(228, 45, 60) 100%);
            color: white;
            text-align: center;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
        }

        .stat-item {
            padding: 2rem;
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        /* Testimonials */
        .testimonials {
            padding: 6rem 0;
            background-color: white;
        }

        .testimonial-slider {
            max-width: 800px;
            margin: 0 auto;
            position: relative;
        }

        .testimonial {
            background: var(--light);
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            text-align: center;
            display: none;
        }

        .testimonial.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .testimonial-content {
            font-style: italic;
            margin-bottom: 1.5rem;
            color: var(--dark);
        }

        .testimonial-author {
            font-weight: 600;
            color: var(--primary);
        }

        .testimonial-role {
            color: var(--gray);
            font-size: 0.9rem;
        }

        .testimonial-nav {
            display: flex;
            justify-content: center;
            margin-top: 2rem;
        }

        .testimonial-dot {
            width: 12px;
            height: 12px;
            background: var(--light-gray);
            border-radius: 50%;
            margin: 0 5px;
            cursor: pointer;
            transition: var(--transition);
        }

        .testimonial-dot.active {
            background: var(--primary);
            transform: scale(1.2);
        }

        /* CTA Section */
        .cta-section {
            padding: 6rem 0;
            background: linear-gradient(rgba(226, 42, 51, 0.9), rgba(228, 51, 107, 0.9)), url('https://images.pexels.com/photos/1925536/pexels-photo-1925536.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2') no-repeat center center/cover;
            color: white;
            text-align: center;
        }

        .cta-section h2 {
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
        }

        .cta-section p {
            max-width: 600px;
            margin: 0 auto 2rem;
            opacity: 0.9;
        }

        .btn-secondary {
            display: inline-block;
            background: transparent;
            color: white;
            padding: 0.8rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition);
            border: 2px solid white;
        }

        .btn-secondary:hover {
            background: white;
            color: var(--primary);
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        /* Footer */
        footer {
            background-color: var(--dark);
            color: var(--light);
            padding: 3rem 0 1.5rem;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .footer-column h3 {
            color: white;
            margin-bottom: 1.5rem;
            font-size: 1.2rem;
        }

        .footer-column ul {
            list-style: none;
        }

        .footer-column ul li {
            margin-bottom: 0.8rem;
        }

        .footer-column ul li a {
            color: var(--gray);
            text-decoration: none;
            transition: var(--transition);
        }

        .footer-column ul li a:hover {
            color: white;
            padding-left: 5px;
        }

        .social-links {
            display: flex;
            gap: 1rem;
        }

        .social-links a {
            color: white;
            background: rgba(255, 255, 255, 0.1);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }

        .social-links a:hover {
            background: var(--primary);
            transform: translateY(-3px);
        }

        .footer-bottom {
            text-align: center;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--gray);
            font-size: 0.9rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-container {
                flex-direction: column;
                padding: 1rem 0;
            }

            .logo {
                margin-bottom: 1rem;
            }

            nav {
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
            }

            nav a {
                margin: 0.5rem;
            }

            .hero {
                padding: 8rem 0 4rem;
                text-align: center;
            }

            .hero h2 {
                font-size: 2.2rem;
            }

            .section-title h2 {
                font-size: 2rem;
            }
        }

        /* Animation */
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }

        .floating {
            animation: float 3s ease-in-out infinite;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header id="header">
        <div class="header-container">
            <a href="index.php" class="logo">
                <i class="fas fa-hands-helping"></i> SkillShare
            </a>
            <nav>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="dashboard.php">Dashboard</a>
                <?php endif; ?>
                <a   href="#features" class="cta-button">Explore</a>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h2>Connect. Share. Grow Together.</h2>
                <p>Join a vibrant community where knowledge flows freely. Share your expertise or learn new skills from passionate individuals. SkillConnect bridges the gap between learners and mentors.</p>
                <div class="hero-buttons">
                    <a href="register.php" class="btn-primary">Get Started - It's Free</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="container">
            <div class="section-title">
                <h2>Why Choose SkillConnect?</h2>
            </div>
            <div class="feature-boxes">
                <div class="feature">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <h3>Share Your Expertise</h3>
                    <p>Earn recognition by teaching what you know. Help others grow while building your personal brand.</p>
                </div>
                <div class="feature">
                    <i class="fas fa-lightbulb"></i>
                    <h3>Learn New Skills</h3>
                    <p>Discover passionate mentors ready to guide you in your learning journey across countless topics.</p>
                </div>
                <div class="feature">
                    <i class="fas fa-users"></i>
                    <h3>Build Connections</h3>
                    <p>Form meaningful relationships with like-minded individuals who share your passions and interests.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats">
        <div class="container">
            <div class="stats-container">
                <div class="stat-item">
                    <div class="stat-number" id="users-count">7+</div>
                    <div class="stat-label">Active Members</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" id="skills-count">4+</div>
                    <div class="stat-label">Skills Shared</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" id="connections-count">10+</div>
                    <div class="stat-label">Connections Made</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" id="countries-count">50+</div>
                    <div class="stat-label">Countries</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="testimonials">
        <div class="container">
            <div class="section-title">
                <h2>What Our Members Say</h2>
            </div>
            <div class="testimonial-slider">
                <div class="testimonial active">
                    <p class="testimonial-content">"SkillConnect transformed how I learn. I found a Web development mentor who guided me and I also could connect to other people with vast skill set!"</p>
                    <div class="testimonial-author">Himanshu Gupta</div>
                    <div class="testimonial-role">Developer, Uttar Pradesh</div>
                </div>
                <div class="testimonial">
                    <p class="testimonial-content">"Teaching web development on SkillConnect has been incredibly rewarding. I've met amazing people and even found new clients for my freelance business."</p>
                    <div class="testimonial-author">Saubhagya Kashyap</div>
                    <div class="testimonial-role">Web Developer, Uttar Pradesh</div>
                </div>
                <div class="testimonial">
                    <p class="testimonial-content">"As a language enthusiast, I've learned Java and Python through SkillConnect. The community is supportive and passionate."</p>
                    <div class="testimonial-author">Pranavi N.</div>
                    <div class="testimonial-role">Developer, Telangana</div>
                </div>
                <div class="testimonial-nav">
                    <div class="testimonial-dot active" data-slide="0"></div>
                    <div class="testimonial-dot" data-slide="1"></div>
                    <div class="testimonial-dot" data-slide="2"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <h2>Ready to Start Your Learning Journey?</h2>
            <p>Join thousands of members who are already sharing skills and building meaningful connections.</p>
            <a href="register.php" class="btn-secondary">Join Now - It's Free</a>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3>SkillConnect</h3>
                    <p>Empowering individuals through knowledge sharing and community building since 2023.</p>
                </div>
                <div class="footer-column">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php">Register</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Contact Us</h3>
                    <ul>
                        <li><i class="fas fa-envelope"></i> skillconnect@gmail.com</li>
                        <li><i class="fas fa-phone"></i> +91 808 182 4884</li>
                        <li><i class="fas fa-map-marker-alt"></i> Srm University, Andha Pradesh</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date("Y"); ?> SkillConnect. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
         const totalUsers = <?php echo json_encode($totalUsers); ?>;
        const totalSkills = <?php echo json_encode($totalSkills); ?>;
        const totalConnections = <?php echo json_encode($totalConnections); ?>;
// Header scroll effect
        window.addEventListener('scroll', function() {
            const header = document.getElementById('header');
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });

        // Testimonial slider
        const testimonials = document.querySelectorAll('.testimonial');
        const dots = document.querySelectorAll('.testimonial-dot');
        let currentSlide = 0;

        function showSlide(index) {
            testimonials.forEach(testimonial => testimonial.classList.remove('active'));
            dots.forEach(dot => dot.classList.remove('active'));
            
            testimonials[index].classList.add('active');
            dots[index].classList.add('active');
            currentSlide = index;
        }

        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => showSlide(index));
        });

        // Auto slide change
        setInterval(() => {
            let nextSlide = (currentSlide + 1) % testimonials.length;
            showSlide(nextSlide);
        }, 5000);

        // Animate stats counting
        function animateValue(id, start, end, duration) {
            let obj = document.getElementById(id);
            let range = end - start;
            let minTimer = 50;
            let stepTime = Math.abs(Math.floor(duration / range));
            stepTime = Math.max(stepTime, minTimer);
            
            let startTime = new Date().getTime();
            let endTime = startTime + duration;
            let timer;
            
            function run() {
                let now = new Date().getTime();
                let remaining = Math.max((endTime - now) / duration, 0);
                let value = Math.round(end - (remaining * range));
                obj.innerHTML = value.toLocaleString() + '+';
                if (value == end) {
                    clearInterval(timer);
                }
            }
            
            timer = setInterval(run, stepTime);
            run();
        }

        // Start counting when stats section is in view
        function startCountingWhenVisible() {
            const statsSection = document.querySelector('.stats');
            const position = statsSection.getBoundingClientRect().top;
            const screenPosition = window.innerHeight / 1.3;
            
            if(position < screenPosition) {
                const usersCount = $totalUsers === 'Error' ? 0 : parseInt(totalUsers);
                const skillsCount = $totalSkills === 'Error' ? 0 : parseInt(totalSkills);
                const connectionsCount = $totalConnections === 'Error' ? 0 : parseInt(totalConnections);

                animateValue('users-count', 0, usersCount, 2000);
                animateValue('skills-count', 0, skillsCount, 1500);
                animateValue('connections-count', 0, connectionsCount, 2000);
                animateValue('countries-count', 0, 50, 1000); // Keep the static value for countries if it's not dynamic
                window.removeEventListener('scroll', startCountingWhenVisible);
            }
        }

        window.addEventListener('scroll', startCountingWhenVisible);
    </script>
</body>
</html>
<?php session_start(); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - SkillConnect</title>
    <link rel="stylesheet" href="../assets/css/login.css">
    <style>
        body::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    width: 100%;
    background: url('https://images.pexels.com/photos/10526880/pexels-photo-10526880.jpeg') no-repeat center center/cover;
    opacity: 0.9; /* Adjust the background image opacity here */
    z-index: -1;
}
        </style>
</head>
<body>
    <div class="login-container">
        <h2>Create Your Account </h2>
        <p>Join the SkillShare network to learn and share</p>

        <?php if (isset($_SESSION['error'])): ?>
            <p style="color: red;"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <p style="color: green;"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
        <?php endif; ?>

        <form method="POST" action="../includes/auth.php">
            <label for="name">Full Name</label>
            <input type="text" name="name" required placeholder="Enter your full name">

            <label for="email">Email</label>
            <input type="email" name="email" required placeholder="Enter your email">

            <label for="password">Password</label>
            <input type="password" name="password" required placeholder="Create a password">

            <button type="submit" name="register">Register</button>
        </form>

        <p class="register-link">Already have an account? <a href="login.php">Login here</a></p>
    </div>
</body>
</html>

<?php
session_start();
require_once('db.php');

// Registration logic
if (isset($_POST['register'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check_query = "SELECT id FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        $_SESSION['error'] = "Email already exists!";
        header("Location: ../pages/register.php");
        exit();
    }

    $query = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sss", $name, $email, $password);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = "Registration successful! Please login.";
        header("Location: ../pages/login.php");
        exit();
    } else {
        $_SESSION['error'] = "Registration failed. Please try again.";
        header("Location: ../pages/register.php");
        exit();
    }
}

// Login logic
if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password_input = $_POST['password'];

    $query = "SELECT id, name, password FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($user = mysqli_fetch_assoc($result)) {
        if (password_verify($password_input, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            header("Location: ../pages/dashboard.php");
            exit();
        } else {
            $_SESSION['error'] = "Invalid email or password.";
            header("Location: ../pages/login.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "Invalid email or password.";
        header("Location: ../pages/login.php");
        exit();
    }
}

// Logout logic (optional usage: auth.php?logout=true)
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ../pages/login.php");
    exit();
}

// Session check logic for protected pages
if (basename($_SERVER['PHP_SELF']) != 'login.php' && basename($_SERVER['PHP_SELF']) != 'register.php') {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../pages/login.php");
        exit();
    }
}

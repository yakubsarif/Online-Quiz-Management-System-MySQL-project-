<?php
session_start();
require_once 'db.php';

// Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function is_admin() {
    return isset($_SESSION['is_admin']) && ($_SESSION['is_admin'] === true || $_SESSION['is_admin'] === 1 || $_SESSION['is_admin'] === '1');
}

// Require login for protected pages
function require_login() {
    if (!is_logged_in()) {
        redirect('login.php');
    }
}

// Require admin access for admin pages
function require_admin() {
    require_login();
    if (!is_admin()) {
        redirect('index.php');
    }
}

// Get current user data
function get_current_user_data() {
    if (!is_logged_in()) {
        return null;
    }
    
    global $pdo;
    $stmt = $pdo->prepare("SELECT id, name, email, is_admin FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// Login user
function login_user($user_id, $name, $is_admin = false) {
    $_SESSION['user_id'] = $user_id;
    $_SESSION['name'] = $name;
    $_SESSION['is_admin'] = $is_admin;
}

// Logout user
function logout_user() {
    session_destroy();
    redirect('login.php');
}
?> 
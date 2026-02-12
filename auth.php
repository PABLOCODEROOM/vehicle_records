<?php
// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'config.php';

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Redirect to login if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

// Get current user ID
function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Get current user data
function getUserData() {
    global $connect;
    $user_id = getUserId();
    
    if (!$user_id) return null;
    
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = mysqli_prepare($connect, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    return mysqli_fetch_assoc($result);
}

// Hash password
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

// Verify password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Login user
function loginUser($username, $password) {
    global $connect;
    
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = mysqli_prepare($connect, $sql);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($user = mysqli_fetch_assoc($result)) {
        if (verifyPassword($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            return true;
        }
    }
    return false;
}

// Register user
function registerUser($username, $email, $password, $full_name) {
    global $connect;
    
    // Check if username or email exists
    $check_sql = "SELECT id FROM users WHERE username = ? OR email = ?";
    $check_stmt = mysqli_prepare($connect, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "ss", $username, $email);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    
    if (mysqli_num_rows($check_result) > 0) {
        return false; // User exists
    }
    
    // Register new user
    $hashed_password = hashPassword($password);
    $sql = "INSERT INTO users (username, email, password, full_name) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($connect, $sql);
    mysqli_stmt_bind_param($stmt, "ssss", $username, $email, $hashed_password, $full_name);
    
    return mysqli_stmt_execute($stmt);
}

// Logout user
function logoutUser() {
    session_destroy();
    header('Location: login.php');
    exit();
}

// Create alert
function createAlert($user_id, $vehicle_id, $alert_type, $message) {
    global $connect;
    
    $sql = "INSERT INTO alerts (user_id, vehicle_id, alert_type, message) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($connect, $sql);
    mysqli_stmt_bind_param($stmt, "iiss", $user_id, $vehicle_id, $alert_type, $message);
    
    return mysqli_stmt_execute($stmt);
}

?>
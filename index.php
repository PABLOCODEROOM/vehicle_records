<?php
session_start();
include 'config.php';
include 'auth.php';

// If user is already logged in, redirect to dashboard
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
} else {
    // Redirect to login page
    header('Location: login.php');
    exit();
}
?>
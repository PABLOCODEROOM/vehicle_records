<?php
include 'auth.php';
requireLogin();

include 'config.php';
include 'functions.php';

$user_id = getUserId();
$user = getUserData();

$message = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $old_password = $_POST['old_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
        $error = "All fields are required!";
    } elseif (!verifyPassword($old_password, $user['password'])) {
        $error = "Current password is incorrect!";
    } elseif (strlen($new_password) < 6) {
        $error = "New password must be at least 6 characters!";
    } elseif ($new_password !== $confirm_password) {
        $error = "New passwords do not match!";
    } else {
        $hashed_password = hashPassword($new_password);
        $sql = "UPDATE users SET password = ? WHERE id = ?";
        $stmt = mysqli_prepare($connect, $sql);
        mysqli_stmt_bind_param($stmt, "si", $hashed_password, $user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $message = "✅ Password changed successfully!";
        } else {
            $error = "❌ Error changing password: " . mysqli_error($connect);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - VehiclePro</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">🚗 VehiclePro</div>
            <p style="font-size: 0.8rem; color: rgba(255,255,255,0.6); margin: 0;">Vehicle Management</p>
        </div>
        
        <ul class="sidebar-nav">
            <li class="sidebar-nav-item">
                <a href="dashboard.php" class="sidebar-nav-link">
                    <i class="fas fa-chart-line"></i> Dashboard
                </a>
            </li>
            <li class="sidebar-nav-item">
                <a href="vehicles.php" class="sidebar-nav-link">
                    <i class="fas fa-car"></i> Vehicles
                </a>
            </li>
            <li class="sidebar-nav-item">
                <a href="insurance.php" class="sidebar-nav-link">
                    <i class="fas fa-shield-alt"></i> Insurance
                </a>
            </li>
            <li class="sidebar-nav-item">
                <a href="payments.php" class="sidebar-nav-link">
                    <i class="fas fa-money-bill-wave"></i> Payments
                </a>
            </li>
            <li class="sidebar-nav-item">
                <a href="alerts.php" class="sidebar-nav-link">
                    <i class="fas fa-bell"></i> Alerts
                </a>
            </li>
            <li class="sidebar-nav-item">
                <a href="maintenance.php" class="sidebar-nav-link">
                    <i class="fas fa-wrench"></i> Maintenance
                </a>
            </li>

            <div class="sidebar-nav-label">Tools</div>

            <li class="sidebar-nav-item">
                <a href="export_excel.php" class="sidebar-nav-link">
                    <i class="fas fa-file-excel"></i> Export Data
                </a>
            </li>
            <li class="sidebar-nav-item">
                <a href="backup_database.php" class="sidebar-nav-link">
                    <i class="fas fa-database"></i> Backup
                </a>
            </li>

            <div class="sidebar-nav-label">Account</div>

            <li class="sidebar-nav-item">
                <a href="profile.php" class="sidebar-nav-link">
                    <i class="fas fa-user-circle"></i> Profile
                </a>
            </li>
            <li class="sidebar-nav-item">
                <a href="logout.php" class="sidebar-nav-link">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </li>
        </ul>
    </aside>

    <!-- Top Navbar -->
    <nav class="navbar">
        <div class="navbar-left">
            <div class="navbar-title">
                <h2>Change Password</h2>
            </div>
        </div>

        <div class="navbar-right">
            <div class="navbar-user">
                <div class="navbar-dropdown">
                    <div class="navbar-avatar" onclick="toggleDropdown()">
                        <?php echo substr($user['full_name'], 0, 1); ?>
                    </div>
                    <div class="dropdown-content" id="userDropdown">
                        <div style="padding: var(--spacing-md) var(--spacing-lg); border-bottom: 1px solid var(--gray-light);">
                            <div style="font-weight: 600; color: var(--dark);"><?php echo htmlspecialchars($user['full_name']); ?></div>
                            <div style="font-size: 0.85rem; color: var(--gray);"><?php echo htmlspecialchars($user['email']); ?></div>
                        </div>
                        <a href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <div style="max-width: 500px; margin: 0 auto;">
                <a href="profile.php" class="btn btn-secondary" style="margin-bottom: var(--spacing-lg);">
                    <i class="fas fa-arrow-left"></i> Back
                </a>

                <?php if ($message): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div style="text-align: center; margin-bottom: var(--spacing-xl); padding-bottom: var(--spacing-xl); border-bottom: 1px solid var(--gray-light);">
                        <div style="font-size: 3rem; margin-bottom: var(--spacing-md);">🔐</div>
                        <h2 style="margin: 0; color: var(--dark);">Change Password</h2>
                        <p style="color: var(--gray); margin: var(--spacing-sm) 0 0 0;">Update your account password</p>
                    </div>

                    <form method="POST" class="form">
                        <div class="form-group">
                            <label for="old_password">
                                <i class="fas fa-lock"></i> Current Password
                            </label>
                            <input 
                                type="password" 
                                id="old_password" 
                                name="old_password" 
                                placeholder="Enter your current password"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="new_password">
                                <i class="fas fa-lock"></i> New Password
                            </label>
                            <input 
                                type="password" 
                                id="new_password" 
                                name="new_password" 
                                placeholder="Enter your new password (at least 6 characters)"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">
                                <i class="fas fa-lock"></i> Confirm New Password
                            </label>
                            <input 
                                type="password" 
                                id="confirm_password" 
                                name="confirm_password" 
                                placeholder="Confirm your new password"
                                required
                            >
                        </div>

                        <button type="submit" class="btn btn-primary" style="width: 100%;">
                            <i class="fas fa-save"></i> Change Password
                        </button>
                    </form>
                </div>

                <div class="card" style="margin-top: var(--spacing-xl);">
                    <div class="card-header">
                        <h3 class="card-title">🔒 Password Tips</h3>
                    </div>
                    <div class="card-body">
                        <ul style="color: var(--dark); margin: 0;">
                            <li>Use at least 6 characters</li>
                            <li>Mix uppercase and lowercase letters</li>
                            <li>Include numbers and special characters</li>
                            <li>Don't use common or easy-to-guess passwords</li>
                            <li>Change your password regularly</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="js/main.js"></script>
</body>
</html>
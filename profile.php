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
    $full_name = htmlspecialchars($_POST['full_name'] ?? '');
    $email = htmlspecialchars($_POST['email'] ?? '');
    $phone = htmlspecialchars($_POST['phone'] ?? '');

    if (empty($full_name) || empty($email)) {
        $error = "Name and email are required!";
    } else {
        $sql = "UPDATE users SET full_name = ?, email = ?, phone = ? WHERE id = ?";
        $stmt = mysqli_prepare($connect, $sql);
        mysqli_stmt_bind_param($stmt, "sssi", $full_name, $email, $phone, $user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['full_name'] = $full_name;
            $user = getUserData();
            $message = "✅ Profile updated successfully!";
        } else {
            $error = "❌ Error updating profile: " . mysqli_error($connect);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - VehiclePro</title>
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
                <a href="profile.php" class="sidebar-nav-link active">
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
                <h2>User Profile</h2>
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
                            <div style="font-weight: 600; color: var(--dark);"><?php echo $user['full_name']; ?></div>
                            <div style="font-size: 0.85rem; color: var(--gray);"><?php echo $user['email']; ?></div>
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
            <div style="max-width: 600px; margin: 0 auto;">
                <a href="dashboard.php" class="btn btn-secondary" style="margin-bottom: var(--spacing-lg);">
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
                        <div style="width: 80px; height: 80px; background: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 2.5rem; margin: 0 auto; margin-bottom: var(--spacing-md);">
                            <?php echo substr($user['full_name'], 0, 1); ?>
                        </div>
                        <h2 style="margin: 0; color: var(--dark);"><?php echo htmlspecialchars($user['full_name']); ?></h2>
                        <p style="color: var(--gray); margin: var(--spacing-sm) 0 0 0;">@<?php echo htmlspecialchars($user['username']); ?></p>
                    </div>

                    <form method="POST" class="form">
                        <div class="form-group">
                            <label for="full_name">
                                <i class="fas fa-user"></i> Full Name
                            </label>
                            <input 
                                type="text" 
                                id="full_name" 
                                name="full_name" 
                                value="<?php echo htmlspecialchars($user['full_name']); ?>"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="email">
                                <i class="fas fa-envelope"></i> Email Address
                            </label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                value="<?php echo htmlspecialchars($user['email']); ?>"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="phone">
                                <i class="fas fa-phone"></i> Phone Number
                            </label>
                            <input 
                                type="tel" 
                                id="phone" 
                                name="phone" 
                                value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                                placeholder="Your phone number"
                            >
                        </div>

                        <div class="form-group">
                            <label>
                                <i class="fas fa-calendar"></i> Member Since
                            </label>
                            <input 
                                type="text" 
                                value="<?php echo formatDate($user['created_at']); ?>"
                                disabled
                                style="background-color: var(--light); cursor: not-allowed;"
                            >
                        </div>

                        <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: var(--spacing-lg);">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </form>
                </div>

                <div class="card" style="margin-top: var(--spacing-xl);">
                    <div class="card-header">
                        <h3 class="card-title">🔐 Security</h3>
                    </div>
                    <div class="card-body">
                        <p style="margin-bottom: var(--spacing-lg); color: var(--gray);">Your account is secure. Use strong passwords and enable two-factor authentication for better security.</p>
                        <a href="change_password.php" class="btn btn-secondary" style="width: 100%;">
                            <i class="fas fa-lock"></i> Change Password
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="js/main.js"></script>
</body>
</html>
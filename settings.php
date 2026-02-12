<?php
include 'auth.php';
requireLogin();

include 'config.php';
include 'functions.php';

$user_id = getUserId();
$user = getUserData();

$message = '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - VehiclePro</title>
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
                <a href="settings.php" class="sidebar-nav-link active">
                    <i class="fas fa-cog"></i> Settings
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
                <h2>Settings</h2>
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
            <a href="dashboard.php" class="btn btn-secondary" style="margin-bottom: var(--spacing-lg);">
                <i class="fas fa-arrow-left"></i> Back
            </a>

            <div style="max-width: 800px; margin: 0 auto;">
                <h1 class="page-title" style="margin-bottom: var(--spacing-2xl);">⚙️ Settings</h1>

                <!-- Account Settings -->
                <div class="card" style="margin-bottom: var(--spacing-2xl);">
                    <div class="card-header">
                        <h3 class="card-title">👤 Account Settings</h3>
                    </div>
                    <div class="card-body">
                        <div style="display: grid; gap: var(--spacing-lg);">
                            <div style="padding: var(--spacing-lg); background-color: var(--light); border-radius: var(--radius-md); display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <div style="font-weight: 600; color: var(--dark);">Username</div>
                                    <div style="color: var(--gray); font-size: 0.9rem;">@<?php echo htmlspecialchars($user['username']); ?></div>
                                </div>
                                <span style="background-color: var(--primary); color: white; padding: 4px 12px; border-radius: 20px; font-size: 0.85rem;">Active</span>
                            </div>

                            <div style="padding: var(--spacing-lg); background-color: var(--light); border-radius: var(--radius-md); display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <div style="font-weight: 600; color: var(--dark);">Email</div>
                                    <div style="color: var(--gray); font-size: 0.9rem;"><?php echo htmlspecialchars($user['email']); ?></div>
                                </div>
                                <a href="profile.php" class="btn btn-sm btn-primary">Edit</a>
                            </div>

                            <div style="padding: var(--spacing-lg); background-color: var(--light); border-radius: var(--radius-md); display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <div style="font-weight: 600; color: var(--dark);">Password</div>
                                    <div style="color: var(--gray); font-size: 0.9rem;">Last changed: Never</div>
                                </div>
                                <a href="change_password.php" class="btn btn-sm btn-primary">Change</a>
                            </div>

                            <div style="padding: var(--spacing-lg); background-color: var(--light); border-radius: var(--radius-md); display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <div style="font-weight: 600; color: var(--dark);">Member Since</div>
                                    <div style="color: var(--gray); font-size: 0.9rem;"><?php echo formatDate($user['created_at']); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Privacy & Security -->
                <div class="card" style="margin-bottom: var(--spacing-2xl);">
                    <div class="card-header">
                        <h3 class="card-title">🔐 Privacy & Security</h3>
                    </div>
                    <div class="card-body">
                        <div style="display: grid; gap: var(--spacing-lg);">
                            <div style="padding: var(--spacing-lg); background-color: var(--light); border-radius: var(--radius-md);">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div>
                                        <div style="font-weight: 600; color: var(--dark);">Two-Factor Authentication</div>
                                        <div style="color: var(--gray); font-size: 0.9rem;">Add an extra layer of security</div>
                                    </div>
                                    <span style="background-color: #fee2e2; color: #7f1d1d; padding: 4px 12px; border-radius: 20px; font-size: 0.85rem;">Coming Soon</span>
                                </div>
                            </div>

                            <div style="padding: var(--spacing-lg); background-color: var(--light); border-radius: var(--radius-md);">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div>
                                        <div style="font-weight: 600; color: var(--dark);">Login History</div>
                                        <div style="color: var(--gray); font-size: 0.9rem;">View your recent login activity</div>
                                    </div>
                                    <span style="background-color: #fee2e2; color: #7f1d1d; padding: 4px 12px; border-radius: 20px; font-size: 0.85rem;">Coming Soon</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data Management -->
                <div class="card" style="margin-bottom: var(--spacing-2xl);">
                    <div class="card-header">
                        <h3 class="card-title">📊 Data Management</h3>
                    </div>
                    <div class="card-body">
                        <div style="display: grid; gap: var(--spacing-lg);">
                            <div style="padding: var(--spacing-lg); background-color: var(--light); border-radius: var(--radius-md);">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div>
                                        <div style="font-weight: 600; color: var(--dark);">Export Data</div>
                                        <div style="color: var(--gray); font-size: 0.9rem;">Download your data as Excel files</div>
                                    </div>
                                    <a href="export_excel.php" class="btn btn-sm btn-success">Export</a>
                                </div>
                            </div>

                            <div style="padding: var(--spacing-lg); background-color: var(--light); border-radius: var(--radius-md);">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div>
                                        <div style="font-weight: 600; color: var(--dark);">Backup Database</div>
                                        <div style="color: var(--gray); font-size: 0.9rem;">Create a backup of your database</div>
                                    </div>
                                    <a href="backup_database.php" class="btn btn-sm btn-warning">Backup</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- About -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">ℹ️ About VehiclePro</h3>
                    </div>
                    <div class="card-body">
                        <div style="color: var(--dark);">
                            <p><strong>Version:</strong> 1.0.0</p>
                            <p><strong>Currency:</strong> Tanzanian Shillings (Tsh)</p>
                            <p><strong>Database:</strong> MySQL with PHP</p>
                            <p style="color: var(--gray); margin-top: var(--spacing-lg);">VehiclePro is a professional vehicle management system designed to help you track insurance, payments, and maintenance records.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="js/main.js"></script>
</body>
</html>
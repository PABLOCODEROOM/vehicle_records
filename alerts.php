<?php
include 'auth.php';
requireLogin();

include 'config.php';
include 'functions.php';

$user_id = getUserId();
$user = getUserData();

$message = '';

// Mark alert as read
if (isset($_GET['mark_read'])) {
    $alert_id = intval($_GET['mark_read']);
    $sql = "UPDATE alerts SET is_read = 1 WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($connect, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $alert_id, $user_id);
    mysqli_stmt_execute($stmt);
    $message = "✅ Alert marked as read";
}

// Delete alert
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $sql = "DELETE FROM alerts WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($connect, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $delete_id, $user_id);
    mysqli_stmt_execute($stmt);
    $message = "✅ Alert deleted";
}

// Get all alerts
$sql = "SELECT * FROM alerts WHERE user_id = ? ORDER BY created_at DESC";
$stmt = mysqli_prepare($connect, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$alerts_result = mysqli_stmt_get_result($stmt);
$total_alerts = mysqli_num_rows($alerts_result);

// Get unread count
$unread_sql = "SELECT COUNT(*) as count FROM alerts WHERE user_id = ? AND is_read = 0";
$unread_stmt = mysqli_prepare($connect, $unread_sql);
mysqli_stmt_bind_param($unread_stmt, "i", $user_id);
mysqli_stmt_execute($unread_stmt);
$unread_result = mysqli_stmt_get_result($unread_stmt);
$unread_data = mysqli_fetch_assoc($unread_result);
$unread_count = $unread_data['count'];

// Get expiring insurances (create auto-alerts)
$expiring_sql = "SELECT i.*, v.plate_number FROM insurance i
                 JOIN vehicles v ON i.vehicle_id = v.id
                 WHERE v.user_id = ? AND i.end_date >= CURDATE() AND i.end_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                 ORDER BY i.end_date ASC";
$expiring_stmt = mysqli_prepare($connect, $expiring_sql);
mysqli_stmt_bind_param($expiring_stmt, "i", $user_id);
mysqli_stmt_execute($expiring_stmt);
$expiring_result = mysqli_stmt_get_result($expiring_stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alerts - VehiclePro</title>
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
                <a href="alerts.php" class="sidebar-nav-link active">
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
                <h2>Alerts & Notifications</h2>
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
            <div class="page-header">
                <div>
                    <h1 class="page-title">🔔 Alerts & Notifications</h1>
                    <p class="page-subtitle">Important reminders and expiration notices</p>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <!-- Stats -->
            <div class="stats-grid" style="margin-bottom: var(--spacing-2xl);">
                <div class="stat-card blue">
                    <div style="position: relative; z-index: 1;">
                        <div class="stat-icon">📢</div>
                        <div class="stat-label">Total Alerts</div>
                        <div class="stat-value"><?php echo $total_alerts; ?></div>
                    </div>
                </div>

                <div class="stat-card red">
                    <div style="position: relative; z-index: 1;">
                        <div class="stat-icon">⚠️</div>
                        <div class="stat-label">Unread</div>
                        <div class="stat-value"><?php echo $unread_count; ?></div>
                    </div>
                </div>
            </div>

            <!-- Expiring Soon Section -->
            <div class="card" style="margin-bottom: var(--spacing-xl);">
                <div class="card-header">
                    <h3 class="card-title">⏰ Insurance Expiring Soon (30 Days)</h3>
                </div>
                <div class="card-body">
                    <?php if (mysqli_num_rows($expiring_result) > 0): ?>
                        <div style="display: grid; gap: var(--spacing-lg);">
                            <?php while ($expiring = mysqli_fetch_assoc($expiring_result)): ?>
                                <?php 
                                $days = daysUntilExpiration($expiring['end_date']);
                                $status_info = getExpirationStatus($expiring['end_date']);
                                ?>
                                <div style="padding: var(--spacing-lg); background-color: var(--light); border-radius: var(--radius-md); border-left: 4px solid var(--warning);">
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <div>
                                            <div style="font-size: 1.1rem; font-weight: 600; margin-bottom: 5px;">
                                                🚗 <?php echo htmlspecialchars($expiring['plate_number']); ?>
                                            </div>
                                            <div style="color: var(--gray); font-size: 0.9rem;">
                                                Provider: <?php echo htmlspecialchars($expiring['provider_name'] ?? 'N/A'); ?> | 
                                                Expires: <?php echo formatDate($expiring['end_date']); ?>
                                            </div>
                                            <div style="margin-top: 8px;">
                                                <span class="badge badge-warning">⏳ <?php echo $days; ?> days left</span>
                                            </div>
                                        </div>
                                        <a href="edit_insurance.php?id=<?php echo $expiring['id']; ?>" class="btn btn-primary">
                                            <i class="fas fa-edit"></i> Renew
                                        </a>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p style="color: var(--gray); margin: 0;">✅ No insurance policies expiring in the next 30 days.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Alerts List -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">📋 All Alerts</h3>
                </div>
                <div class="card-body">
                    <?php if ($total_alerts > 0): ?>
                        <div style="display: grid; gap: var(--spacing-md);">
                            <?php while ($alert = mysqli_fetch_assoc($alerts_result)): ?>
                                <div style="padding: var(--spacing-lg); background-color: <?php echo $alert['is_read'] ? 'var(--light)' : '#e3f2fd'; ?>; border-radius: var(--radius-md); border-left: 4px solid var(--info); display: flex; justify-content: space-between; align-items: center;">
                                    <div>
                                        <div style="font-weight: 600; margin-bottom: 5px;">
                                            <?php echo htmlspecialchars($alert['alert_type']); ?>
                                            <?php if (!$alert['is_read']): ?>
                                                <span class="badge badge-info" style="margin-left: 10px;">NEW</span>
                                            <?php endif; ?>
                                        </div>
                                        <div style="color: var(--dark); margin-bottom: 5px;">
                                            <?php echo htmlspecialchars($alert['message']); ?>
                                        </div>
                                        <div style="font-size: 0.85rem; color: var(--gray);">
                                            <?php echo date('M d, Y H:i A', strtotime($alert['created_at'])); ?>
                                        </div>
                                    </div>
                                    <div style="display: flex; gap: var(--spacing-sm);">
                                        <?php if (!$alert['is_read']): ?>
                                            <a href="alerts.php?mark_read=<?php echo $alert['id']; ?>" class="btn btn-sm" style="background-color: var(--success); color: white;">
                                                <i class="fas fa-check"></i> Mark Read
                                            </a>
                                        <?php endif; ?>
                                        <a href="alerts.php?delete_id=<?php echo $alert['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this alert?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div style="text-align: center; padding: var(--spacing-2xl);">
                            <div style="font-size: 3rem; margin-bottom: var(--spacing-lg);">😊</div>
                            <p style="color: var(--gray);">All caught up! No alerts or notifications.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script src="js/main.js"></script>
</body>
</html>
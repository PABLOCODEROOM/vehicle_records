<?php
include 'auth.php';
requireLogin();

include 'config.php';
include 'functions.php';

$user_id = getUserId();
$user = getUserData();

$message = '';
$error = '';

// Handle delete insurance
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    // Verify ownership
    $verify_sql = "SELECT i.* FROM insurance i
                   JOIN vehicles v ON i.vehicle_id = v.id
                   WHERE i.id = ? AND v.user_id = ?";
    $verify_stmt = mysqli_prepare($connect, $verify_sql);
    mysqli_stmt_bind_param($verify_stmt, "ii", $delete_id, $user_id);
    mysqli_stmt_execute($verify_stmt);
    $verify_result = mysqli_stmt_get_result($verify_stmt);
    
    if (mysqli_num_rows($verify_result) > 0) {
        $sql = "DELETE FROM insurance WHERE id = ?";
        $stmt = mysqli_prepare($connect, $sql);
        mysqli_stmt_bind_param($stmt, "i", $delete_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $message = "✅ Insurance record deleted!";
        }
    }
}

// Get user's insurance records with vehicle info
$sql = "SELECT i.*, v.plate_number, v.owner_name 
        FROM insurance i
        JOIN vehicles v ON i.vehicle_id = v.id
        WHERE v.user_id = ? 
        ORDER BY i.end_date ASC";
$stmt = mysqli_prepare($connect, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$insurance_result = mysqli_stmt_get_result($stmt);
$total_insurance = mysqli_num_rows($insurance_result);

// Count active and expired
$active_sql = "SELECT COUNT(*) as count FROM insurance i
               JOIN vehicles v ON i.vehicle_id = v.id
               WHERE v.user_id = ? AND i.end_date >= CURDATE()";
$active_stmt = mysqli_prepare($connect, $active_sql);
mysqli_stmt_bind_param($active_stmt, "i", $user_id);
mysqli_stmt_execute($active_stmt);
$active_result = mysqli_stmt_get_result($active_stmt);
$active_data = mysqli_fetch_assoc($active_result);
$active_count = $active_data['count'];

$expired_sql = "SELECT COUNT(*) as count FROM insurance i
                JOIN vehicles v ON i.vehicle_id = v.id
                WHERE v.user_id = ? AND i.end_date < CURDATE()";
$expired_stmt = mysqli_prepare($connect, $expired_sql);
mysqli_stmt_bind_param($expired_stmt, "i", $user_id);
mysqli_stmt_execute($expired_stmt);
$expired_result = mysqli_stmt_get_result($expired_stmt);
$expired_data = mysqli_fetch_assoc($expired_result);
$expired_count = $expired_data['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insurance - VehiclePro</title>
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
                <a href="insurance.php" class="sidebar-nav-link active">
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
                <h2>Insurance Management</h2>
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
                    <h1 class="page-title">🛡️ Insurance Records</h1>
                    <p class="page-subtitle">Track and manage vehicle insurance policies</p>
                </div>
                <a href="add_insurance.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Insurance
                </a>
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
                        <div class="stat-icon">📋</div>
                        <div class="stat-label">Total Policies</div>
                        <div class="stat-value"><?php echo $total_insurance; ?></div>
                    </div>
                </div>

                <div class="stat-card green">
                    <div style="position: relative; z-index: 1;">
                        <div class="stat-icon">✅</div>
                        <div class="stat-label">Active Policies</div>
                        <div class="stat-value"><?php echo $active_count; ?></div>
                    </div>
                </div>

                <div class="stat-card red">
                    <div style="position: relative; z-index: 1;">
                        <div class="stat-icon">⚠️</div>
                        <div class="stat-label">Expired Policies</div>
                        <div class="stat-value"><?php echo $expired_count; ?></div>
                    </div>
                </div>
            </div>

            <!-- Insurance Table -->
            <?php if ($total_insurance > 0): ?>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Vehicle</th>
                                <th>Provider</th>
                                <th>Policy #</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Status</th>
                                <th>Premium</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($insurance = mysqli_fetch_assoc($insurance_result)): ?>
                                <?php $status_info = getExpirationStatus($insurance['end_date']); ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($insurance['plate_number']); ?></strong>
                                        <br>
                                        <span style="font-size: 0.85rem; color: var(--gray);"><?php echo htmlspecialchars($insurance['owner_name']); ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($insurance['provider_name'] ?? 'N/A'); ?></td>
                                    <td><code><?php echo htmlspecialchars($insurance['policy_number'] ?? 'N/A'); ?></code></td>
                                    <td><?php echo formatDate($insurance['start_date']); ?></td>
                                    <td><strong><?php echo formatDate($insurance['end_date']); ?></strong></td>
                                    <td><?php echo $status_info['text']; ?></td>
                                    <td><?php echo formatCurrency($insurance['monthly_premium'] ?? 0); ?></td>
                                    <td>
                                        <a href="edit_insurance.php?id=<?php echo $insurance['id']; ?>" class="btn btn-sm" style="background-color: var(--info); color: white; margin-right: 5px;">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="insurance.php?delete_id=<?php echo $insurance['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this insurance record?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="card">
                    <div style="text-align: center; padding: var(--spacing-2xl);">
                        <div style="font-size: 4rem; margin-bottom: var(--spacing-lg);">🛡️</div>
                        <h3 style="color: var(--gray); margin-bottom: var(--spacing-md);">No insurance records yet</h3>
                        <p style="color: var(--gray); margin-bottom: var(--spacing-lg);">Add insurance records to track your vehicle's coverage.</p>
                        <a href="add_insurance.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add First Insurance
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script src="js/main.js"></script>
</body>
</html>
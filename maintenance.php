<?php
include 'auth.php';
requireLogin();

include 'config.php';
include 'functions.php';

$user_id = getUserId();
$user = getUserData();

$message = '';

// Handle delete maintenance
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    // Verify ownership
    $verify_sql = "SELECT m.* FROM maintenance m
                   JOIN vehicles v ON m.vehicle_id = v.id
                   WHERE m.id = ? AND v.user_id = ?";
    $verify_stmt = mysqli_prepare($connect, $verify_sql);
    mysqli_stmt_bind_param($verify_stmt, "ii", $delete_id, $user_id);
    mysqli_stmt_execute($verify_stmt);
    $verify_result = mysqli_stmt_get_result($verify_stmt);
    
    if (mysqli_num_rows($verify_result) > 0) {
        $sql = "DELETE FROM maintenance WHERE id = ?";
        $stmt = mysqli_prepare($connect, $sql);
        mysqli_stmt_bind_param($stmt, "i", $delete_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $message = "✅ Maintenance record deleted!";
        }
    }
}

// Get all maintenance records
$sql = "SELECT m.*, v.plate_number FROM maintenance m
        JOIN vehicles v ON m.vehicle_id = v.id
        WHERE v.user_id = ? 
        ORDER BY m.maintenance_date DESC";
$stmt = mysqli_prepare($connect, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$maintenance_result = mysqli_stmt_get_result($stmt);
$total_maintenance = mysqli_num_rows($maintenance_result);

// Get total maintenance cost
$cost_sql = "SELECT SUM(m.cost) as total FROM maintenance m
             JOIN vehicles v ON m.vehicle_id = v.id
             WHERE v.user_id = ?";
$cost_stmt = mysqli_prepare($connect, $cost_sql);
mysqli_stmt_bind_param($cost_stmt, "i", $user_id);
mysqli_stmt_execute($cost_stmt);
$cost_result = mysqli_stmt_get_result($cost_stmt);
$cost_data = mysqli_fetch_assoc($cost_result);
$total_cost = $cost_data['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance - VehiclePro</title>
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
                <a href="maintenance.php" class="sidebar-nav-link active">
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
                <h2>Maintenance Records</h2>
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
                    <h1 class="page-title">🔧 Maintenance</h1>
                    <p class="page-subtitle">Track vehicle maintenance and service records</p>
                </div>
                <a href="add_maintenance.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Maintenance
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
                        <div class="stat-label">Total Records</div>
                        <div class="stat-value"><?php echo $total_maintenance; ?></div>
                    </div>
                </div>

                <div class="stat-card orange">
                    <div style="position: relative; z-index: 1;">
                        <div class="stat-icon">💰</div>
                        <div class="stat-label">Total Cost</div>
                        <div class="stat-value"><?php echo formatCurrency($total_cost); ?></div>
                    </div>
                </div>
            </div>

            <!-- Maintenance Table -->
            <?php if ($total_maintenance > 0): ?>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Vehicle</th>
                                <th>Type</th>
                                <th>Cost</th>
                                <th>Date</th>
                                <th>Next Due</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($maintenance = mysqli_fetch_assoc($maintenance_result)): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($maintenance['plate_number']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($maintenance['maintenance_type'] ?? 'General'); ?></td>
                                    <td><strong><?php echo formatCurrency($maintenance['cost'] ?? 0); ?></strong></td>
                                    <td><?php echo formatDate($maintenance['maintenance_date']); ?></td>
                                    <td>
                                        <?php if ($maintenance['next_due_date']): ?>
                                            <?php 
                                            $days_to_due = daysUntilExpiration($maintenance['next_due_date']);
                                            if ($days_to_due < 0): ?>
                                                <span class="badge badge-danger">⚠️ OVERDUE</span>
                                            <?php elseif ($days_to_due <= 30): ?>
                                                <span class="badge badge-warning">⏰ Soon</span>
                                            <?php else: ?>
                                                <span class="badge badge-success">✅ OK</span>
                                            <?php endif; ?>
                                            <br>
                                            <span style="font-size: 0.85rem;"><?php echo formatDate($maintenance['next_due_date']); ?></span>
                                        <?php else: ?>
                                            <span style="color: var(--gray);">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars(substr($maintenance['description'] ?? '-', 0, 50)); ?></td>
                                    <td>
                                        <a href="edit_maintenance.php?id=<?php echo $maintenance['id']; ?>" class="btn btn-sm" style="background-color: var(--info); color: white; margin-right: 5px;">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="maintenance.php?delete_id=<?php echo $maintenance['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this record?');">
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
                        <div style="font-size: 4rem; margin-bottom: var(--spacing-lg);">🔧</div>
                        <h3 style="color: var(--gray); margin-bottom: var(--spacing-md);">No maintenance records</h3>
                        <p style="color: var(--gray); margin-bottom: var(--spacing-lg);">Track vehicle maintenance and repairs.</p>
                        <a href="add_maintenance.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add First Record
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script src="js/main.js"></script>
</body>
</html>
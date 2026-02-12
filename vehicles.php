<?php
include 'auth.php';
requireLogin();

include 'config.php';
include 'functions.php';

$user_id = getUserId();
$user = getUserData();

$message = '';
$error = '';

// Handle delete vehicle
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $vehicle = getVehicleById($delete_id, $user_id);
    
    if ($vehicle) {
        $sql = "DELETE FROM vehicles WHERE id = ? AND user_id = ?";
        $stmt = mysqli_prepare($connect, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $delete_id, $user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $message = "✅ Vehicle deleted successfully!";
        } else {
            $error = "❌ Failed to delete vehicle.";
        }
    }
}

// Get all vehicles
$vehicles_result = getUserVehicles($user_id);
$total_vehicles = mysqli_num_rows($vehicles_result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicles - VehiclePro</title>
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
                <a href="vehicles.php" class="sidebar-nav-link active">
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
                <h2>Manage Vehicles</h2>
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
                        <a href="profile.php">
                            <i class="fas fa-user-circle"></i> Profile
                        </a>
                        <div class="dropdown-divider"></div>
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
                    <h1 class="page-title">🚗 My Vehicles</h1>
                    <p class="page-subtitle">Manage and track all your vehicles</p>
                </div>
                <a href="add_vehicle.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Vehicle
                </a>
            </div>

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

            <!-- Stats -->
            <div class="stats-grid" style="margin-bottom: var(--spacing-2xl);">
                <div class="stat-card blue">
                    <div style="position: relative; z-index: 1;">
                        <div class="stat-icon">🚗</div>
                        <div class="stat-label">Total Vehicles</div>
                        <div class="stat-value"><?php echo $total_vehicles; ?></div>
                    </div>
                </div>
            </div>

            <!-- Vehicles Table -->
            <?php if ($total_vehicles > 0): ?>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Plate Number</th>
                                <th>Make & Model</th>
                                <th>Year</th>
                                <th>Color</th>
                                <th>Owner</th>
                                <th>Status</th>
                                <th>Added Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($vehicle = mysqli_fetch_assoc($vehicles_result)): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($vehicle['plate_number']); ?></strong>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($vehicle['make'] ?? 'N/A'); ?> 
                                        <?php echo htmlspecialchars($vehicle['model'] ?? 'N/A'); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($vehicle['year'] ?? 'N/A'); ?></td>
                                    <td>
                                        <span style="display: inline-block; width: 20px; height: 20px; background-color: <?php echo htmlspecialchars($vehicle['color'] ?? '#999'); ?>; border-radius: 50%; border: 2px solid #ddd;"></span>
                                    </td>
                                    <td>
                                        <div><?php echo htmlspecialchars($vehicle['owner_name']); ?></div>
                                        <div style="font-size: 0.85rem; color: var(--gray);"><?php echo htmlspecialchars($vehicle['phone_number']); ?></div>
                                    </td>
                                    <td>
                                        <?php 
                                        $status_badge = [
                                            'active' => '<span class="badge badge-success">🟢 Active</span>',
                                            'inactive' => '<span class="badge badge-warning">🟡 Inactive</span>',
                                            'sold' => '<span class="badge badge-danger">🔴 Sold</span>'
                                        ];
                                        echo $status_badge[$vehicle['status']] ?? '<span class="badge badge-info">Unknown</span>';
                                        ?>
                                    </td>
                                    <td><?php echo formatDate($vehicle['created_at']); ?></td>
                                    <td>
                                        <a href="edit_vehicle.php?id=<?php echo $vehicle['id']; ?>" class="btn btn-sm" style="background-color: var(--info); color: white; margin-right: 5px;">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="vehicles.php?delete_id=<?php echo $vehicle['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this vehicle?');">
                                            <i class="fas fa-trash"></i> Delete
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
                        <div style="font-size: 4rem; margin-bottom: var(--spacing-lg);">🚗</div>
                        <h3 style="color: var(--gray); margin-bottom: var(--spacing-md);">No vehicles yet</h3>
                        <p style="color: var(--gray); margin-bottom: var(--spacing-lg);">Start by adding your first vehicle to track insurance, payments, and maintenance.</p>
                        <a href="add_vehicle.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Your First Vehicle
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script src="js/main.js"></script>
</body>
</html>
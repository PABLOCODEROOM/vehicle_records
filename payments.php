<?php
include 'auth.php';
requireLogin();

include 'config.php';
include 'functions.php';

$user_id = getUserId();
$user = getUserData();

$message = '';

// Handle delete payment
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    // Verify ownership
    $verify_sql = "SELECT p.* FROM payments p
                   JOIN vehicles v ON p.vehicle_id = v.id
                   WHERE p.id = ? AND v.user_id = ?";
    $verify_stmt = mysqli_prepare($connect, $verify_sql);
    mysqli_stmt_bind_param($verify_stmt, "ii", $delete_id, $user_id);
    mysqli_stmt_execute($verify_stmt);
    $verify_result = mysqli_stmt_get_result($verify_stmt);
    
    if (mysqli_num_rows($verify_result) > 0) {
        $sql = "DELETE FROM payments WHERE id = ?";
        $stmt = mysqli_prepare($connect, $sql);
        mysqli_stmt_bind_param($stmt, "i", $delete_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $message = "✅ Payment deleted!";
        }
    }
}

// Get all payments
$sql = "SELECT p.*, v.plate_number FROM payments p
        JOIN vehicles v ON p.vehicle_id = v.id
        WHERE v.user_id = ? 
        ORDER BY p.payment_date DESC";
$stmt = mysqli_prepare($connect, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$payments_result = mysqli_stmt_get_result($stmt);
$total_count = mysqli_num_rows($payments_result);

// Get total paid
$total_sql = "SELECT SUM(p.amount) as total FROM payments p
              JOIN vehicles v ON p.vehicle_id = v.id
              WHERE v.user_id = ?";
$total_stmt = mysqli_prepare($connect, $total_sql);
mysqli_stmt_bind_param($total_stmt, "i", $user_id);
mysqli_stmt_execute($total_stmt);
$total_result = mysqli_stmt_get_result($total_stmt);
$total_data = mysqli_fetch_assoc($total_result);
$total_amount = $total_data['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments - VehiclePro</title>
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
                <a href="payments.php" class="sidebar-nav-link active">
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
                <h2>Payment Records</h2>
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
                    <h1 class="page-title">💰 Payments</h1>
                    <p class="page-subtitle">Track all vehicle payments and expenses</p>
                </div>
                <a href="add_payment.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Payment
                </a>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <!-- Stats -->
            <div class="stats-grid" style="margin-bottom: var(--spacing-2xl);">
                <div class="stat-card orange">
                    <div style="position: relative; z-index: 1;">
                        <div class="stat-icon">💰</div>
                        <div class="stat-label">Total Paid</div>
                        <div class="stat-value"><?php echo formatCurrency($total_amount); ?></div>
                    </div>
                </div>

                <div class="stat-card blue">
                    <div style="position: relative; z-index: 1;">
                        <div class="stat-icon">📊</div>
                                                <div class="stat-label">Total Transactions</div>
                        <div class="stat-value"><?php echo $total_count; ?></div>
                    </div>
                </div>
            </div>

            <!-- Payments Table -->
            <?php if ($total_count > 0): ?>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Vehicle</th>
                                <th>Payment Type</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($payment = mysqli_fetch_assoc($payments_result)): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($payment['plate_number']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($payment['payment_type'] ?? 'General'); ?></td>
                                    <td><strong style="color: var(--success); font-size: 1.1rem;"><?php echo formatCurrency($payment['amount']); ?></strong></td>
                                    <td><?php echo formatDate($payment['payment_date']); ?></td>
                                    <td><?php echo htmlspecialchars($payment['description'] ?? '-'); ?></td>
                                    <td>
                                        <a href="edit_payment.php?id=<?php echo $payment['id']; ?>" class="btn btn-sm" style="background-color: var(--info); color: white; margin-right: 5px;">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="payments.php?delete_id=<?php echo $payment['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this payment?');">
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
                        <div style="font-size: 4rem; margin-bottom: var(--spacing-lg);">💰</div>
                        <h3 style="color: var(--gray); margin-bottom: var(--spacing-md);">No payments recorded</h3>
                        <p style="color: var(--gray); margin-bottom: var(--spacing-lg);">Start recording payments to track expenses.</p>
                        <a href="add_payment.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Record First Payment
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script src="js/main.js"></script>
</body>
</html>
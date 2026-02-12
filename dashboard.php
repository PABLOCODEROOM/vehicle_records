<?php
include 'auth.php';
requireLogin();

include 'config.php';
include 'functions.php';

$user_id = getUserId();
$user = getUserData();

// Get statistics
// Total vehicles
$vehicles_sql = "SELECT COUNT(*) as count FROM vehicles WHERE user_id = ?";
$vehicles_stmt = mysqli_prepare($connect, $vehicles_sql);
mysqli_stmt_bind_param($vehicles_stmt, "i", $user_id);
mysqli_stmt_execute($vehicles_stmt);
$vehicles_result = mysqli_stmt_get_result($vehicles_stmt);
$vehicles_data = mysqli_fetch_assoc($vehicles_result);
$total_vehicles = $vehicles_data['count'];

// Total payments
$payments_sql = "SELECT SUM(p.amount) as total FROM payments p
                 JOIN vehicles v ON p.vehicle_id = v.id
                 WHERE v.user_id = ?";
$payments_stmt = mysqli_prepare($connect, $payments_sql);
mysqli_stmt_bind_param($payments_stmt, "i", $user_id);
mysqli_stmt_execute($payments_stmt);
$payments_result = mysqli_stmt_get_result($payments_stmt);
$payments_data = mysqli_fetch_assoc($payments_result);
$total_payments = $payments_data['total'] ?? 0;

// Active insurance (not expired)
$active_sql = "SELECT COUNT(*) as count FROM insurance i
               JOIN vehicles v ON i.vehicle_id = v.id
               WHERE v.user_id = ? AND i.end_date >= CURDATE()";
$active_stmt = mysqli_prepare($connect, $active_sql);
mysqli_stmt_bind_param($active_stmt, "i", $user_id);
mysqli_stmt_execute($active_stmt);
$active_result = mysqli_stmt_get_result($active_stmt);
$active_data = mysqli_fetch_assoc($active_result);
$active_insurance = $active_data['count'];

// Expired insurance
$expired_sql = "SELECT COUNT(*) as count FROM insurance i
                JOIN vehicles v ON i.vehicle_id = v.id
                WHERE v.user_id = ? AND i.end_date < CURDATE()";
$expired_stmt = mysqli_prepare($connect, $expired_sql);
mysqli_stmt_bind_param($expired_stmt, "i", $user_id);
mysqli_stmt_execute($expired_stmt);
$expired_result = mysqli_stmt_get_result($expired_stmt);
$expired_data = mysqli_fetch_assoc($expired_result);
$expired_insurance = $expired_data['count'];

// Get recent vehicles (last 5)
$recent_vehicles_sql = "SELECT * FROM vehicles WHERE user_id = ? ORDER BY created_at DESC LIMIT 5";
$recent_vehicles_stmt = mysqli_prepare($connect, $recent_vehicles_sql);
mysqli_stmt_bind_param($recent_vehicles_stmt, "i", $user_id);
mysqli_stmt_execute($recent_vehicles_stmt);
$recent_vehicles_result = mysqli_stmt_get_result($recent_vehicles_stmt);

// Get recent payments (last 5)
$recent_payments_sql = "SELECT p.*, v.plate_number FROM payments p
                        JOIN vehicles v ON p.vehicle_id = v.id
                        WHERE v.user_id = ?
                        ORDER BY p.payment_date DESC LIMIT 5";
$recent_payments_stmt = mysqli_prepare($connect, $recent_payments_sql);
mysqli_stmt_bind_param($recent_payments_stmt, "i", $user_id);
mysqli_stmt_execute($recent_payments_stmt);
$recent_payments_result = mysqli_stmt_get_result($recent_payments_stmt);

// Get expiring insurances (next 30 days)
$expiring_sql = "SELECT i.*, v.plate_number FROM insurance i
                 JOIN vehicles v ON i.vehicle_id = v.id
                 WHERE v.user_id = ? AND i.end_date >= CURDATE() AND i.end_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                 ORDER BY i.end_date ASC LIMIT 5";
$expiring_stmt = mysqli_prepare($connect, $expiring_sql);
mysqli_stmt_bind_param($expiring_stmt, "i", $user_id);
mysqli_stmt_execute($expiring_stmt);
$expiring_insurances_result = mysqli_stmt_get_result($expiring_stmt);

// Get alerts count
$alerts_sql = "SELECT COUNT(*) as count FROM alerts WHERE user_id = ? AND is_read = 0";
$alerts_stmt = mysqli_prepare($connect, $alerts_sql);
mysqli_stmt_bind_param($alerts_stmt, "i", $user_id);
mysqli_stmt_execute($alerts_stmt);
$alerts_result = mysqli_stmt_get_result($alerts_stmt);
$alerts_data = mysqli_fetch_assoc($alerts_result);
$alerts_count = $alerts_data['count'];

// Monthly payments data for chart (all 12 months)
$monthly_sql = "SELECT 
                    MONTH(p.payment_date) as month, 
                    SUM(p.amount) as total 
                FROM payments p
                JOIN vehicles v ON p.vehicle_id = v.id
                WHERE v.user_id = ? AND YEAR(p.payment_date) = YEAR(CURDATE())
                GROUP BY MONTH(p.payment_date) 
                ORDER BY month";
$monthly_stmt = mysqli_prepare($connect, $monthly_sql);
mysqli_stmt_bind_param($monthly_stmt, "i", $user_id);
mysqli_stmt_execute($monthly_stmt);
$monthly_result = mysqli_stmt_get_result($monthly_stmt);

// Prepare data for chart
$months = [];
$amounts = [];
for ($m = 1; $m <= 12; $m++) {
    $months[] = date('M', mktime(0, 0, 0, $m));
    $amounts[] = 0;
}

while ($row = mysqli_fetch_assoc($monthly_result)) {
    $amounts[$row['month'] - 1] = floatval($row['total']);
}

// Get total maintenance cost
$maintenance_cost_sql = "SELECT SUM(m.cost) as total FROM maintenance m
                         JOIN vehicles v ON m.vehicle_id = v.id
                         WHERE v.user_id = ?";
$maintenance_cost_stmt = mysqli_prepare($connect, $maintenance_cost_sql);
mysqli_stmt_bind_param($maintenance_cost_stmt, "i", $user_id);
mysqli_stmt_execute($maintenance_cost_stmt);
$maintenance_cost_result = mysqli_stmt_get_result($maintenance_cost_stmt);
$maintenance_cost_data = mysqli_fetch_assoc($maintenance_cost_result);
$total_maintenance = $maintenance_cost_data['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - VehiclePro</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- Sidebar Navigation -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">🚗 VehiclePro</div>
            <p style="font-size: 0.8rem; color: rgba(255,255,255,0.6); margin: 0;">Vehicle Management</p>
        </div>
        
        <ul class="sidebar-nav">
            <li class="sidebar-nav-item">
                <a href="dashboard.php" class="sidebar-nav-link active">
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
                    <?php if ($alerts_count > 0): ?>
                        <span style="margin-left: auto; background: var(--danger); border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; font-size: 0.75rem;">
                            <?php echo $alerts_count; ?>
                        </span>
                    <?php endif; ?>
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
                <h2>Dashboard</h2>
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
                        <a href="profile.php">
                            <i class="fas fa-user-circle"></i> Profile
                        </a>
                        <a href="settings.php">
                            <i class="fas fa-cog"></i> Settings
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
            <!-- Welcome Banner -->
            <div style="margin-bottom: var(--spacing-2xl); padding: var(--spacing-xl); background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); border-radius: var(--radius-lg); color: white; box-shadow: var(--shadow-lg);">
                <h2 style="margin-bottom: var(--spacing-sm); color: white; font-size: 1.8rem;">👋 Welcome back, <?php echo htmlspecialchars($user['full_name']); ?>!</h2>
                <p style="color: rgba(255,255,255,0.9); margin: 0; font-size: 1.1rem;">Here's your vehicle management overview</p>
            </div>

            <!-- Key Statistics Grid -->
            <div class="stats-grid" style="margin-bottom: var(--spacing-2xl);">
                <div class="stat-card blue">
                    <div style="position: relative; z-index: 1;">
                        <div class="stat-icon">🚗</div>
                        <div class="stat-label">Total Vehicles</div>
                        <div class="stat-value"><?php echo $total_vehicles; ?></div>
                        <div class="stat-change">Recorded & Tracked</div>
                    </div>
                </div>

                <div class="stat-card green">
                    <div style="position: relative; z-index: 1;">
                        <div class="stat-icon">✅</div>
                        <div class="stat-label">Active Insurance</div>
                        <div class="stat-value"><?php echo $active_insurance; ?></div>
                        <div class="stat-change">Currently Valid</div>
                    </div>
                </div>

                <div class="stat-card red">
                    <div style="position: relative; z-index: 1;">
                        <div class="stat-icon">⚠️</div>
                        <div class="stat-label">Expired Insurance</div>
                        <div class="stat-value"><?php echo $expired_insurance; ?></div>
                        <div class="stat-change negative">Requires Action</div>
                    </div>
                </div>

                <div class="stat-card orange">
                    <div style="position: relative; z-index: 1;">
                        <div class="stat-icon">💰</div>
                        <div class="stat-label">Total Payments</div>
                        <div class="stat-value"><?php echo formatCurrency($total_payments); ?></div>
                        <div class="stat-change">Recorded</div>
                    </div>
                </div>

                <div class="stat-card info" style="border-top-color: var(--info);">
                    <div style="position: relative; z-index: 1;">
                        <div class="stat-icon">🔧</div>
                        <div class="stat-label">Maintenance Cost</div>
                        <div class="stat-value"><?php echo formatCurrency($total_maintenance); ?></div>
                        <div class="stat-change">Total Spent</div>
                    </div>
                </div>

                <div class="stat-card" style="border-top-color: #8b5cf6;">
                    <div style="position: relative; z-index: 1;">
                        <div class="stat-icon">📢</div>
                        <div class="stat-label">Unread Alerts</div>
                        <div class="stat-value"><?php echo $alerts_count; ?></div>
                        <div class="stat-change">Active Notifications</div>
                    </div>
                </div>
            </div>

            <!-- Quick Action Buttons -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: var(--spacing-lg); margin-bottom: var(--spacing-2xl);">
                <a href="add_vehicle.php" class="btn btn-primary" style="text-align: center; padding: var(--spacing-lg);">
                    <i class="fas fa-plus" style="display: block; font-size: 1.5rem; margin-bottom: 10px;"></i>
                    Add Vehicle
                </a>
                <a href="add_insurance.php" class="btn" style="background-color: var(--success); color: white; text-align: center; padding: var(--spacing-lg);">
                    <i class="fas fa-plus" style="display: block; font-size: 1.5rem; margin-bottom: 10px;"></i>
                    Add Insurance
                </a>
                <a href="add_payment.php" class="btn" style="background-color: var(--secondary); color: white; text-align: center; padding: var(--spacing-lg);">
                    <i class="fas fa-plus" style="display: block; font-size: 1.5rem; margin-bottom: 10px;"></i>
                    Record Payment
                </a>
                <a href="add_maintenance.php" class="btn" style="background-color: var(--warning); color: white; text-align: center; padding: var(--spacing-lg);">
                    <i class="fas fa-plus" style="display: block; font-size: 1.5rem; margin-bottom: 10px;"></i>
                    Add Maintenance
                </a>
            </div>

            <!-- Charts Section -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(500px, 1fr)); gap: var(--spacing-lg); margin-bottom: var(--spacing-2xl);">
                <!-- Monthly Payments Chart -->
                <div class="chart-container">
                    <div class="chart-title">📊 Monthly Payments (Tsh)</div>
                    <canvas id="paymentsChart"></canvas>
                </div>

                <!-- Insurance Status Chart -->
                <div class="chart-container">
                    <div class="chart-title">🛡️ Insurance Status Overview</div>
                    <canvas id="insuranceChart"></canvas>
                </div>
            </div>

            <!-- Data Tables Row -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(500px, 1fr)); gap: var(--spacing-lg); margin-bottom: var(--spacing-2xl);">

                <!-- Recent Vehicles -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">🚗 Recent Vehicles</h3>
                        <a href="vehicles.php" class="btn btn-sm btn-secondary">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if(mysqli_num_rows($recent_vehicles_result) > 0): ?>
                            <div style="overflow-x: auto;">
                                <table class="table" style="margin: 0;">
                                    <thead>
                                        <tr>
                                            <th>Plate</th>
                                            <th>Make</th>
                                            <th>Model</th>
                                            <th>Year</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($v = mysqli_fetch_assoc($recent_vehicles_result)): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($v['plate_number']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($v['make'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($v['model'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($v['year'] ?? 'N/A'); ?></td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div style="text-align: center; padding: var(--spacing-2xl); color: var(--gray);">
                                <p style="margin: 0;">📭 No vehicles yet</p>
                                <a href="add_vehicle.php" class="btn btn-primary" style="margin-top: var(--spacing-lg); display: inline-block;">Add Your First Vehicle</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Payments -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">💰 Recent Payments</h3>
                        <a href="payments.php" class="btn btn-sm btn-secondary">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if(mysqli_num_rows($recent_payments_result) > 0): ?>
                            <div style="overflow-x: auto;">
                                <table class="table" style="margin: 0;">
                                    <thead>
                                        <tr>
                                            <th>Plate</th>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Type</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($p = mysqli_fetch_assoc($recent_payments_result)): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($p['plate_number']); ?></td>
                                            <td><?php echo formatDate($p['payment_date']); ?></td>
                                            <td><strong style="color: var(--success);"><?php echo formatCurrency($p['amount']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($p['payment_type'] ?? 'General'); ?></td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div style="text-align: center; padding: var(--spacing-2xl); color: var(--gray);">
                                <p style="margin: 0;">📭 No payments yet</p>
                                <a href="add_payment.php" class="btn btn-primary" style="margin-top: var(--spacing-lg); display: inline-block;">Record First Payment</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Expiring Insurances Alert Section -->
            <div class="card" style="margin-bottom: var(--spacing-2xl);">
                <div class="card-header">
                    <h3 class="card-title">⏰ Insurance Expiring Soon (Next 30 Days)</h3>
                    <a href="alerts.php" class="btn btn-sm btn-warning">View All</a>
                </div>
                <div class="card-body">
                    <?php if(mysqli_num_rows($expiring_insurances_result) > 0): ?>
                        <div style="display: grid; gap: var(--spacing-md);">
                            <?php while($ins = mysqli_fetch_assoc($expiring_insurances_result)): 
                                $days = daysUntilExpiration($ins['end_date']);
                                $status_info = getExpirationStatus($ins['end_date']); ?>
                                <div style="padding: var(--spacing-lg); background-color: var(--light); border-radius: var(--radius-md); border-left: 4px solid var(--warning); display: flex; justify-content: space-between; align-items: center;">
                                    <div>
                                        <div style="font-size: 1.1rem; font-weight: 600; margin-bottom: 8px;">
                                            🚗 <?php echo htmlspecialchars($ins['plate_number']); ?>
                                        </div>
                                        <div style="color: var(--gray); font-size: 0.9rem; margin-bottom: 8px;">
                                            Expires: <strong><?php echo formatDate($ins['end_date']); ?></strong>
                                        </div>
                                        <div>
                                            <span class="badge badge-warning">⏳ <?php echo $days; ?> days remaining</span>
                                        </div>
                                    </div>
                                    <a href="edit_insurance.php?id=<?php echo $ins['id']; ?>" class="btn btn-primary">
                                        <i class="fas fa-edit"></i> Renew
                                    </a>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div style="text-align: center; padding: var(--spacing-2xl); color: var(--gray);">
                            <p style="margin: 0;">✅ No insurances expiring in the next 30 days</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Stats Summary -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">📈 Summary Statistics</h3>
                </div>
                <div class="card-body">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--spacing-lg);">
                        <div style="padding: var(--spacing-lg); background-color: var(--light); border-radius: var(--radius-md);">
                            <div style="color: var(--gray); font-size: 0.9rem; margin-bottom: 8px;">Total Insurance Records</div>
                            <div style="font-size: 2rem; font-weight: 700; color: var(--primary);"><?php echo $active_insurance + $expired_insurance; ?></div>
                        </div>

                        <div style="padding: var(--spacing-lg); background-color: var(--light); border-radius: var(--radius-md);">
                            <div style="color: var(--gray); font-size: 0.9rem; margin-bottom: 8px;">Average Payment</div>
                            <div style="font-size: 2rem; font-weight: 700; color: var(--success);">
                                <?php 
                                $count_sql = "SELECT COUNT(*) as count FROM payments p
                                             JOIN vehicles v ON p.vehicle_id = v.id
                                             WHERE v.user_id = ?";
                                $count_stmt = mysqli_prepare($connect, $count_sql);
                                mysqli_stmt_bind_param($count_stmt, "i", $user_id);
                                mysqli_stmt_execute($count_stmt);
                                $count_result = mysqli_stmt_get_result($count_stmt);
                                $count_data = mysqli_fetch_assoc($count_result);
                                $payment_count = $count_data['count'];
                                $avg_payment = $payment_count > 0 ? $total_payments / $payment_count : 0;
                                echo formatCurrency($avg_payment);
                                ?>
                            </div>
                        </div>

                        <div style="padding: var(--spacing-lg); background-color: var(--light); border-radius: var(--radius-md);">
                            <div style="color: var(--gray); font-size: 0.9rem; margin-bottom: 8px;">Total Maintenance Records</div>
                            <div style="font-size: 2rem; font-weight: 700; color: var(--warning);">
                                <?php 
                                $maint_count_sql = "SELECT COUNT(*) as count FROM maintenance m
                                                    JOIN vehicles v ON m.vehicle_id = v.id
                                                    WHERE v.user_id = ?";
                                $maint_count_stmt = mysqli_prepare($connect, $maint_count_sql);
                                mysqli_stmt_bind_param($maint_count_stmt, "i", $user_id);
                                mysqli_stmt_execute($maint_count_stmt);
                                $maint_count_result = mysqli_stmt_get_result($maint_count_stmt);
                                $maint_count_data = mysqli_fetch_assoc($maint_count_result);
                                echo $maint_count_data['count'];
                                ?>
                            </div>
                        </div>

                        <div style="padding: var(--spacing-lg); background-color: var(--light); border-radius: var(--radius-md);">
                            <div style="color: var(--gray); font-size: 0.9rem; margin-bottom: 8px;">Compliance Rate</div>
                            <div style="font-size: 2rem; font-weight: 700; color: var(--info);">
                                <?php 
                                $total_vehicles_count = $total_vehicles;
                                $compliance_rate = ($total_vehicles_count > 0) ? round(($active_insurance / $total_vehicles_count) * 100) : 0;
                                echo $compliance_rate . '%';
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Chart.js Script -->
    <script>
        // Monthly Payments Bar Chart
        const months = <?php echo json_encode($months); ?>;
        const payments = <?php echo json_encode($amounts); ?>;
        
        const ctxPay = document.getElementById('paymentsChart').getContext('2d');
        new Chart(ctxPay, {
            type: 'bar',
            data: {
                labels: months,
                datasets: [{
                    label: 'Amount (Tsh)',
                    data: payments,
                    backgroundColor: 'rgba(245, 158, 11, 0.7)',
                    borderColor: 'rgba(245, 158, 11, 1)',
                    borderWidth: 2,
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Tsh ' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Insurance Status Pie Chart
        const ctxIns = document.getElementById('insuranceChart').getContext('2d');
        new Chart(ctxIns, {
            type: 'doughnut',
            data: {
                labels: ['🟢 Active', '🔴 Expired'],
                datasets: [{
                    data: [<?php echo $active_insurance; ?>, <?php echo $expired_insurance; ?>],
                    backgroundColor: ['#10b981', '#ef4444'],
                    borderColor: ['#059669', '#dc2626'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>

    <script src="js/main.js"></script>
</body>
</html>
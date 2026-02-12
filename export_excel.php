<?php
include 'auth.php';
requireLogin();

include 'config.php';
include 'functions.php';

$user_id = getUserId();
$user = getUserData();

$message = '';
$error = '';

// Handle export request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['export_type'])) {
    
    $export_type = htmlspecialchars($_POST['export_type']);
    
    // Create CSV export (works in all Excel versions)
    $filename = "VehicleRecords_" . date('Y-m-d_H-i-s') . ".csv";
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    if ($export_type === 'all' || $export_type === 'vehicles') {
        fputcsv($output, ['VEHICLES']);
        fputcsv($output, ['ID', 'Plate Number', 'Make', 'Model', 'Year', 'Color', 'VIN', 'Owner Name', 'Phone Number', 'Purchase Date', 'Status', 'Added Date']);
        
        $sql = "SELECT id, plate_number, make, model, year, color, vin, owner_name, phone_number, purchase_date, status, created_at 
                FROM vehicles WHERE user_id = ? ORDER BY created_at DESC";
        $stmt = mysqli_prepare($connect, $sql);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($result)) {
            fputcsv($output, $row);
        }
        fputcsv($output, []);
        fputcsv($output, []);
    }
    
    if ($export_type === 'all' || $export_type === 'insurance') {
        fputcsv($output, ['INSURANCE']);
        fputcsv($output, ['ID', 'Vehicle Plate', 'Provider', 'Policy Number', 'Start Date', 'End Date', 'Coverage Type', 'Monthly Premium (Tsh)', 'Status', 'Added Date']);
        
        $sql = "SELECT i.id, v.plate_number, i.provider_name, i.policy_number, i.start_date, i.end_date, i.coverage_type, i.monthly_premium, i.status, i.created_at 
                FROM insurance i JOIN vehicles v ON i.vehicle_id = v.id WHERE v.user_id = ? ORDER BY i.created_at DESC";
        $stmt = mysqli_prepare($connect, $sql);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($result)) {
            fputcsv($output, $row);
        }
        fputcsv($output, []);
        fputcsv($output, []);
    }
    
    if ($export_type === 'all' || $export_type === 'payments') {
        fputcsv($output, ['PAYMENTS']);
        fputcsv($output, ['ID', 'Vehicle Plate', 'Amount (Tsh)', 'Payment Type', 'Payment Date', 'Description', 'Added Date']);
        
        $sql = "SELECT p.id, v.plate_number, p.amount, p.payment_type, p.payment_date, p.description, p.created_at 
                FROM payments p JOIN vehicles v ON p.vehicle_id = v.id WHERE v.user_id = ? ORDER BY p.created_at DESC";
        $stmt = mysqli_prepare($connect, $sql);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $total = 0;
        while ($row = mysqli_fetch_assoc($result)) {
            fputcsv($output, $row);
            $total += $row['amount'];
        }
        fputcsv($output, ['TOTAL', '', $total]);
        fputcsv($output, []);
        fputcsv($output, []);
    }
    
    if ($export_type === 'all' || $export_type === 'maintenance') {
        fputcsv($output, ['MAINTENANCE']);
        fputcsv($output, ['ID', 'Vehicle Plate', 'Maintenance Type', 'Cost (Tsh)', 'Maintenance Date', 'Next Due Date', 'Description', 'Added Date']);
        
        $sql = "SELECT m.id, v.plate_number, m.maintenance_type, m.cost, m.maintenance_date, m.next_due_date, m.description, m.created_at 
                FROM maintenance m JOIN vehicles v ON m.vehicle_id = v.id WHERE v.user_id = ? ORDER BY m.created_at DESC";
        $stmt = mysqli_prepare($connect, $sql);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $total = 0;
        while ($row = mysqli_fetch_assoc($result)) {
            fputcsv($output, $row);
            $total += $row['cost'];
        }
        fputcsv($output, ['TOTAL', '', $total]);
    }
    
    fclose($output);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Data - VehiclePro</title>
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
                <a href="export_excel.php" class="sidebar-nav-link active">
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
                <h2>Export Data</h2>
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
            <a href="dashboard.php" class="btn btn-secondary" style="margin-bottom: var(--spacing-lg);">
                <i class="fas fa-arrow-left"></i> Back
            </a>

            <div style="max-width: 800px; margin: 0 auto;">
                <!-- Introduction -->
                <div class="card" style="margin-bottom: var(--spacing-2xl);">
                    <div style="text-align: center; padding: var(--spacing-2xl);">
                        <div style="font-size: 3rem; margin-bottom: var(--spacing-lg);">📊</div>
                        <h2 style="margin-bottom: var(--spacing-md); color: var(--dark);">Export Your Data</h2>
                        <p style="color: var(--gray); margin-bottom: var(--spacing-lg);">Download your vehicle records, insurance, payments, and maintenance data as CSV files. Perfect for backups, reports, and analysis.</p>
                    </div>
                </div>

                <!-- Export Options -->
                <form method="POST" class="form">
                    <div style="margin-bottom: var(--spacing-xl);">
                        <h3 style="color: var(--dark); margin-bottom: var(--spacing-lg); font-size: 1.3rem;">
                            <i class="fas fa-file-excel"></i> Select Data to Export
                        </h3>

                        <!-- All Data Option -->
                        <div style="padding: var(--spacing-lg); background-color: var(--light); border-radius: var(--radius-md); border: 2px solid var(--gray-light); margin-bottom: var(--spacing-lg); cursor: pointer;" onclick="document.getElementById('all').checked=true">
                            <input type="radio" id="all" name="export_type" value="all" checked style="margin-right: var(--spacing-md); cursor: pointer;">
                            <label for="all" style="cursor: pointer; display: inline-block;">
                                <strong style="font-size: 1.1rem; color: var(--primary);">📦 Export All Data</strong>
                                <p style="margin: 8px 0 0 0; color: var(--gray); font-size: 0.9rem;">Export vehicles, insurance, payments, and maintenance in one file</p>
                            </label>
                        </div>

                        <!-- Vehicles Only -->
                        <div style="padding: var(--spacing-lg); background-color: var(--light); border-radius: var(--radius-md); border: 2px solid var(--gray-light); margin-bottom: var(--spacing-lg); cursor: pointer;" onclick="document.getElementById('vehicles').checked=true">
                            <input type="radio" id="vehicles" name="export_type" value="vehicles" style="margin-right: var(--spacing-md); cursor: pointer;">
                            <label for="vehicles" style="cursor: pointer; display: inline-block;">
                                <strong style="font-size: 1.1rem; color: var(--primary);">🚗 Vehicles Only</strong>
                                <p style="margin: 8px 0 0 0; color: var(--gray); font-size: 0.9rem;">Export all your vehicle records with details</p>
                            </label>
                        </div>

                        <!-- Insurance Only -->
                        <div style="padding: var(--spacing-lg); background-color: var(--light); border-radius: var(--radius-md); border: 2px solid var(--gray-light); margin-bottom: var(--spacing-lg); cursor: pointer;" onclick="document.getElementById('insurance').checked=true">
                            <input type="radio" id="insurance" name="export_type" value="insurance" style="margin-right: var(--spacing-md); cursor: pointer;">
                            <label for="insurance" style="cursor: pointer; display: inline-block;">
                                <strong style="font-size: 1.1rem; color: var(--primary);">🛡️ Insurance Only</strong>
                                <p style="margin: 8px 0 0 0; color: var(--gray); font-size: 0.9rem;">Export all insurance policies and coverage details</p>
                            </label>
                        </div>

                        <!-- Payments Only -->
                        <div style="padding: var(--spacing-lg); background-color: var(--light); border-radius: var(--radius-md); border: 2px solid var(--gray-light); margin-bottom: var(--spacing-lg); cursor: pointer;" onclick="document.getElementById('payments').checked=true">
                            <input type="radio" id="payments" name="export_type" value="payments" style="margin-right: var(--spacing-md); cursor: pointer;">
                            <label for="payments" style="cursor: pointer; display: inline-block;">
                                <strong style="font-size: 1.1rem; color: var(--primary);">💰 Payments Only</strong>
                                <p style="margin: 8px 0 0 0; color: var(--gray); font-size: 0.9rem;">Export all payment records with totals</p>
                            </label>
                        </div>

                        <!-- Maintenance Only -->
                        <div style="padding: var(--spacing-lg); background-color: var(--light); border-radius: var(--radius-md); border: 2px solid var(--gray-light); margin-bottom: var(--spacing-lg); cursor: pointer;" onclick="document.getElementById('maintenance').checked=true">
                            <input type="radio" id="maintenance" name="export_type" value="maintenance" style="margin-right: var(--spacing-md); cursor: pointer;">
                            <label for="maintenance" style="cursor: pointer; display: inline-block;">
                                <strong style="font-size: 1.1rem; color: var(--primary);">🔧 Maintenance Only</strong>
                                <p style="margin: 8px 0 0 0; color: var(--gray); font-size: 0.9rem;">Export all maintenance records with costs</p>
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success" style="width: 100%; padding: var(--spacing-lg); font-size: 1.1rem;">
                        <i class="fas fa-download"></i> Download CSV File
                    </button>
                </form>

                <!-- Features Section -->
                <div class="card" style="margin-top: var(--spacing-2xl);">
                    <div class="card-header">
                        <h3 class="card-title">✨ What's Included</h3>
                    </div>
                    <div class="card-body">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--spacing-lg);">
                            <div style="padding: var(--spacing-lg); background-color: var(--light); border-radius: var(--radius-md);">
                                <div style="font-size: 1.5rem; margin-bottom: 10px;">📋</div>
                                <strong>Clear Headers</strong>
                                <p style="color: var(--gray); font-size: 0.9rem; margin-top: 8px;">Easy to understand column names</p>
                            </div>

                            <div style="padding: var(--spacing-lg); background-color: var(--light); border-radius: var(--radius-md);">
                                <div style="font-size: 1.5rem; margin-bottom: 10px;">📊</div>
                                <strong>Totals & Summaries</strong>
                                <p style="color: var(--gray); font-size: 0.9rem; margin-top: 8px;">Auto-calculated totals included</p>
                            </div>

                            <div style="padding: var(--spacing-lg); background-color: var(--light); border-radius: var(--radius-md);">
                                <div style="font-size: 1.5rem; margin-bottom: 10px;">🔒</div>
                                <strong>Your Data Only</strong>
                                <p style="color: var(--gray); font-size: 0.9rem; margin-top: 8px;">Only your personal records exported</p>
                            </div>

                            <div style="padding: var(--spacing-lg); background-color: var(--light); border-radius: var(--radius-md);">
                                <div style="font-size: 1.5rem; margin-bottom: 10px;">⏰</div>
                                <strong>Timestamped Files</strong>
                                <p style="color: var(--gray); font-size: 0.9rem; margin-top: 8px;">Files named with date and time</p>
                            </div>

                            <div style="padding: var(--spacing-lg); background-color: var(--light); border-radius: var(--radius-md);">
                                <div style="font-size: 1.5rem; margin-bottom: 10px;">📝</div>
                                <strong>CSV Format</strong>
                                <p style="color: var(--gray); font-size: 0.9rem; margin-top: 8px;">Works in Excel, Google Sheets, LibreOffice</p>
                            </div>

                            <div style="padding: var(--spacing-lg); background-color: var(--light); border-radius: var(--radius-md);">
                                <div style="font-size: 1.5rem; margin-bottom: 10px;">⚡</div>
                                <strong>No Setup Required</strong>
                                <p style="color: var(--gray); font-size: 0.9rem; margin-top: 8px;">Works instantly, no dependencies</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Instructions Section -->
                <div class="card" style="margin-top: var(--spacing-2xl);">
                    <div class="card-header">
                        <h3 class="card-title">📖 How to Use</h3>
                    </div>
                    <div class="card-body">
                        <ol style="color: var(--dark); line-height: 1.8;">
                            <li style="margin-bottom: 15px;">
                                <strong>Select Export Type</strong> - Choose what data you want (All, Vehicles, Insurance, Payments, or Maintenance)
                            </li>
                            <li style="margin-bottom: 15px;">
                                <strong>Click Download</strong> - Click "Download CSV File" button
                            </li>
                            <li style="margin-bottom: 15px;">
                                <strong>Open File</strong> - Open with Excel, Google Sheets, or any spreadsheet app
                            </li>
                            <li style="margin-bottom: 15px;">
                                <strong>Use Your Data</strong> - Analyze, print, share, or backup your records
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="js/main.js"></script>
</body>
</html>
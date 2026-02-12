<?php
include 'auth.php';
requireLogin();

include 'config.php';
include 'functions.php';

$user_id = getUserId();
$user = getUserData();

$message = '';
$error = '';

// Get user's vehicles for dropdown
$vehicles_sql = "SELECT id, plate_number FROM vehicles WHERE user_id = ? ORDER BY plate_number";
$vehicles_stmt = mysqli_prepare($connect, $vehicles_sql);
mysqli_stmt_bind_param($vehicles_stmt, "i", $user_id);
mysqli_stmt_execute($vehicles_stmt);
$vehicles_result = mysqli_stmt_get_result($vehicles_stmt);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $vehicle_id = intval($_POST['vehicle_id'] ?? 0);
    $amount = floatval($_POST['amount'] ?? 0);
    $payment_date = $_POST['payment_date'] ?? '';
    $payment_type = htmlspecialchars($_POST['payment_type'] ?? '');
    $description = htmlspecialchars($_POST['description'] ?? '');

    if (empty($vehicle_id) || empty($amount) || empty($payment_date)) {
        $error = "Vehicle, amount, and date are required!";
    } else {
        // Verify vehicle ownership
        $verify_sql = "SELECT id FROM vehicles WHERE id = ? AND user_id = ?";
        $verify_stmt = mysqli_prepare($connect, $verify_sql);
        mysqli_stmt_bind_param($verify_stmt, "ii", $vehicle_id, $user_id);
        mysqli_stmt_execute($verify_stmt);
        $verify_result = mysqli_stmt_get_result($verify_stmt);

        if (mysqli_num_rows($verify_result) > 0) {
            $sql = "INSERT INTO payments (vehicle_id, amount, payment_date, payment_type, description) 
                    VALUES (?, ?, ?, ?, ?)";
            
            $stmt = mysqli_prepare($connect, $sql);
            mysqli_stmt_bind_param($stmt, "idsss", $vehicle_id, $amount, $payment_date, $payment_type, $description);
            
            if (mysqli_stmt_execute($stmt)) {
                $message = "✅ Payment recorded successfully!";
                $_POST = [];
            } else {
                $error = "❌ Error recording payment: " . mysqli_error($connect);
            }
        } else {
            $error = "Invalid vehicle selected!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Payment - VehiclePro</title>
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
                <h2>Record Payment</h2>
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
            <div style="max-width: 800px; margin: 0 auto;">
                <a href="payments.php" class="btn btn-secondary" style="margin-bottom: var(--spacing-lg);">
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
                    <form method="POST" class="form">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-lg);">
                            <div class="form-group">
                                <label for="vehicle_id">
                                    <i class="fas fa-car"></i> Select Vehicle *
                                </label>
                                <select id="vehicle_id" name="vehicle_id" required>
                                    <option value="">-- Choose a vehicle --</option>
                                    <?php while ($vehicle = mysqli_fetch_assoc($vehicles_result)): ?>
                                        <option value="<?php echo $vehicle['id']; ?>" <?php echo (intval($_POST['vehicle_id'] ?? 0) === $vehicle['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($vehicle['plate_number']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="amount">
                                    <i class="fas fa-coins"></i> Amount (Tsh) *
                                </label>
                                <input 
                                    type="number" 
                                    id="amount" 
                                    name="amount" 
                                    placeholder="0.00"
                                    step="0.01"
                                    min="0"
                                    value="<?php echo htmlspecialchars($_POST['amount'] ?? ''); ?>"
                                    required
                                >
                            </div>

                            <div class="form-group">
                                <label for="payment_date">
                                    <i class="fas fa-calendar-alt"></i> Payment Date *
                                </label>
                                <input 
                                    type="date" 
                                    id="payment_date" 
                                    name="payment_date"
                                    value="<?php echo htmlspecialchars($_POST['payment_date'] ?? ''); ?>"
                                    required
                                >
                            </div>

                            <div class="form-group">
                                <label for="payment_type">
                                    <i class="fas fa-list"></i> Payment Type
                                </label>
                                <select id="payment_type" name="payment_type">
                                    <option value="">Select...</option>
                                    <option value="Insurance" <?php echo ($_POST['payment_type'] ?? '') === 'Insurance' ? 'selected' : ''; ?>>Insurance</option>
                                    <option value="Maintenance" <?php echo ($_POST['payment_type'] ?? '') === 'Maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                                    <option value="Fuel" <?php echo ($_POST['payment_type'] ?? '') === 'Fuel' ? 'selected' : ''; ?>>Fuel</option>
                                    <option value="Registration" <?php echo ($_POST['payment_type'] ?? '') === 'Registration' ? 'selected' : ''; ?>>Registration</option>
                                    <option value="Inspection" <?php echo ($_POST['payment_type'] ?? '') === 'Inspection' ? 'selected' : ''; ?>>Inspection</option>
                                    <option value="Other" <?php echo ($_POST['payment_type'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>

                            <div class="form-group" style="grid-column: 1 / -1;">
                                <label for="description">
                                    <i class="fas fa-comment"></i> Description
                                </label>
                                <textarea 
                                    id="description" 
                                    name="description" 
                                    placeholder="Add any notes about this payment..."
                                    rows="3"
                                    style="resize: vertical; width: 100%; padding: var(--spacing-md); border: 1.5px solid var(--gray-light); border-radius: var(--radius-md); font-family: var(--font-main);"
                                ><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <div style="display: flex; gap: var(--spacing-md); margin-top: var(--spacing-xl);">
                            <button type="submit" class="btn btn-primary" style="flex: 1;">
                                <i class="fas fa-save"></i> Save Payment
                            </button>
                            <a href="payments.php" class="btn btn-secondary" style="flex: 1; text-align: center;">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script src="js/main.js"></script>
</body>
</html>
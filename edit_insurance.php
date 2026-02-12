<?php
include 'auth.php';
requireLogin();

include 'config.php';
include 'functions.php';

$user_id = getUserId();
$user = getUserData();

$insurance_id = intval($_GET['id'] ?? 0);

// Get insurance record
$insurance_sql = "SELECT i.* FROM insurance i
                  JOIN vehicles v ON i.vehicle_id = v.id
                  WHERE i.id = ? AND v.user_id = ?";
$insurance_stmt = mysqli_prepare($connect, $insurance_sql);
mysqli_stmt_bind_param($insurance_stmt, "ii", $insurance_id, $user_id);
mysqli_stmt_execute($insurance_stmt);
$insurance_result = mysqli_stmt_get_result($insurance_stmt);
$insurance = mysqli_fetch_assoc($insurance_result);

if (!$insurance) {
    header('Location: insurance.php');
    exit();
}

// Get vehicles
$vehicles_sql = "SELECT id, plate_number FROM vehicles WHERE user_id = ? ORDER BY plate_number";
$vehicles_stmt = mysqli_prepare($connect, $vehicles_sql);
mysqli_stmt_bind_param($vehicles_stmt, "i", $user_id);
mysqli_stmt_execute($vehicles_stmt);
$vehicles_result = mysqli_stmt_get_result($vehicles_stmt);

$message = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $vehicle_id = intval($_POST['vehicle_id'] ?? 0);
    $provider_name = htmlspecialchars($_POST['provider_name'] ?? '');
    $policy_number = htmlspecialchars($_POST['policy_number'] ?? '');
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $monthly_premium = floatval($_POST['monthly_premium'] ?? 0);
    $coverage_type = htmlspecialchars($_POST['coverage_type'] ?? '');

    if (empty($vehicle_id) || empty($start_date) || empty($end_date)) {
        $error = "Vehicle, start date, and end date are required!";
    } else {
        $sql = "UPDATE insurance SET vehicle_id = ?, provider_name = ?, policy_number = ?, start_date = ?, end_date = ?, monthly_premium = ?, coverage_type = ? 
                WHERE id = ?";
        
        $stmt = mysqli_prepare($connect, $sql);
        mysqli_stmt_bind_param($stmt, "isssdsi", $vehicle_id, $provider_name, $policy_number, $start_date, $end_date, $monthly_premium, $coverage_type, $insurance_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $message = "✅ Insurance record updated successfully!";
            // Reload insurance data
            $insurance_stmt = mysqli_prepare($connect, $insurance_sql);
            mysqli_stmt_bind_param($insurance_stmt, "ii", $insurance_id, $user_id);
            mysqli_stmt_execute($insurance_stmt);
            $insurance_result = mysqli_stmt_get_result($insurance_stmt);
            $insurance = mysqli_fetch_assoc($insurance_result);
        } else {
            $error = "❌ Error updating insurance: " . mysqli_error($connect);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Insurance - VehiclePro</title>
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
                <h2>Edit Insurance</h2>
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
                <a href="insurance.php" class="btn btn-secondary" style="margin-bottom: var(--spacing-lg);">
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
                                    <i class="fas fa-car"></i> Vehicle *
                                </label>
                                <select id="vehicle_id" name="vehicle_id" required>
                                    <?php while ($vehicle = mysqli_fetch_assoc($vehicles_result)): ?>
                                        <option value="<?php echo $vehicle['id']; ?>" <?php echo $insurance['vehicle_id'] == $vehicle['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($vehicle['plate_number']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="provider_name">
                                    <i class="fas fa-shield-alt"></i> Provider Name
                                </label>
                                <input 
                                    type="text" 
                                    id="provider_name" 
                                    name="provider_name" 
                                    placeholder="e.g., APA, HABA"
                                    value="<?php echo htmlspecialchars($insurance['provider_name'] ?? ''); ?>"
                                >
                            </div>

                            <div class="form-group">
                                <label for="policy_number">
                                    <i class="fas fa-file-contract"></i> Policy Number
                                </label>
                                <input 
                                    type="text" 
                                    id="policy_number" 
                                    name="policy_number" 
                                    placeholder="Policy number"
                                    value="<?php echo htmlspecialchars($insurance['policy_number'] ?? ''); ?>"
                                >
                            </div>

                            <div class="form-group">
                                <label for="coverage_type">
                                    <i class="fas fa-list"></i> Coverage Type
                                </label>
                                <select id="coverage_type" name="coverage_type">
                                    <option value="">Select...</option>
                                    <option value="Third Party" <?php echo ($insurance['coverage_type'] ?? '') === 'Third Party' ? 'selected' : ''; ?>>Third Party</option>
                                    <option value="Comprehensive" <?php echo ($insurance['coverage_type'] ?? '') === 'Comprehensive' ? 'selected' : ''; ?>>Comprehensive</option>
                                    <option value="Full Coverage" <?php echo ($insurance['coverage_type'] ?? '') === 'Full Coverage' ? 'selected' : ''; ?>>Full Coverage</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="start_date">
                                    <i class="fas fa-calendar-check"></i> Start Date *
                                </label>
                                <input 
                                    type="date" 
                                    id="start_date" 
                                    name="start_date"
                                    value="<?php echo htmlspecialchars($insurance['start_date']); ?>"
                                    required
                                >
                            </div>

                            <div class="form-group">
                                <label for="end_date">
                                    <i class="fas fa-calendar-times"></i> End Date *
                                </label>
                                <input 
                                    type="date" 
                                    id="end_date" 
                                    name="end_date"
                                    value="<?php echo htmlspecialchars($insurance['end_date']); ?>"
                                    required
                                >
                            </div>

                            <div class="form-group">
                                <label for="monthly_premium">
                                    <i class="fas fa-coins"></i> Monthly Premium (Tsh)
                                </label>
                                <input 
                                    type="number" 
                                    id="monthly_premium" 
                                    name="monthly_premium" 
                                    placeholder="0.00"
                                    step="0.01"
                                    min="0"
                                    value="<?php echo htmlspecialchars($insurance['monthly_premium'] ?? ''); ?>"
                                >
                            </div>
                        </div>

                        <div style="display: flex; gap: var(--spacing-md); margin-top: var(--spacing-xl);">
                            <button type="submit" class="btn btn-primary" style="flex: 1;">
                                <i class="fas fa-save"></i> Update Insurance
                            </button>
                            <a href="insurance.php" class="btn btn-secondary" style="flex: 1; text-align: center;">
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
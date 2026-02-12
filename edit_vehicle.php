<?php
include 'auth.php';
requireLogin();

include 'config.php';
include 'functions.php';

$user_id = getUserId();
$user = getUserData();

$vehicle_id = intval($_GET['id'] ?? 0);
$vehicle = getVehicleById($vehicle_id, $user_id);

if (!$vehicle) {
    header('Location: vehicles.php');
    exit();
}

$message = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $plate_number = htmlspecialchars($_POST['plate_number'] ?? '');
    $make = htmlspecialchars($_POST['make'] ?? '');
    $model = htmlspecialchars($_POST['model'] ?? '');
    $year = intval($_POST['year'] ?? 0);
    $color = htmlspecialchars($_POST['color'] ?? '');
    $vin = htmlspecialchars($_POST['vin'] ?? '');
    $owner_name = htmlspecialchars($_POST['owner_name'] ?? '');
    $phone_number = htmlspecialchars($_POST['phone_number'] ?? '');
    $purchase_date = $_POST['purchase_date'] ?? '';
    $status = htmlspecialchars($_POST['status'] ?? 'active');

    if (empty($plate_number) || empty($owner_name) || empty($phone_number)) {
        $error = "Plate number, owner name, and phone number are required!";
    } else {
        $sql = "UPDATE vehicles SET plate_number = ?, make = ?, model = ?, year = ?, color = ?, vin = ?, owner_name = ?, phone_number = ?, purchase_date = ?, status = ? 
                WHERE id = ? AND user_id = ?";
        
        $stmt = mysqli_prepare($connect, $sql);
        mysqli_stmt_bind_param($stmt, "sssissssssi", $plate_number, $make, $model, $year, $color, $vin, $owner_name, $phone_number, $purchase_date, $status, $vehicle_id, $user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $message = "✅ Vehicle updated successfully!";
            $vehicle = getVehicleById($vehicle_id, $user_id);
        } else {
            $error = "❌ Error updating vehicle: " . mysqli_error($connect);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Vehicle - VehiclePro</title>
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
                <h2>Edit Vehicle</h2>
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
                <a href="vehicles.php" class="btn btn-secondary" style="margin-bottom: var(--spacing-lg);">
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
                    <div style="margin-bottom: var(--spacing-lg); padding-bottom: var(--spacing-lg); border-bottom: 1px solid var(--gray-light);">
                        <h2 style="margin: 0; color: var(--dark);">📝 Edit Vehicle</h2>
                        <p style="color: var(--gray); margin: var(--spacing-sm) 0 0 0;">Update vehicle information</p>
                    </div>

                    <form method="POST" class="form">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-lg);">
                            <div class="form-group">
                                <label for="plate_number">
                                    <i class="fas fa-id-card"></i> Plate Number *
                                </label>
                                <input 
                                    type="text" 
                                    id="plate_number" 
                                    name="plate_number" 
                                    placeholder="e.g., ABC-123"
                                    value="<?php echo htmlspecialchars($vehicle['plate_number']); ?>"
                                    required
                                >
                            </div>

                            <div class="form-group">
                                <label for="make">
                                    <i class="fas fa-car"></i> Make
                                </label>
                                <input 
                                    type="text" 
                                    id="make" 
                                    name="make" 
                                    placeholder="e.g., Toyota"
                                    value="<?php echo htmlspecialchars($vehicle['make'] ?? ''); ?>"
                                >
                            </div>

                            <div class="form-group">
                                <label for="model">
                                    <i class="fas fa-car-side"></i> Model
                                </label>
                                <input 
                                    type="text" 
                                    id="model" 
                                    name="model" 
                                    placeholder="e.g., Corolla"
                                    value="<?php echo htmlspecialchars($vehicle['model'] ?? ''); ?>"
                                >
                            </div>

                            <div class="form-group">
                                <label for="year">
                                    <i class="fas fa-calendar"></i> Year
                                </label>
                                <input 
                                    type="number" 
                                    id="year" 
                                    name="year" 
                                    placeholder="e.g., 2023"
                                    min="1900"
                                    max="2100"
                                    value="<?php echo htmlspecialchars($vehicle['year'] ?? ''); ?>"
                                >
                            </div>

                            <div class="form-group">
                                <label for="color">
                                    <i class="fas fa-palette"></i> Color
                                </label>
                                <div style="display: flex; gap: var(--spacing-md);">
                                    <input 
                                        type="color" 
                                        id="color" 
                                        name="color" 
                                        value="<?php echo htmlspecialchars($vehicle['color'] ?? '#000000'); ?>"
                                        style="width: 60px; height: 45px; border: none; border-radius: var(--radius-md); cursor: pointer;"
                                    >
                                    <input 
                                        type="text" 
                                        placeholder="Color name"
                                        style="flex: 1;"
                                        readonly
                                        value="<?php echo htmlspecialchars($vehicle['color'] ?? 'Select Color'); ?>"
                                    >
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="vin">
                                    <i class="fas fa-fingerprint"></i> VIN
                                </label>
                                <input 
                                    type="text" 
                                    id="vin" 
                                    name="vin" 
                                    placeholder="Vehicle Identification Number"
                                    value="<?php echo htmlspecialchars($vehicle['vin'] ?? ''); ?>"
                                >
                            </div>

                            <div class="form-group">
                                <label for="owner_name">
                                    <i class="fas fa-user"></i> Owner Name *
                                </label>
                                <input 
                                    type="text" 
                                    id="owner_name" 
                                    name="owner_name" 
                                    placeholder="Owner's full name"
                                    value="<?php echo htmlspecialchars($vehicle['owner_name']); ?>"
                                    required
                                >
                            </div>

                            <div class="form-group">
                                <label for="phone_number">
                                    <i class="fas fa-phone"></i> Phone Number *
                                </label>
                                <input 
                                    type="tel" 
                                    id="phone_number" 
                                    name="phone_number" 
                                    placeholder="Phone number"
                                    value="<?php echo htmlspecialchars($vehicle['phone_number']); ?>"
                                    required
                                >
                            </div>

                            <div class="form-group">
                                <label for="purchase_date">
                                    <i class="fas fa-calendar-alt"></i> Purchase Date
                                </label>
                                <input 
                                    type="date" 
                                    id="purchase_date" 
                                    name="purchase_date"
                                    value="<?php echo htmlspecialchars($vehicle['purchase_date'] ?? ''); ?>"
                                >
                            </div>

                            <div class="form-group">
                                <label for="status">
                                    <i class="fas fa-toggle-on"></i> Status
                                </label>
                                <select id="status" name="status">
                                    <option value="active" <?php echo $vehicle['status'] === 'active' ? 'selected' : ''; ?>>🟢 Active</option>
                                    <option value="inactive" <?php echo $vehicle['status'] === 'inactive' ? 'selected' : ''; ?>>🟡 Inactive</option>
                                    <option value="sold" <?php echo $vehicle['status'] === 'sold' ? 'selected' : ''; ?>>🔴 Sold</option>
                                </select>
                            </div>
                        </div>

                        <div style="display: flex; gap: var(--spacing-md); margin-top: var(--spacing-xl);">
                            <button type="submit" class="btn btn-primary" style="flex: 1;">
                                <i class="fas fa-save"></i> Update Vehicle
                            </button>
                            <a href="vehicles.php" class="btn btn-secondary" style="flex: 1; text-align: center;">
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
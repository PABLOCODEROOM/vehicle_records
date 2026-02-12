<?php
include 'auth.php';
requireLogin();

include 'config.php';
include 'functions.php';

$user_id = getUserId();
$user = getUserData();

$message = '';
$error = '';

// Handle backup request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['backup'])) {
    
    // Create uploads folder if it doesn't exist
    if (!file_exists('uploads')) {
        mkdir('uploads', 0777, true);
    }
    
    if (!file_exists('uploads/backup')) {
        mkdir('uploads/backup', 0777, true);
    }
    
    // Generate backup file name with date
    $backup_file = 'uploads/backup/backup_' . date('Y-m-d_H-i-s') . '.sql';
    
    // MySQL backup command
    $command = "mysqldump --user=root --password= vehicle_records > " . escapeshellarg($backup_file);
    
    // Execute the backup command
    system($command, $output);
    
    if (file_exists($backup_file) && filesize($backup_file) > 0) {
        $file_size = filesize($backup_file);
        $message = "✅ Backup created successfully! File: " . basename($backup_file) . " (Size: " . round($file_size/1024, 2) . " KB)";
    } else {
        $error = "❌ Backup failed. Make sure mysqldump is installed and in your system PATH.";
    }
}

// Get list of existing backups
$backups = [];
if (file_exists('uploads/backup')) {
    $files = scandir('uploads/backup');
    foreach ($files as $file) {
        if (strpos($file, 'backup_') === 0 && strpos($file, '.sql') !== false) {
            $backups[] = [
                'name' => $file,
                'size' => filesize('uploads/backup/' . $file),
                'date' => filemtime('uploads/backup/' . $file)
            ];
        }
    }
    // Sort by date (newest first)
    usort($backups, function($a, $b) {
        return $b['date'] - $a['date'];
    });
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Backup - VehiclePro</title>
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
                <a href="export_excel.php" class="sidebar-nav-link">
                    <i class="fas fa-file-excel"></i> Export Data
                </a>
            </li>
            <li class="sidebar-nav-item">
                <a href="backup_database.php" class="sidebar-nav-link active">
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
                <h2>Database Backup</h2>
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
            <div class="page-header">
                <div>
                    <h1 class="page-title">💾 Database Backup</h1>
                    <p class="page-subtitle">Backup and restore your vehicle records safely</p>
                </div>
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

            <!-- Introduction Card -->
            <div class="card" style="margin-bottom: var(--spacing-2xl);">
                <div style="padding: var(--spacing-xl);">
                    <div style="display: grid; grid-template-columns: 100px 1fr; gap: var(--spacing-lg); align-items: start;">
                        <div style="font-size: 3rem; text-align: center;">💾</div>
                        <div>
                            <h2 style="margin-top: 0; color: var(--dark);">What is Database Backup?</h2>
                            <p style="color: var(--gray); margin-bottom: var(--spacing-md);">A database backup is a copy of all your vehicle records, insurance data, payments, and maintenance information. If something goes wrong, you can restore your data from a backup.</p>
                            <p style="color: var(--gray); margin: 0;">Regular backups are essential for data protection and compliance.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3-2-1 Backup Strategy -->
            <div class="card" style="margin-bottom: var(--spacing-2xl);">
                <div class="card-header">
                    <h3 class="card-title">📚 3-2-1 Backup Strategy</h3>
                </div>
                <div class="card-body">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: var(--spacing-lg);">
                        <!-- 3 Copies -->
                        <div style="padding: var(--spacing-lg); background: linear-gradient(135deg, #dbeafe 0%, #e0f2fe 100%); border-radius: var(--radius-lg); border-left: 4px solid var(--info);">
                            <div style="font-size: 2rem; margin-bottom: 10px;">3️⃣</div>
                            <h4 style="color: var(--dark); margin-top: 0;">Three Copies</h4>
                            <p style="color: var(--gray); margin: 0; font-size: 0.95rem;">Keep 3 versions of your backup files</p>
                            <ul style="color: var(--gray); font-size: 0.9rem; margin: 10px 0 0 0; padding-left: 20px;">
                                <li>Original database</li>
                                <li>Backup copy 1</li>
                                <li>Backup copy 2</li>
                            </ul>
                        </div>

                        <!-- 2 Storage Types -->
                        <div style="padding: var(--spacing-lg); background: linear-gradient(135deg, #fef3c7 0%, #fed7aa 100%); border-radius: var(--radius-lg); border-left: 4px solid var(--secondary);">
                            <div style="font-size: 2rem; margin-bottom: 10px;">2️⃣</div>
                            <h4 style="color: var(--dark); margin-top: 0;">Two Storage Types</h4>
                            <p style="color: var(--gray); margin: 0; font-size: 0.95rem;">Store on different types of media</p>
                            <ul style="color: var(--gray); font-size: 0.9rem; margin: 10px 0 0 0; padding-left: 20px;">
                                <li>Internal hard drive</li>
                                <li>External USB/SSD</li>
                            </ul>
                        </div>

                        <!-- 1 Off-site -->
                        <div style="padding: var(--spacing-lg); background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%); border-radius: var(--radius-lg); border-left: 4px solid var(--success);">
                            <div style="font-size: 2rem; margin-bottom: 10px;">1️⃣</div>
                            <h4 style="color: var(--dark); margin-top: 0;">One Off-site</h4>
                            <p style="color: var(--gray); margin: 0; font-size: 0.95rem;">Keep one copy in different location</p>
                            <ul style="color: var(--gray); font-size: 0.9rem; margin: 10px 0 0 0; padding-left: 20px;">
                                <li>Cloud storage (Google Drive)</li>
                                <li>Remote server</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Backup Actions -->
            <div class="card" style="margin-bottom: var(--spacing-2xl);">
                <div class="card-header">
                    <h3 class="card-title">🚀 Create New Backup</h3>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <p style="color: var(--gray); margin-bottom: var(--spacing-lg);">Click the button below to create a new backup of your entire database. This may take a few seconds.</p>
                        <button type="submit" name="backup" value="1" class="btn btn-warning" style="padding: var(--spacing-lg); font-size: 1.1rem; width: 100%;">
                            <i class="fas fa-download"></i> Create Backup Now
                        </button>
                    </form>
                </div>
            </div>

            <!-- Backup List -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">📦 Existing Backups</h3>
                    <span style="background-color: var(--primary); color: white; padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 600;">
                        <?php echo count($backups); ?> files
                    </span>
                </div>
                <div class="card-body">
                    <?php if (!empty($backups)): ?>
                        <!-- Backups Summary -->
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--spacing-lg); margin-bottom: var(--spacing-2xl);">
                            <div style="padding: var(--spacing-lg); background-color: var(--light); border-radius: var(--radius-md);">
                                <div style="color: var(--gray); font-size: 0.9rem; margin-bottom: 8px;">Total Backups</div>
                                <div style="font-size: 2rem; font-weight: 700; color: var(--primary);"><?php echo count($backups); ?></div>
                            </div>

                            <div style="padding: var(--spacing-lg); background-color: var(--light); border-radius: var(--radius-md);">
                                <div style="color: var(--gray); font-size: 0.9rem; margin-bottom: 8px;">Latest Backup</div>
                                <div style="font-size: 1.1rem; font-weight: 600; color: var(--dark);">
                                    <?php 
                                    $latest = reset($backups);
                                    echo date('M d, Y H:i', $latest['date']); 
                                    ?>
                                </div>
                            </div>

                            <div style="padding: var(--spacing-lg); background-color: var(--light); border-radius: var(--radius-md);">
                                <div style="color: var(--gray); font-size: 0.9rem; margin-bottom: 8px;">Total Size</div>
                                <div style="font-size: 1.1rem; font-weight: 600; color: var(--dark);">
                                    <?php 
                                    $total_size = 0;
                                    foreach ($backups as $backup) {
                                        $total_size += $backup['size'];
                                    }
                                    echo round($total_size / (1024 * 1024), 2) . ' MB';
                                    ?>
                                </div>
                            </div>
                        </div>

                        <!-- Backups Table -->
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-file"></i> Backup File</th>
                                        <th><i class="fas fa-database"></i> Size</th>
                                        <th><i class="fas fa-calendar"></i> Created Date</th>
                                        <th><i class="fas fa-tools"></i> Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($backups as $backup): ?>
                                        <tr>
                                            <td>
                                                <div style="display: flex; align-items: center; gap: 10px;">
                                                    <i class="fas fa-file-archive" style="color: var(--secondary); font-size: 1.2rem;"></i>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($backup['name']); ?></strong>
                                                        <div style="font-size: 0.85rem; color: var(--gray); margin-top: 3px;">SQL Database Backup</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span style="background-color: var(--light); padding: 6px 12px; border-radius: var(--radius-md); font-weight: 600; color: var(--dark);">
                                                    <?php echo round($backup['size']/1024, 2); ?> KB
                                                </span>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong><?php echo date('M d, Y', $backup['date']); ?></strong>
                                                    <div style="font-size: 0.85rem; color: var(--gray); margin-top: 3px;"><?php echo date('H:i:s', $backup['date']); ?></div>
                                                </div>
                                            </td>
                                            <td>
                                                <div style="display: flex; gap: 8px;">
                                                    <a href="uploads/backup/<?php echo htmlspecialchars($backup['name']); ?>" class="btn btn-sm btn-primary" download title="Download backup">
                                                        <i class="fas fa-download"></i> Download
                                                    </a>
                                                    <button class="btn btn-sm btn-secondary" onclick="copyToClipboard('<?php echo htmlspecialchars($backup['name']); ?>')" title="Copy filename">
                                                        <i class="fas fa-copy"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Backup Tips -->
                        <div style="padding: var(--spacing-lg); background-color: #fef3c7; border-radius: var(--radius-md); border-left: 4px solid var(--secondary); margin-top: var(--spacing-lg);">
                            <div style="font-weight: 600; color: var(--dark); margin-bottom: 10px;">
                                <i class="fas fa-lightbulb"></i> Backup Tips
                            </div>
                            <ul style="color: var(--dark); font-size: 0.95rem; margin: 0; padding-left: 20px;">
                                <li>Download backups and store them on USB drives or external hard drives</li>
                                <li>Upload backups to cloud storage (Google Drive, Dropbox, OneDrive)</li>
                                <li>Keep at least 3 copies following the 3-2-1 strategy</li>
                                <li>Test your backups periodically to ensure they work</li>
                                <li>Label backups with dates for easy identification</li>
                            </ul>
                        </div>

                    <?php else: ?>
                        <div style="text-align: center; padding: var(--spacing-2xl);">
                            <div style="font-size: 4rem; margin-bottom: var(--spacing-lg);">📦</div>
                            <h3 style="color: var(--gray); margin-bottom: var(--spacing-md);">No backups yet</h3>
                            <p style="color: var(--gray); margin-bottom: var(--spacing-lg);">Start by creating your first backup to protect your data.</p>
                            <form method="POST" style="display: inline;">
                                <button type="submit" name="backup" value="1" class="btn btn-warning">
                                    <i class="fas fa-download"></i> Create First Backup
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Manual Backup Instructions -->
            <div class="card" style="margin-top: var(--spacing-2xl);">
                <div class="card-header">
                    <h3 class="card-title">⚙️ Manual Backup (Command Line)</h3>
                </div>
                <div class="card-body">
                    <p style="color: var(--gray); margin-bottom: var(--spacing-lg);">If automated backups fail, you can create a backup manually using the command line:</p>
                    
                    <div style="background-color: var(--light); padding: var(--spacing-lg); border-radius: var(--radius-md); font-family: 'Courier New', monospace; margin-bottom: var(--spacing-lg); overflow-x: auto;">
                        <code style="color: var(--dark);">mysqldump -u root vehicle_records > C:\backup.sql</code>
                    </div>

                    <p style="color: var(--gray); margin-bottom: var(--spacing-md);">This command:</p>
                    <ul style="color: var(--dark); margin: 0;">
                        <li>Exports the entire <code style="background-color: var(--light); padding: 2px 6px; border-radius: 3px;">vehicle_records</code> database</li>
                        <li>Saves it as <code style="background-color: var(--light); padding: 2px 6px; border-radius: 3px;">backup.sql</code> on your C: drive</li>
                        <li>The file contains all your vehicle, insurance, payment, and maintenance data</li>
                    </ul>

                    <div style="padding: var(--spacing-lg); background-color: #dbeafe; border-radius: var(--radius-md); border-left: 4px solid var(--info); margin-top: var(--spacing-lg);">
                        <strong style="color: var(--primary);">💡 Tip:</strong> Replace <code style="background-color: white; padding: 2px 6px; border-radius: 3px;">C:\backup.sql</code> with your desired save location.
                    </div>
                </div>
            </div>

            <!-- Restore Instructions -->
            <div class="card" style="margin-top: var(--spacing-lg); margin-bottom: var(--spacing-2xl);">
                <div class="card-header">
                    <h3 class="card-title">🔄 How to Restore from Backup</h3>
                </div>
                <div class="card-body">
                    <ol style="color: var(--dark); line-height: 2;">
                        <li>
                            <strong>Download backup file</strong> - Click the download button next to your backup
                        </li>
                        <li>
                            <strong>Open Command Prompt</strong> - Go to the folder where backup is saved
                        </li>
                        <li>
                            <strong>Run restore command:</strong>
                            <div style="background-color: var(--light); padding: var(--spacing-lg); border-radius: var(--radius-md); font-family: 'Courier New', monospace; margin-top: 10px;">
                                <code style="color: var(--dark);">mysql -u root vehicle_records < backup_2024-01-10_10-30-45.sql</code>
                            </div>
                        </li>
                        <li>
                            <strong>Wait for completion</strong> - The database will be restored
                        </li>
                    </ol>

                    <div style="padding: var(--spacing-lg); background-color: #fee2e2; border-radius: var(--radius-md); border-left: 4px solid var(--danger); margin-top: var(--spacing-lg);">
                        <strong style="color: var(--danger);">⚠️ Warning:</strong> Restoring will overwrite your current database. Make sure you want to restore before proceeding!
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="js/main.js"></script>
    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text);
            alert('Filename copied to clipboard!');
        }
    </script>
</body>
</html>
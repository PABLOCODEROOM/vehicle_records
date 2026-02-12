<?php
// Include database connection
include 'config.php';

// Get all vehicles from database
$sql = "SELECT * FROM vehicles ORDER BY created_at DESC";
$result = mysqli_query($connect, $sql);

// Count total vehicles
$total_vehicles = mysqli_num_rows($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Vehicles</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>🚗 All Vehicles (Total: <?php echo $total_vehicles; ?>)</h1>
        
        <a href="add_vehicle.php" class="btn btn-primary">+ Add New Vehicle</a>
        
        <?php if ($total_vehicles > 0): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Plate Number</th>
                        <th>Owner Name</th>
                        <th>Phone Number</th>
                        <th>Added Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['plate_number']); ?></td>
                            <td><?php echo htmlspecialchars($row['owner_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['phone_number']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="no-data">No vehicles found. <a href="add_vehicle.php">Add one now!</a></p>
        <?php endif; ?>
        
        <a href="index.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</body>
</html>
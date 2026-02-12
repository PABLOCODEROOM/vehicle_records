<?php
// Include database connection
include 'config.php';

// Get all payments with vehicle information
$sql = "SELECT p.*, v.plate_number 
        FROM payments p 
        JOIN vehicles v ON p.vehicle_id = v.id 
        ORDER BY p.payment_date DESC";

$result = mysqli_query($connect, $sql);
$total_payments = 0;
$payment_count = mysqli_num_rows($result);

// Calculate total payments
$total_sql = "SELECT SUM(amount) as total FROM payments";
$total_result = mysqli_query($connect, $total_sql);
$total_row = mysqli_fetch_assoc($total_result);
$total_payments = $total_row['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Payments</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>💰 All Payments</h1>
        
        <div class="stats">
            <div class="stat-card">
                <h3>Total Payments</h3>
                <p class="stat-value">$<?php echo number_format($total_payments, 2); ?></p>
            </div>
            <div class="stat-card">
                <h3>Transaction Count</h3>
                <p class="stat-value"><?php echo $payment_count; ?></p>
            </div>
        </div>
        
        <a href="add_payment.php" class="btn btn-primary">+ Add Payment</a>
        
        <?php if ($payment_count > 0): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Plate Number</th>
                        <th>Amount</th>
                        <th>Payment Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['plate_number']); ?></td>
                            <td class="amount">$<?php echo number_format($row['amount'], 2); ?></td>
                            <td><?php echo date('M d, Y', strtotime($row['payment_date'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="no-data">No payments found.</p>
        <?php endif; ?>
        
        <a href="index.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</body>
</html>
<?php
include 'config.php';

// Format currency in Tanzanian Shillings
function formatCurrency($amount) {
    return CURRENCY_SYMBOL . ' ' . number_format($amount, 2);
}

// Get user's vehicles count
function getUserVehiclesCount($user_id) {
    global $connect;
    $sql = "SELECT COUNT(*) as count FROM vehicles WHERE user_id = ?";
    $stmt = mysqli_prepare($connect, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);
    return $data['count'];
}

// Get user's total payments
function getUserTotalPayments($user_id) {
    global $connect;
    $sql = "SELECT SUM(p.amount) as total FROM payments p 
            JOIN vehicles v ON p.vehicle_id = v.id 
            WHERE v.user_id = ?";
    $stmt = mysqli_prepare($connect, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);
    return $data['total'] ?? 0;
}

// Get active insurance count
function getActiveInsuranceCount($user_id) {
    global $connect;
    $sql = "SELECT COUNT(*) as count FROM insurance i
            JOIN vehicles v ON i.vehicle_id = v.id
            WHERE v.user_id = ? AND i.end_date >= CURDATE()";
    $stmt = mysqli_prepare($connect, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);
    return $data['count'];
}

// Get expired insurance count
function getExpiredInsuranceCount($user_id) {
    global $connect;
    $sql = "SELECT COUNT(*) as count FROM insurance i
            JOIN vehicles v ON i.vehicle_id = v.id
            WHERE v.user_id = ? AND i.end_date < CURDATE()";
    $stmt = mysqli_prepare($connect, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);
    return $data['count'];
}

// Get days until expiration
function daysUntilExpiration($end_date) {
    $today = new DateTime();
    $expiry = new DateTime($end_date);
    $interval = $today->diff($expiry);
    return $interval->invert == 1 ? -$interval->days : $interval->days;
}

// Get expiration status
function getExpirationStatus($end_date) {
    $days = daysUntilExpiration($end_date);
    
    if ($days < 0) {
        return ['status' => 'expired', 'text' => '🔴 EXPIRED', 'class' => 'badge-danger'];
    } elseif ($days <= 7) {
        return ['status' => 'expiring', 'text' => '🟡 EXPIRING SOON', 'class' => 'badge-warning'];
    } elseif ($days <= 30) {
        return ['status' => 'warning', 'text' => '🟠 WARNING', 'class' => 'badge-info'];
    } else {
        return ['status' => 'active', 'text' => '🟢 ACTIVE', 'class' => 'badge-success'];
    }
}

// Get user's vehicles
function getUserVehicles($user_id) {
    global $connect;
    $sql = "SELECT * FROM vehicles WHERE user_id = ? ORDER BY created_at DESC";
    $stmt = mysqli_prepare($connect, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

// Get vehicle by ID and verify ownership
function getVehicleById($vehicle_id, $user_id) {
    global $connect;
    $sql = "SELECT * FROM vehicles WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($connect, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $vehicle_id, $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

// Get insurance records for vehicle
function getVehicleInsurance($vehicle_id) {
    global $connect;
    $sql = "SELECT * FROM insurance WHERE vehicle_id = ? ORDER BY end_date DESC";
    $stmt = mysqli_prepare($connect, $sql);
    mysqli_stmt_bind_param($stmt, "i", $vehicle_id);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

// Get payment records for vehicle
function getVehiclePayments($vehicle_id) {
    global $connect;
    $sql = "SELECT * FROM payments WHERE vehicle_id = ? ORDER BY payment_date DESC";
    $stmt = mysqli_prepare($connect, $sql);
    mysqli_stmt_bind_param($stmt, "i", $vehicle_id);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

// Format date
function formatDate($date) {
    return date('M d, Y', strtotime($date));
}

?>
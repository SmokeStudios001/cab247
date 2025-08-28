<?php
// admin-panel/get-drivers-location.php
include 'includes/auth.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

include 'includes/config.php';

// Get all active drivers with their locations
$sql = "SELECT d.id, d.name, d.car_model, d.car_number, d.latitude, d.longitude, d.status,
               ct.name as car_type, ct.icon
        FROM drivers d 
        LEFT JOIN car_types ct ON d.car_type_id = ct.id 
        WHERE d.is_verified = 1 
        AND d.latitude IS NOT NULL 
        AND d.longitude IS NOT NULL
        ORDER BY d.status DESC, d.name ASC";

$result = $conn->query($sql);
$drivers = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $drivers[] = $row;
    }
}

echo json_encode([
    'success' => true,
    'drivers' => $drivers,
    'timestamp' => date('Y-m-d H:i:s')
]);
?>
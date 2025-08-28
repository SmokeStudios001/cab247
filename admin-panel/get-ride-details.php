<?php
// admin-panel/get-ride-details.php
include 'includes/auth.php';
// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("HTTP/1.1 401 Unauthorized");
    echo json_encode(array("success" => false, "message" => "Unauthorized"));
    exit;
}

include 'includes/config.php';

if (isset($_GET['id'])) {
    $ride_id = intval($_GET['id']);
    
    $sql = "SELECT r.*, p.name as passenger_name, p.phone as passenger_phone, 
                   d.name as driver_name, d.phone as driver_phone, 
                   ct.name as car_type_name, s.setting_value as currency
            FROM rides r 
            LEFT JOIN passengers p ON r.passenger_id = p.id 
            LEFT JOIN drivers d ON r.driver_id = d.id 
            LEFT JOIN car_types ct ON r.car_type_id = ct.id 
            LEFT JOIN settings s ON s.setting_key = 'currency'
            WHERE r.id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $ride_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $ride = $result->fetch_assoc();
        
        header('Content-Type: application/json');
        echo json_encode(array("success" => true, "data" => $ride));
    } else {
        header('Content-Type: application/json');
        echo json_encode(array("success" => false, "message" => "Ride not found"));
    }
} else {
    header('Content-Type: application/json');
    header("HTTP/1.1 400 Bad Request");
    echo json_encode(array("success" => false, "message" => "Ride ID is required"));
}
?>
<?php
include 'includes/auth.php';
// admin-panel/update-settings.php
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if ($data) {
        $success = true;
        
        foreach ($data as $key => $value) {
            if (strpos($key, 'setting_') === 0) {
                $setting_key = substr($key, 8);
                $setting_value = trim($value);
                
                $sql = "UPDATE settings SET setting_value = ? WHERE setting_key = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ss", $setting_value, $setting_key);
                
                if (!$stmt->execute()) {
                    $success = false;
                }
            }
        }
        
        if ($success) {
            echo json_encode(array("success" => true, "message" => "Settings updated successfully"));
        } else {
            echo json_encode(array("success" => false, "message" => "Error updating some settings"));
        }
    } else {
        echo json_encode(array("success" => false, "message" => "Invalid JSON data"));
    }
} else {
    header("HTTP/1.1 405 Method Not Allowed");
    echo json_encode(array("success" => false, "message" => "Method not allowed"));
}
?>
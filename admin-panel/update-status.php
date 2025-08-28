<?php
// admin-panel/update-status.php
include 'includes/auth.php';
include 'includes/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the raw POST data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (isset($data['id']) && isset($data['table']) && isset($data['status'])) {
        $id = $data['id'];
        $table = $data['table'];
        $status = $data['status'];
        
        // Validate table name to prevent SQL injection
        $valid_tables = ['car_types', 'drivers', 'passengers', 'rides'];
        if (!in_array($table, $valid_tables)) {
            echo json_encode(['success' => false, 'message' => 'Invalid table name']);
            exit;
        }
        
        // Update the status
        $stmt = $conn->prepare("UPDATE $table SET status = ? WHERE id = ?");
        $stmt->bind_param("ii", $status, $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => $conn->error]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
<?php
// admin-panel/get-users.php
include 'includes/auth.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

include 'includes/config.php';

$type = $_GET['type'] ?? '';

if ($type === 'driver') {
    $result = $conn->query("SELECT id, name, email FROM drivers ORDER BY name");
} elseif ($type === 'passenger') {
    $result = $conn->query("SELECT id, name, email FROM passengers ORDER BY name");
} else {
    echo json_encode(['users' => []]);
    exit;
}

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

echo json_encode(['users' => $users]);

$conn->close();
?>